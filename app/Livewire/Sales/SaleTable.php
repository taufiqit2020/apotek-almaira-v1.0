<?php

namespace App\Livewire\Sales;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class SaleTable extends Component
{
    use WithPagination;

    #[Url(as: 'q', keep: true)]
    public string $search = '';

    #[Url(keep: true)]
    public string $startDate = '';

    #[Url(keep: true)]
    public string $endDate = '';

    #[Url(keep: true)]
    public string $paymentMethod = '';

    #[Url(keep: true)]
    public string $status = '';

    #[Url(keep: true)]
    public string $userId = '';

    #[Url(keep: true)]
    public string $paymentStatus = '';

    public int $perPage = 20;

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate   = now()->format('Y-m-d');
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStartDate(): void { $this->resetPage(); }
    public function updatingEndDate(): void { $this->resetPage(); }
    public function updatingPaymentMethod(): void { $this->resetPage(); }
    public function updatingStatus(): void { $this->resetPage(); }
    public function updatingUserId(): void { $this->resetPage(); }
    public function updatingPaymentStatus(): void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->search        = '';
        $this->startDate     = now()->startOfMonth()->format('Y-m-d');
        $this->endDate       = now()->format('Y-m-d');
        $this->paymentMethod = '';
        $this->status        = '';
        $this->userId        = '';
        $this->paymentStatus = '';
        $this->resetPage();
    }

    public function render()
    {
        $query = Sale::with(['user', 'items'])->latest('sold_at');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('invoice_no', 'like', '%' . $this->search . '%')
                  ->orWhere('customer_name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->startDate) {
            $query->where('sold_at', '>=', $this->startDate . ' 00:00:00');
        }

        if ($this->endDate) {
            $query->where('sold_at', '<=', $this->endDate . ' 23:59:59');
        }

        if ($this->paymentMethod) {
            $query->where('payment_method', $this->paymentMethod);
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->userId) {
            $query->where('user_id', $this->userId);
        }

        if ($this->paymentStatus) {
            $query->where('payment_status', $this->paymentStatus);
        }

        if (Auth::user()->isKasir()) {
            $query->where('user_id', Auth::id());
        }

        $sales     = $query->paginate($this->perPage);
        $totalRevenue = $query->sum('total');
        $kasirList = User::query()
            ->whereHas('role', fn ($q) => $q->whereIn('slug', ['super_admin', 'admin_keuangan', 'kasir', 'staff_operasional']))
            ->orderBy('name')
            ->get();

        return view('livewire.sales.sale-table', compact('sales', 'totalRevenue', 'kasirList'));
    }
}
