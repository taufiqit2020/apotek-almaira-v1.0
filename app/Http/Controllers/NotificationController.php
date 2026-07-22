<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $lowStock = Product::active()
            ->whereColumn('stock', '<=', 'stock_min')
            ->orderBy('stock', 'asc')
            ->limit(30)
            ->get(['id', 'name', 'code', 'stock', 'stock_min']);

        $expiringSoon = Product::active()
            ->whereNotNull('expired_date')
            ->whereBetween('expired_date', [today(), today()->addDays(30)])
            ->orderBy('expired_date', 'asc')
            ->limit(30)
            ->get(['id', 'name', 'code', 'expired_date']);

        $expiringData = $expiringSoon->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'code' => $p->code,
                'expired_date' => $p->expired_date->format('d/m/Y'),
                'days_left' => today()->diffInDays($p->expired_date, false),
            ];
        });

        // Count unique products
        $uniqueProductIds = array_unique(array_merge(
            $lowStock->pluck('id')->toArray(),
            $expiringSoon->pluck('id')->toArray()
        ));
        $totalCount = count($uniqueProductIds);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'low_stock' => $lowStock->take(10),
                'expiring_soon' => $expiringData->take(10),
                'total_count' => $totalCount,
            ]);
        }

        return view('notifications.index', compact('lowStock', 'expiringData', 'totalCount'));
    }
}
