<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $query = Purchase::with(['supplier', 'user'])->latest();

        if ($request->search) {
            $query->where('reference_no', 'like', '%' . $request->search . '%');
        }

        if ($request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('purchase_date', [$request->start_date, $request->end_date]);
        }

        // Stats summary (ignoring pagination for sums)
        $statsQuery = clone $query;
        $totalAmount = $statsQuery->sum('total_amount');
        
        $totalReceived = (clone $statsQuery)->where('status', 'received')->sum('total_amount');
        $totalPending = (clone $statsQuery)->whereIn('status', ['draft', 'sent'])->sum('total_amount');

        $purchases = $query->paginate(20)->withQueryString();
        $suppliers = Supplier::active()->orderBy('name')->get();

        return view('purchases.index', compact('purchases', 'suppliers', 'totalAmount', 'totalReceived', 'totalPending'));
    }

    public function create(Request $request)
    {
        $suppliers = Supplier::active()->orderBy('name')->get();
        
        // Premium feature: pre-populate items from reorder list
        $prepopulatedItems = [];
        if ($request->has('reorder_products')) {
            $productIds = explode(',', $request->get('reorder_products'));
            $products = Product::whereIn('id', $productIds)->get();
            foreach ($products as $p) {
                $suggestedQty = max(1, ($p->stock_min * 2) - $p->stock);
                $prepopulatedItems[] = [
                    'product_id' => $p->id,
                    'product_name' => $p->name,
                    'quantity' => $suggestedQty,
                    'unit_name' => $p->unit?->name ?? 'pcs',
                    'purchase_price' => $p->purchase_price,
                    'sell_price' => $p->sell_price,
                    'expired_date' => $p->expired_date ? $p->expired_date->format('Y-m-d') : '',
                ];
            }
        }

        // Generate automatic purchase reference code (almaira-0001/tanggal/bulan/tahun)
        $today = now()->format('d/m/Y');
        $prefix = 'almaira-';
        $suffix = '/' . $today;
        
        $lastPurchase = Purchase::where('reference_no', 'like', $prefix . '%' . $suffix)->latest('id')->first();
        
        $nextNumber = 1;
        if ($lastPurchase) {
            $ref = $lastPurchase->reference_no;
            $parts = explode('/', str_replace($prefix, '', $ref));
            if (count($parts) > 0) {
                $lastNum = (int)$parts[0];
                $nextNumber = $lastNum + 1;
            }
        }
        
        $nextRefNo = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT) . $suffix;

        return view('purchases.create', compact('suppliers', 'prepopulatedItems', 'nextRefNo'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'reference_no' => 'required|string|max:50',
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'status' => 'required|in:draft,sent,received',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.purchase_price' => 'required|numeric|min:0',
            'items.*.sell_price' => 'required|numeric|min:0',
            'items.*.expired_date' => 'nullable|date',
        ], [
            'reference_no.required' => 'Nomor faktur wajib diisi.',
            'supplier_id.required' => 'Supplier wajib dipilih.',
            'purchase_date.required' => 'Tanggal pembelian wajib diisi.',
            'items.required' => 'Item barang masuk minimal harus 1.',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $totalAmount = 0;
                foreach ($request->items as $item) {
                    $totalAmount += $item['quantity'] * $item['purchase_price'];
                }

                $purchase = Purchase::create([
                    'reference_no' => $request->reference_no,
                    'supplier_id' => $request->supplier_id,
                    'user_id' => auth()->id(),
                    'purchase_date' => $request->purchase_date,
                    'total_amount' => $totalAmount,
                    'notes' => $request->notes,
                    'status' => $request->status,
                ]);

                foreach ($request->items as $item) {
                    $product = Product::findOrFail($item['product_id']);
                    $subtotal = $item['quantity'] * $item['purchase_price'];

                    // Create purchase item
                    PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity' => $item['quantity'],
                        'purchase_price' => $item['purchase_price'],
                        'sell_price' => $item['sell_price'],
                        'subtotal' => $subtotal,
                        'expired_date' => $item['expired_date'] ?: null,
                    ]);

                    // Only update stock and pricing if status is received
                    if ($request->status === 'received') {
                        $newStock = $product->stock + $item['quantity'];
                        
                        $hetPercentage = (int)($product->het_percentage ?? 10);
                        $newHetPrice = $item['purchase_price'] * (1 + $hetPercentage / 100);

                        $product->update([
                            'stock' => $newStock,
                            'purchase_price' => $item['purchase_price'],
                            'sell_price' => $item['sell_price'],
                            'het_price' => $newHetPrice,
                            'expired_date' => $item['expired_date'] ?: $product->expired_date,
                        ]);
                    }
                }

                $statusLabel = [
                    'draft' => 'Draft PO',
                    'sent' => 'PO Dikirim',
                    'received' => 'Barang Masuk (Diterima)'
                ][$request->status];

                ActivityLogService::log(
                    'CREATE',
                    'Barang Masuk',
                    "Mencatat {$statusLabel} faktur {$purchase->reference_no} — Total: Rp " . number_format($totalAmount, 0, ',', '.')
                );
            });

            $msg = $request->status === 'received' 
                ? 'Barang masuk berhasil disimpan dan stok telah bertambah!' 
                : 'Purchase Order (PO) berhasil disimpan dengan status ' . ucfirst($request->status) . '!';

            return redirect()->route('purchases.index')->with('toast_success', $msg);
        } catch (\Exception $e) {
            return back()->withInput()->with('toast_error', 'Gagal menyimpan barang masuk: ' . $e->getMessage());
        }
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'user', 'items.product']);
        return view('purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase)
    {
        // Safety: received purchase cannot be edited because it has already modified stocks
        if ($purchase->status === 'received') {
            return redirect()->route('purchases.show', $purchase)->with('toast_error', 'Barang masuk yang telah diterima tidak dapat diedit kembali.');
        }

        $suppliers = Supplier::active()->orderBy('name')->get();
        $purchase->load(['items.product']);
        
        return view('purchases.edit', compact('purchase', 'suppliers'));
    }

    public function update(Request $request, Purchase $purchase)
    {
        if ($purchase->status === 'received') {
            return redirect()->route('purchases.show', $purchase)->with('toast_error', 'Barang masuk yang telah diterima tidak dapat diedit kembali.');
        }

        $request->validate([
            'reference_no' => 'required|string|max:50',
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'status' => 'required|in:draft,sent,received',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.purchase_price' => 'required|numeric|min:0',
            'items.*.sell_price' => 'required|numeric|min:0',
            'items.*.expired_date' => 'nullable|date',
        ], [
            'reference_no.required' => 'Nomor faktur wajib diisi.',
            'supplier_id.required' => 'Supplier wajib dipilih.',
            'purchase_date.required' => 'Tanggal pembelian wajib diisi.',
            'items.required' => 'Item barang masuk minimal harus 1.',
        ]);

        try {
            DB::transaction(function () use ($request, $purchase) {
                $totalAmount = 0;
                foreach ($request->items as $item) {
                    $totalAmount += $item['quantity'] * $item['purchase_price'];
                }

                // Delete old items
                $purchase->items()->delete();

                // Update purchase
                $purchase->update([
                    'reference_no' => $request->reference_no,
                    'supplier_id' => $request->supplier_id,
                    'purchase_date' => $request->purchase_date,
                    'total_amount' => $totalAmount,
                    'notes' => $request->notes,
                    'status' => $request->status,
                ]);

                foreach ($request->items as $item) {
                    $product = Product::findOrFail($item['product_id']);
                    $subtotal = $item['quantity'] * $item['purchase_price'];

                    // Create new purchase item
                    PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity' => $item['quantity'],
                        'purchase_price' => $item['purchase_price'],
                        'sell_price' => $item['sell_price'],
                        'subtotal' => $subtotal,
                        'expired_date' => $item['expired_date'] ?: null,
                    ]);

                    // If status became received, update product stock and pricing
                    if ($request->status === 'received') {
                        $newStock = $product->stock + $item['quantity'];
                        
                        $hetPercentage = (int)($product->het_percentage ?? 10);
                        $newHetPrice = $item['purchase_price'] * (1 + $hetPercentage / 100);

                        $product->update([
                            'stock' => $newStock,
                            'purchase_price' => $item['purchase_price'],
                            'sell_price' => $item['sell_price'],
                            'het_price' => $newHetPrice,
                            'expired_date' => $item['expired_date'] ?: $product->expired_date,
                        ]);
                    }
                }

                $statusLabel = [
                    'draft' => 'Draft PO',
                    'sent' => 'PO Dikirim',
                    'received' => 'Barang Masuk (Diterima)'
                ][$request->status];

                ActivityLogService::log(
                    'UPDATE',
                    'Barang Masuk',
                    "Memperbarui {$statusLabel} faktur {$purchase->reference_no} — Total: Rp " . number_format($totalAmount, 0, ',', '.')
                );
            });

            $msg = $request->status === 'received' 
                ? 'Barang masuk berhasil diterima dan stok telah bertambah!' 
                : 'Purchase Order (PO) berhasil diperbarui dengan status ' . ucfirst($request->status) . '!';

            return redirect()->route('purchases.index')->with('toast_success', $msg);
        } catch (\Exception $e) {
            return back()->withInput()->with('toast_error', 'Gagal memperbarui barang masuk: ' . $e->getMessage());
        }
    }

    public function receive(Purchase $purchase)
    {
        if ($purchase->status === 'received') {
            return back()->with('toast_error', 'Barang masuk sudah berstatus diterima.');
        }

        try {
            DB::transaction(function () use ($purchase) {
                $purchase->update(['status' => 'received']);
                $purchase->load('items.product');

                foreach ($purchase->items as $item) {
                    if ($item->product) {
                        $product = $item->product;
                        $newStock = $product->stock + $item->quantity;
                        
                        $hetPercentage = (int)($product->het_percentage ?? 10);
                        $newHetPrice = $item->purchase_price * (1 + $hetPercentage / 100);

                        $product->update([
                            'stock' => $newStock,
                            'purchase_price' => $item->purchase_price,
                            'sell_price' => $item->sell_price,
                            'het_price' => $newHetPrice,
                            'expired_date' => $item->expired_date ?: $product->expired_date,
                        ]);
                    }
                }

                ActivityLogService::log(
                    'UPDATE',
                    'Barang Masuk',
                    "Menerima barang masuk untuk PO {$purchase->reference_no} — Stok bertambah."
                );
            });

            return redirect()->route('purchases.show', $purchase)->with('toast_success', 'Barang berhasil diterima dan stok telah bertambah!');
        } catch (\Exception $e) {
            return back()->with('toast_error', 'Gagal memproses penerimaan barang: ' . $e->getMessage());
        }
    }

    public function reorderList()
    {
        $products = Product::whereColumn('stock', '<=', 'stock_min')
            ->with(['supplier', 'unit'])
            ->orderBy('name')
            ->get();

        return view('purchases.reorder', compact('products'));
    }

    public function downloadPdf(Request $request, Purchase $purchase)
    {
        $purchase->load(['supplier', 'user', 'items.product']);
        $entity = $request->input('entity', 'pt'); // Default to 'pt' for Purchase Order since PO is usually a PT document
        
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $pdf = Pdf::loadView('purchases.pdf', compact('purchase', 'entity'));
        $pdf->setPaper('a4', 'portrait');

        ActivityLogService::log(
            'DOWNLOAD',
            'Barang Masuk',
            "Mengunduh PDF Faktur Pembelian PO {$purchase->reference_no} (Entitas: {$entity})"
        );

        return $pdf->stream("faktur_pembelian_{$purchase->reference_no}.pdf");
    }
}
