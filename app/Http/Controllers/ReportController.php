<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Models\StockOpname;
use App\Models\ActivityLog;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(): View
    {
        $cashiers = User::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::active()->orderBy('name')->get();
        $modules = ActivityLog::select('module')
            ->distinct()
            ->whereNotNull('module')
            ->orderBy('module')
            ->pluck('module');

        return view('reports.index', compact('cashiers', 'categories', 'suppliers', 'products', 'modules'));
    }

    public function generate(Request $request)
    {
        ini_set('memory_limit', '256M');
        $type = $request->input('type');
        $format = $request->input('format', 'html');

        if (!$type) {
            abort(400, 'Tipe laporan tidak ditentukan.');
        }

        $user = $request->user();
        if ($user->allows('activity_log') && ! $user->allows('reports') && ! $user->isSuperAdmin() && $type !== 'log_aktivitas') {
            abort(403, 'Anda hanya dapat mengakses log aktivitas.');
        }
        if ($type === 'log_aktivitas' && ! $user->allows('activity_log') && ! $user->isSuperAdmin()) {
            abort(403, 'Anda tidak memiliki akses log aktivitas.');
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $entity = $request->input('entity', 'apotek');

        // Initial view data
        $viewData = [
            'type' => $type,
            'format' => $format,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'entity' => $entity,
            'report_name' => $this->getReportName($type),
        ];

        // Route queries based on type
        switch ($type) {
            case 'penjualan_harian':
                $query = Sale::where('status', 'completed');
                if ($startDate) $query->whereDate('sold_at', '>=', $startDate);
                if ($endDate) $query->whereDate('sold_at', '<=', $endDate);
                if ($request->user_id) $query->where('user_id', $request->user_id);
                if ($request->payment_method) $query->where('payment_method', $request->payment_method);
                $viewData['data'] = $query->with('user')->orderBy('sold_at', 'asc')->get();
                break;

            case 'penjualan_bulanan':
                $month = $request->input('month', date('m'));
                $year = $request->input('year', date('Y'));
                $viewData['month'] = $month;
                $viewData['year'] = $year;
                $viewData['data'] = Sale::where('status', 'completed')
                    ->whereMonth('sold_at', $month)
                    ->whereYear('sold_at', $year)
                    ->selectRaw("date(sold_at) as date_label, COUNT(*) as count, SUM(subtotal) as subtotal, SUM(discount_amount) as discount_amount, SUM(ppn_amount) as ppn_amount, SUM(total) as total")
                    ->groupBy('date_label')
                    ->orderBy('date_label', 'asc')
                    ->get();
                break;

            case 'penjualan_per_produk':
                $query = SaleItem::select('product_id', 'product_name', 'product_code', 'unit_name')
                    ->selectRaw('SUM(quantity) as total_qty, AVG(unit_price) as avg_price, SUM(discount_amount) as total_discount, SUM(subtotal) as total_revenue')
                    ->whereHas('sale', function($q) use ($startDate, $endDate) {
                        $q->where('status', 'completed');
                        if ($startDate) $q->whereDate('sold_at', '>=', $startDate);
                        if ($endDate) $q->whereDate('sold_at', '<=', $endDate);
                    });
                if ($request->category_id) {
                    $query->whereHas('product', function($q) use ($request) {
                        $q->where('category_id', $request->category_id);
                    });
                }
                if ($request->product_id) {
                    $query->where('product_id', $request->product_id);
                }
                $viewData['data'] = $query->groupBy('product_id', 'product_name', 'product_code', 'unit_name')
                    ->orderByDesc('total_qty')
                    ->get();
                break;

            case 'penjualan_per_kasir':
                $query = Sale::where('status', 'completed');
                if ($startDate) $query->whereDate('sold_at', '>=', $startDate);
                if ($endDate) $query->whereDate('sold_at', '<=', $endDate);
                if ($request->user_id) $query->where('user_id', $request->user_id);
                $viewData['data'] = $query->select('user_id')
                    ->selectRaw('COUNT(*) as count, SUM(subtotal) as subtotal, SUM(discount_amount) as discount_amount, SUM(ppn_amount) as ppn_amount, SUM(total) as total')
                    ->groupBy('user_id')
                    ->with('user')
                    ->get();
                break;

            case 'pembelian':
                $query = \App\Models\Purchase::query();
                if ($startDate) $query->whereDate('purchase_date', '>=', $startDate);
                if ($endDate) $query->whereDate('purchase_date', '<=', $endDate);
                if ($request->supplier_id) $query->where('supplier_id', $request->supplier_id);
                $viewData['data'] = $query->with(['supplier', 'user'])->orderBy('purchase_date', 'asc')->get();
                break;

            case 'stok_saat_ini':
                $query = Product::active();
                if ($request->category_id) $query->where('category_id', $request->category_id);
                if ($request->supplier_id) $query->where('supplier_id', $request->supplier_id);
                if ($request->stock_level === 'low') {
                    $query->lowStock();
                } elseif ($request->stock_level === 'normal') {
                    $query->whereColumn('stock', '>', 'stock_min');
                }
                $viewData['data'] = $query->with(['category', 'unit', 'supplier'])->orderBy('name', 'asc')->get();
                break;

            case 'stok_menipis':
                $query = Product::active()->lowStock();
                if ($request->category_id) $query->where('category_id', $request->category_id);
                if ($request->supplier_id) $query->where('supplier_id', $request->supplier_id);
                $viewData['data'] = $query->with(['category', 'unit', 'supplier'])->orderBy('stock', 'asc')->get();
                break;

            case 'produk_kadaluarsa':
                $query = Product::active()->whereNotNull('expired_date');
                if ($request->category_id) $query->where('category_id', $request->category_id);
                if ($request->supplier_id) $query->where('supplier_id', $request->supplier_id);
                
                $range = $request->input('expiry_range', 'all');
                $viewData['expiry_range'] = $range;
                if ($range === 'expired') {
                    $query->where('expired_date', '<', today());
                } elseif ($range === '30_days') {
                    $query->where('expired_date', '<=', now()->addDays(30))
                          ->where('expired_date', '>=', today());
                } elseif ($range === '60_days') {
                    $query->where('expired_date', '<=', now()->addDays(60))
                          ->where('expired_date', '>', now()->addDays(30));
                }
                $viewData['data'] = $query->with(['category', 'unit'])->orderBy('expired_date', 'asc')->get();
                break;

            case 'laba_rugi':
                $startDate = $startDate ?? today()->startOfMonth()->format('Y-m-d');
                $endDate = $endDate ?? today()->endOfMonth()->format('Y-m-d');
                $viewData['start_date'] = $startDate;
                $viewData['end_date'] = $endDate;

                $sales = Sale::where('status', 'completed')
                    ->whereBetween('sold_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                    ->get();
                $salesIds = $sales->pluck('id');
                $totalRevenue = $sales->sum('total');
                $totalSubtotal = $sales->sum('subtotal');
                $totalDiscount = $sales->sum('discount_amount');
                $totalPpn = $sales->sum('ppn_amount');

                $totalHpp = SaleItem::whereIn('sale_id', $salesIds)
                    ->leftJoin('products', 'sale_items.product_id', '=', 'products.id')
                    ->selectRaw('SUM(sale_items.quantity * COALESCE(products.purchase_price, 0)) as total_hpp')
                    ->value('total_hpp') ?? 0;

                $totalProfit = SaleItem::whereIn('sale_id', $salesIds)
                    ->leftJoin('products', 'sale_items.product_id', '=', 'products.id')
                    ->selectRaw('SUM(sale_items.subtotal - (COALESCE(products.purchase_price, 0) * sale_items.quantity)) as profit')
                    ->value('profit') ?? 0;

                $totalGlobalDiscount = $sales->sum('discount_amount');
                $grossProfit = $totalProfit - $totalGlobalDiscount;

                // Operational Expenses 1: Salaries paid in this period
                $totalSalaries = \App\Models\Salary::whereBetween('payment_date', [$startDate, $endDate])
                    ->sum('net_salary') ?? 0;

                // Operational Expenses 2: Loss from expired / damaged stock outs in this period
                $totalStockOutLoss = \App\Models\StockOut::whereIn('reason', ['expired', 'damaged'])
                    ->whereBetween('out_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                    ->leftJoin('products', 'stock_outs.product_id', '=', 'products.id')
                    ->selectRaw('SUM(stock_outs.quantity * COALESCE(products.purchase_price, 0)) as loss')
                    ->value('loss') ?? 0;

                $totalOperationalCosts = $totalSalaries + $totalStockOutLoss;
                $netProfit = $grossProfit - $totalOperationalCosts;

                $viewData['data'] = (object)[
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'total_sales_count' => $sales->count(),
                    'total_revenue' => $totalRevenue,
                    'total_subtotal' => $totalSubtotal,
                    'total_discount' => $totalDiscount,
                    'total_ppn' => $totalPpn,
                    'total_hpp' => $totalHpp,
                    'gross_profit' => $grossProfit,
                    'total_salaries' => $totalSalaries,
                    'total_stock_out_loss' => $totalStockOutLoss,
                    'total_operational_costs' => $totalOperationalCosts,
                    'net_profit' => $netProfit,
                ];
                break;

            case 'ppn_pajak':
                $query = Sale::where('status', 'completed')->where('ppn_active', true);
                if ($startDate) $query->whereDate('sold_at', '>=', $startDate);
                if ($endDate) $query->whereDate('sold_at', '<=', $endDate);
                if ($request->ppn_bearer && $request->ppn_bearer !== 'all') {
                    $query->where('ppn_bearer', $request->ppn_bearer);
                }
                $viewData['data'] = $query->orderBy('sold_at', 'asc')->get();
                break;

            case 'diskon':
                $query = Sale::where('status', 'completed')
                    ->where(function($q) {
                        $q->where('discount_amount', '>', 0)
                          ->orWhereHas('items', function($sq) {
                              $sq->where('discount_amount', '>', 0);
                          });
                    });
                if ($startDate) $query->whereDate('sold_at', '>=', $startDate);
                if ($endDate) $query->whereDate('sold_at', '<=', $endDate);
                $viewData['data'] = $query->with(['items' => function($q) {
                    $q->where('discount_amount', '>', 0);
                }, 'user'])->orderBy('sold_at', 'asc')->get();
                break;

            case 'stok_opname':
                $query = StockOpname::query();
                if ($startDate) $query->whereDate('created_at', '>=', $startDate);
                if ($endDate) $query->whereDate('created_at', '<=', $endDate);
                $viewData['data'] = $query->with(['product', 'user'])->latest()->get();
                break;

            case 'log_aktivitas':
                $query = ActivityLog::query();
                if ($startDate) $query->whereDate('created_at', '>=', $startDate);
                if ($endDate) $query->whereDate('created_at', '<=', $endDate);
                if ($request->user_id) $query->where('user_id', $request->user_id);
                if ($request->module) $query->where('module', $request->module);
                $viewData['data'] = $query->with('user')->orderBy('created_at', 'desc')->get();
                break;

            case 'transaksi_qris':
                $query = Sale::where('status', 'completed')->where('payment_method', 'qris');
                if ($startDate) $query->whereDate('sold_at', '>=', $startDate);
                if ($endDate) $query->whereDate('sold_at', '<=', $endDate);
                if ($request->user_id) $query->where('user_id', $request->user_id);
                $viewData['data'] = $query->with('user')->orderBy('sold_at', 'asc')->get();
                break;

            case 'gaji_karyawan':
                $query = \App\Models\Salary::with(['employee', 'user', 'creator']);
                if ($startDate) $query->whereDate('payment_date', '>=', $startDate);
                if ($endDate) $query->whereDate('payment_date', '<=', $endDate);
                if ($request->employee_id) $query->where('employee_id', $request->employee_id);
                if ($request->user_id) $query->where('user_id', $request->user_id);
                if ($request->filled('entity') && in_array($request->entity, ['pt', 'apotek'], true)) {
                    $query->where('entity', $request->entity);
                    $viewData['entity'] = $request->entity;
                } else {
                    $viewData['entity'] = $entity;
                }
                $viewData['data'] = $query->latest('payment_date')->get();
                break;

            case 'kredit_piutang':
                $pos = Sale::unpaidInvoices()
                    ->when($startDate, fn ($q) => $q->whereDate('sold_at', '>=', $startDate))
                    ->when($endDate, fn ($q) => $q->whereDate('sold_at', '<=', $endDate))
                    ->with('customer')->get()
                    ->map(fn ($s) => (object) [
                        'sumber' => 'POS',
                        'nomor' => $s->invoice_no,
                        'nama' => $s->customer_name ?? $s->customer?->name,
                        'tanggal' => $s->sold_at,
                        'jatuh_tempo' => $s->due_date,
                        'total' => $s->total,
                    ]);
                $mitra = \App\Models\PartnerOrder::creditUnpaid()
                    ->when($startDate, fn ($q) => $q->whereDate('created_at', '>=', $startDate))
                    ->when($endDate, fn ($q) => $q->whereDate('created_at', '<=', $endDate))
                    ->with('partner')->get()
                    ->map(fn ($o) => (object) [
                        'sumber' => 'Mitra PO',
                        'nomor' => $o->order_no,
                        'nama' => $o->partner?->name,
                        'tanggal' => $o->created_at,
                        'jatuh_tempo' => $o->due_date,
                        'total' => $o->total,
                    ]);
                $viewData['data'] = $pos->concat($mitra)->sortByDesc('tanggal')->values();
                $viewData['entity'] = 'pt';
                break;

            case 'invoice_lunas':
                $pos = Sale::paidInvoices()
                    ->when($startDate, fn ($q) => $q->whereDate('settled_at', '>=', $startDate))
                    ->when($endDate, fn ($q) => $q->whereDate('settled_at', '<=', $endDate))
                    ->with('settledBy')->get()
                    ->map(fn ($s) => (object) [
                        'sumber' => 'POS',
                        'nomor' => $s->invoice_no,
                        'nama' => $s->customer_name ?? $s->customer?->name,
                        'tanggal_lunas' => $s->settled_at,
                        'metode' => $s->settlement_method === 'cash' ? 'Tunai' : 'Transfer',
                        'total' => $s->total,
                    ]);
                $mitra = \App\Models\PartnerOrder::creditPaid()
                    ->when($startDate, fn ($q) => $q->whereDate('settled_at', '>=', $startDate))
                    ->when($endDate, fn ($q) => $q->whereDate('settled_at', '<=', $endDate))
                    ->with('partner')->get()
                    ->map(fn ($o) => (object) [
                        'sumber' => 'Mitra PO',
                        'nomor' => $o->order_no,
                        'nama' => $o->partner?->name,
                        'tanggal_lunas' => $o->settled_at,
                        'metode' => $o->settlement_method === 'cash' ? 'Tunai' : 'Transfer',
                        'total' => $o->total,
                    ]);
                $viewData['data'] = $pos->concat($mitra)->sortByDesc('tanggal_lunas')->values();
                $viewData['entity'] = 'pt';
                break;

            default:
                abort(404, 'Tipe Laporan tidak ditemukan.');
        }

        // Log report generation
        if (in_array($format, ['pdf', 'excel'])) {
            $formatName = strtoupper($format);
            $reportName = $this->getReportName($type);
            ActivityLogService::log(
                'PRINT_REPORT',
                'Laporan',
                "Mengunduh laporan: {$reportName} (Format: {$formatName})"
            );
        }

        // Format selection
        if ($format === 'pdf') {
            // Render as HTML print-ready page (auto-triggers browser print dialog)
            // This bypasses download managers entirely since it's an HTML page, not a binary file.
            // User can then print or save as PDF via browser's native print dialog.
            return view('reports.print_layout', $viewData);
        } elseif ($format === 'excel') {
            $filename = 'Laporan_' . $type . '_' . date('YmdHis') . '.xls';
            $content = view('reports.excel_layout', $viewData)->render();
            return response($content, 200, [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);
        }

        // HTML preview format (renders styled template table)
        return view('reports.preview', $viewData);
    }

    private function getReportName(string $type): string
    {
        $names = [
            'penjualan_harian' => 'Laporan Penjualan Harian',
            'penjualan_bulanan' => 'Laporan Penjualan Bulanan',
            'penjualan_per_produk' => 'Laporan Penjualan per Produk',
            'penjualan_per_kasir' => 'Laporan Penjualan per Kasir',
            'pembelian' => 'Laporan Pembelian (Barang Masuk)',
            'stok_saat_ini' => 'Laporan Stok Saat Ini',
            'stok_menipis' => 'Laporan Stok Menipis',
            'produk_kadaluarsa' => 'Laporan Produk Kadaluarsa',
            'laba_rugi' => 'Laporan Laba Rugi',
            'ppn_pajak' => 'Laporan Pajak (PPN)',
            'diskon' => 'Laporan Penggunaan Diskon',
            'stok_opname' => 'Laporan Stok Opname',
            'log_aktivitas' => 'Laporan Log Aktivitas Sistem',
            'transaksi_qris' => 'Laporan Transaksi Pembayaran QRIS',
            'gaji_karyawan' => 'Laporan Gaji Karyawan',
            'kredit_piutang' => 'Laporan Kredit / Piutang (Belum Lunas)',
            'invoice_lunas' => 'Laporan Invoice Lunas',
        ];

        return $names[$type] ?? 'Laporan Apotek Almaira';
    }
}
