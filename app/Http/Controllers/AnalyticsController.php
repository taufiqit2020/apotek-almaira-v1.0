<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        // 1. Top 10 Best Sellers (last 30 days)
        $topSellers = SaleItem::select('product_name', DB::raw('SUM(quantity) as total_qty'))
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.status', 'completed')
            ->where('sales.sold_at', '>=', now()->subDays(30))
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        // 2. Deadstock Alert (stock > 0, no sales in last 30 days)
        $deadstock = Product::where('stock', '>', 0)
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('sale_items')
                  ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                  ->whereRaw('sale_items.product_id = products.id')
                  ->where('sales.sold_at', '>=', now()->subDays(30))
                  ->where('sales.status', 'completed');
            })
            ->orderByDesc('stock')
            ->limit(15)
            ->get();

        // 3. Profit Margin Analysis (HPP vs Sell Price)
        $productsWithMargin = Product::where('sell_price', '>', 0)
            ->orderBy('name')
            ->paginate(15, ['*'], 'margin_page');

        // 4. Comparative Trends
        $thisMonthRevenue = Sale::where('status', 'completed')
            ->whereMonth('sold_at', now()->month)
            ->whereYear('sold_at', now()->year)
            ->sum('total');

        $lastMonthRevenue = Sale::where('status', 'completed')
            ->whereMonth('sold_at', now()->subMonth()->month)
            ->whereYear('sold_at', now()->subMonth()->year)
            ->sum('total');

        $thisMonthTx = Sale::where('status', 'completed')
            ->whereMonth('sold_at', now()->month)
            ->whereYear('sold_at', now()->year)
            ->count();

        $lastMonthTx = Sale::where('status', 'completed')
            ->whereMonth('sold_at', now()->subMonth()->month)
            ->whereYear('sold_at', now()->subMonth()->year)
            ->count();

        return view('analytics.index', compact(
            'topSellers', 'deadstock', 'productsWithMargin',
            'thisMonthRevenue', 'lastMonthRevenue',
            'thisMonthTx', 'lastMonthTx'
        ));
    }

    public function shiftReport(Request $request)
    {
        $cashierId = $request->user_id ?: auth()->id();
        $date = $request->date ?: date('Y-m-d');
        
        $sales = Sale::where('user_id', $cashierId)
            ->whereDate('sold_at', $date)
            ->where('status', 'completed')
            ->get();

        $totalTunai = $sales->where('payment_method', 'Tunai')->sum('total');
        $totalQris = $sales->where('payment_method', 'QRIS')->sum('total');
        $totalAmount = $sales->sum('total');
        $totalDiscount = $sales->sum('discount_amount');
        $totalTxCount = $sales->count();
        
        $cashierName = \App\Models\User::find($cashierId)->name;

        // Get cashiers list for select box
        $cashiers = \App\Models\User::orderBy('name')->get();

        return view('analytics.shift_report', compact(
            'sales', 'totalTunai', 'totalQris', 'totalAmount', 'totalDiscount', 
            'totalTxCount', 'cashierName', 'cashiers', 'cashierId', 'date'
        ));
    }
}
