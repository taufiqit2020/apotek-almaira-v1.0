<?php

namespace App\Livewire\Shared;

use App\Models\Partner;
use Livewire\Attributes\On;
use Livewire\Component;

class PartnerNavBadge extends Component
{
    public int $pendingCount = 0;

    public int $selfPendingCount = 0;

    public function mount(): void
    {
        $this->loadCounts();
    }

    #[On('partner-pending-refresh')]
    public function refreshCounts(): void
    {
        $this->loadCounts();
    }

    protected function loadCounts(): void
    {
        $this->pendingCount = Partner::pending()->count();
        $this->selfPendingCount = Partner::pending()
            ->where('registration_source', Partner::SOURCE_SELF)
            ->count();
    }

    public function render()
    {
        return view('livewire.shared.partner-nav-badge');
    }
}
