<?php

namespace App\Livewire\Shared;

use App\Models\Product;
use Livewire\Attributes\On;
use Livewire\Component;

class NotificationBadge extends Component
{
    public int $lowStockCount    = 0;
    public int $expiredSoonCount = 0;
    public int $total            = 0;

    public function mount(): void
    {
        $this->loadCounts();
    }

    #[On('dashboard-refresh')]
    public function refreshCounts(): void
    {
        $this->loadCounts();
    }

    protected function loadCounts(): void
    {
        $this->lowStockCount = Product::active()->lowStock()->count();
        $this->expiredSoonCount = Product::active()
            ->whereNotNull('expired_date')
            ->where('expired_date', '<=', now()->addDays(30))
            ->where('expired_date', '>=', today())
            ->count();
        $this->total = $this->lowStockCount + $this->expiredSoonCount;
    }

    public function render()
    {
        return view('livewire.shared.notification-badge');
    }
}
