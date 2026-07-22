<?php

namespace App\Livewire\Dashboard;

use App\Models\Sale;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class RecentTransactions extends Component
{
    public $transactions;

    public function mount(): void
    {
        $this->loadTransactions();
    }

    #[On('dashboard-refresh')]
    public function refreshTransactions(): void
    {
        $this->loadTransactions();
    }

    protected function loadTransactions(): void
    {
        $user  = Auth::user();
        $today = today()->toDateString();

        $query = Sale::with(['user'])->latest('sold_at')->limit(10);

        if ($user->isKasir()) {
            $query->whereDate('sold_at', $today)->where('user_id', $user->id);
        }

        $this->transactions = $query->get();
    }

    public function render()
    {
        return view('livewire.dashboard.recent-transactions');
    }
}
