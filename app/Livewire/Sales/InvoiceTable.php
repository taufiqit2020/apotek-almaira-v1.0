<?php

namespace App\Livewire\Sales;

use App\Models\PartnerOrder;
use App\Models\Sale;
use App\Services\ActivityLogService;
use App\Services\InvoiceReceivableService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class InvoiceTable extends Component
{
    use WithPagination;

    #[Url(keep: true)]
    public string $filterStatus = 'all';

    #[Url(keep: true)]
    public string $search = '';

    #[Url(keep: true)]
    public string $startDate = '';

    #[Url(keep: true)]
    public string $endDate = '';

    public int $perPage = 20;

    public bool   $showPayModal     = false;
    public ?int   $payingSaleId     = null;
    public string $settlementMethod = 'cash';
    public ?array $payingSaleData   = null;

    public bool   $showMitraPayModal = false;
    public ?array $payingMitraData   = null;

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate   = now()->format('Y-m-d');
    }

    public function updatingSearch(): void       { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }
    public function updatingStartDate(): void    { $this->resetPage(); }
    public function updatingEndDate(): void      { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->search       = '';
        $this->filterStatus = 'all';
        $this->startDate    = now()->startOfMonth()->format('Y-m-d');
        $this->endDate      = now()->format('Y-m-d');
        $this->resetPage();
    }

    public function openPayModal(int $saleId): void
    {
        $sale = Sale::find($saleId);
        if (!$sale) {
            return;
        }

        $this->payingSaleId     = $saleId;
        $this->settlementMethod = 'cash';
        $this->payingSaleData   = [
            'id'         => $sale->id,
            'invoice_no' => $sale->invoice_no,
            'customer'   => $sale->customer_name,
            'total'      => $sale->total,
            'due_date'   => $sale->due_date?->format('d M Y'),
            'is_overdue' => $sale->isOverdue(),
        ];
        $this->showPayModal = true;
    }

    public function openMitraPayModal(int $orderId): void
    {
        $order = PartnerOrder::with('partner')->find($orderId);
        if (!$order || $order->payment_method !== PartnerOrder::PAY_INVOICE
            || $order->payment_status !== PartnerOrder::PAYMENT_UNPAID) {
            return;
        }

        $this->payingMitraData = [
            'id'         => $order->id,
            'order_no'   => $order->order_no,
            'customer'   => $order->partner?->name ?? '—',
            'total'      => $order->total,
            'due_date'   => $order->due_date?->format('d M Y'),
            'is_overdue' => $order->isCreditOverdue(),
            'pay_url'    => route('credits.pay-mitra', $order),
        ];
        $this->showMitraPayModal = true;
    }

    public function closePayModal(): void
    {
        $this->showPayModal   = false;
        $this->payingSaleId   = null;
        $this->payingSaleData = null;
    }

    public function closeMitraPayModal(): void
    {
        $this->showMitraPayModal = false;
        $this->payingMitraData   = null;
    }

    public function processPayment(): void
    {
        if (!$this->payingSaleId) {
            return;
        }

        $sale = Sale::find($this->payingSaleId);
        if (!$sale || $sale->payment_status === 'paid') {
            $this->closePayModal();
            $this->dispatch('toast', type: 'error', message: 'Invoice sudah dilunasi atau tidak ditemukan.');

            return;
        }

        $methodLabel = $this->settlementMethod === 'cash' ? 'Tunai' : 'Transfer';

        $sale->update([
            'payment_status'    => 'paid',
            'settlement_method' => $this->settlementMethod,
            'settled_at'        => now(),
            'settled_by'        => Auth::id(),
        ]);

        ActivityLogService::log(
            'UPDATE',
            'Invoice',
            "Pelunasan invoice {$sale->invoice_no} sebesar Rp " .
            number_format($sale->total, 0, ',', '.') . " — Metode: {$methodLabel}"
        );

        $this->closePayModal();
        $this->dispatch('toast', type: 'success', message: "Invoice {$sale->invoice_no} berhasil dilunasi dengan {$methodLabel}!");
    }

    public function render()
    {
        $allRows = InvoiceReceivableService::rows(
            $this->filterStatus,
            $this->search,
            $this->startDate ?: null,
            $this->endDate ?: null,
        );

        $page     = LengthAwarePaginator::resolveCurrentPage();
        $items    = $allRows->slice(($page - 1) * $this->perPage, $this->perPage)->values();
        $invoices = new LengthAwarePaginator(
            $items,
            $allRows->count(),
            $this->perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $stats = InvoiceReceivableService::stats();

        return view('livewire.sales.invoice-table', compact('invoices', 'stats'));
    }
}
