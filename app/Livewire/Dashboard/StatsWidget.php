<?php

namespace App\Livewire\Dashboard;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class StatsWidget extends Component
{
    public float $salesTodayAmount = 0;
    public int $salesTodayCount = 0;
    public float $profitToday = 0;
    public int $lowStockCount = 0;
    public bool $isAdmin = false;

    public function mount(): void
    {
        $this->isAdmin = ! Auth::user()->isKasir();
        $this->loadStats();
    }

    #[On('dashboard-refresh')]
    public function refreshStats(): void
    {
        $this->loadStats();
    }

    protected function loadStats(): void
    {
        $today = today()->toDateString();
        $user  = Auth::user();

        $query = Sale::where('status', 'completed')->whereDate('sold_at', $today);

        if ($user->isKasir()) {
            $query->where('user_id', $user->id);
        }

        $this->salesTodayAmount = (float) $query->sum('total');
        $this->salesTodayCount  = (clone $query)->count();

        if ($this->isAdmin) {
            $this->profitToday = (float) (Sale::whereDate('sold_at', $today)
                ->where('sales.status', 'completed')
                ->join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->selectRaw('SUM(sale_items.subtotal - (products.purchase_price * sale_items.quantity)) as profit')
                ->value('profit') ?? 0);

            $this->lowStockCount = Product::active()->lowStock()->count();
        }
    }

    public function render()
    {
        return view('livewire.dashboard.stats-widget');
    }
}
