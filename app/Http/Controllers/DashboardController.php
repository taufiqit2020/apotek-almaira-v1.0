<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(): View|\Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();

        if ($user->isMitra()) {
            return redirect()->route('mitra.account');
        }

        $today = date('Y-m-d');

        // Check if the user is Kasir
        if ($user->isKasir()) {
            return view('dashboard.index', compact('user'));
        } else {
            // --- ADMIN & IT ADMIN VIEW DATA ---
            // Data Grafik Penjualan (Harian, Mingguan, Bulanan)
            $salesHistory = Sale::where('status', 'completed')
                ->where('sold_at', '>=', now()->subMonths(5)->startOfMonth())
                ->orderBy('sold_at', 'asc')
                ->get(['total', 'sold_at']);

            // A. Grafik Harian (7 Hari Terakhir)
            $dailyLabels = [];
            $dailyValues = [];
            for ($i = 6; $i >= 0; $i--) {
                $day = now()->subDays($i);
                $dailyLabels[] = $day->locale('id')->isoFormat('D MMM');
                $dailyValues[] = (float) $salesHistory->filter(fn($s) => $s->sold_at->isSameDay($day))->sum('total');
            }

            // B. Grafik Mingguan (4 Minggu Terakhir)
            $weeklyLabels = [];
            $weeklyValues = [];
            for ($i = 3; $i >= 0; $i--) {
                $startOfWeek = now()->subWeeks($i)->startOfWeek();
                $endOfWeek = now()->subWeeks($i)->endOfWeek();
                $weeklyLabels[] = 'Mgg ' . ($i === 0 ? 'Ini' : now()->subWeeks($i)->format('W'));
                $weeklyValues[] = (float) $salesHistory->filter(fn($s) => $s->sold_at->between($startOfWeek, $endOfWeek))->sum('total');
            }

            // C. Grafik Bulanan (6 Bulan Terakhir)
            $monthlyLabels = [];
            $monthlyValues = [];
            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $monthlyLabels[] = $month->locale('id')->isoFormat('MMMM');
                $monthlyValues[] = (float) $salesHistory->filter(fn($s) => $s->sold_at->isSameMonth($month) && $s->sold_at->isSameYear($month))->sum('total');
            }

            // Pie Chart: Tunai vs QRIS Hari Ini
            $cashSalesTotal = 0;
            $qrisSalesTotal = 0;
            $cashSalesCount = 0;
            $qrisSalesCount = 0;

            $salesToday = Sale::where('status', 'completed')
                ->whereDate('sold_at', $today)
                ->get(['payment_method', 'total']);

            foreach ($salesToday as $sale) {
                if ($sale->payment_method === 'Tunai') {
                    $cashSalesTotal += $sale->total;
                    $cashSalesCount++;
                } elseif ($sale->payment_method === 'QRIS') {
                    $qrisSalesTotal += $sale->total;
                    $qrisSalesCount++;
                }
            }

            // Top 5 Produk Terlaris Bulan Ini
            $topProducts = SaleItem::select('product_id', 'product_name', 'product_code')
                ->selectRaw('SUM(quantity) as total_qty, SUM(subtotal) as total_revenue')
                ->whereHas('sale', function($q) {
                    $q->where('status', 'completed')
                      ->whereMonth('sold_at', now()->month)
                      ->whereYear('sold_at', now()->year);
                })
                ->groupBy('product_id', 'product_name', 'product_code')
                ->orderByDesc('total_qty')
                ->limit(5)
                ->get();

            // Alerts Data: Produk Kadaluarsa
            $lowStockProducts = Product::active()
                ->lowStock()
                ->with(['category', 'unit'])
                ->orderBy('stock', 'asc')
                ->limit(5)
                ->get();

            $expiredSoon30 = Product::active()
                ->whereNotNull('expired_date')
                ->where('expired_date', '<=', now()->addDays(30))
                ->where('expired_date', '>=', today())
                ->with(['category', 'unit'])
                ->orderBy('expired_date', 'asc')
                ->limit(5)
                ->get();

            $expiredSoon30Count = Product::active()
                ->whereNotNull('expired_date')
                ->where('expired_date', '<=', now()->addDays(30))
                ->where('expired_date', '>=', today())
                ->count();

            $expiredSoon60 = Product::active()
                ->whereNotNull('expired_date')
                ->where('expired_date', '<=', now()->addDays(60))
                ->where('expired_date', '>', now()->addDays(30))
                ->with(['category', 'unit'])
                ->orderBy('expired_date', 'asc')
                ->limit(5)
                ->get();

            $expiredSoon60Count = Product::active()
                ->whereNotNull('expired_date')
                ->where('expired_date', '<=', now()->addDays(60))
                ->where('expired_date', '>', now()->addDays(30))
                ->count();

            return view('dashboard.index', compact(
                'user',
                'dailyLabels',
                'dailyValues',
                'weeklyLabels',
                'weeklyValues',
                'monthlyLabels',
                'monthlyValues',
                'cashSalesTotal',
                'qrisSalesTotal',
                'cashSalesCount',
                'qrisSalesCount',
                'topProducts',
                'lowStockProducts',
                'expiredSoon30',
                'expiredSoon30Count',
                'expiredSoon60',
                'expiredSoon60Count'
            ));
        }
    }
}

