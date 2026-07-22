<?php

namespace App\Services;

use App\Models\PartnerOrder;
use App\Models\Sale;
use Illuminate\Support\Collection;

class InvoiceReceivableService
{
    /** @return array{total_unpaid: int, total_overdue: int, total_paid_month: int, amount_unpaid: float, amount_overdue: float} */
    public static function stats(): array
    {
        $posBase = Sale::query()->where('payment_method', 'invoice')->where('status', 'completed');
        $mitraBase = PartnerOrder::query()
            ->where('payment_method', PartnerOrder::PAY_INVOICE)
            ->whereNotIn('status', [PartnerOrder::STATUS_CANCELLED]);

        $posUnpaid = (clone $posBase)->where('payment_status', 'unpaid');
        $mitraUnpaid = (clone $mitraBase)->where('payment_status', PartnerOrder::PAYMENT_UNPAID);

        return [
            'total_unpaid'     => $posUnpaid->count() + $mitraUnpaid->count(),
            'total_overdue'    => (clone $posUnpaid)->where('due_date', '<', now()->startOfDay())->count()
                + PartnerOrder::creditOverdue()->count(),
            'total_paid_month' => (clone $posBase)->where('payment_status', 'paid')
                    ->whereMonth('settled_at', now()->month)
                    ->whereYear('settled_at', now()->year)
                    ->count()
                + (clone $mitraBase)->where('payment_status', PartnerOrder::PAYMENT_PAID)
                    ->whereNotNull('settled_at')
                    ->whereMonth('settled_at', now()->month)
                    ->whereYear('settled_at', now()->year)
                    ->count(),
            'amount_unpaid'    => (float) $posUnpaid->sum('total') + (float) $mitraUnpaid->sum('total'),
            'amount_overdue'   => (float) (clone $posUnpaid)->where('due_date', '<', now()->startOfDay())->sum('total')
                + (float) PartnerOrder::creditOverdue()->sum('total'),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public static function rows(
        string $filterStatus = 'all',
        string $search = '',
        ?string $startDate = null,
        ?string $endDate = null,
    ): Collection {
        $rows = collect();

        $posQuery = Sale::with(['user', 'partner'])
            ->where('payment_method', 'invoice')
            ->where('status', 'completed');

        if ($filterStatus === 'unpaid') {
            $posQuery->where('payment_status', 'unpaid');
        } elseif ($filterStatus === 'overdue') {
            $posQuery->where('payment_status', 'unpaid')->where('due_date', '<', now()->startOfDay());
        } elseif ($filterStatus === 'paid') {
            $posQuery->where('payment_status', 'paid');
        }

        if ($search !== '') {
            $posQuery->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', '%' . $search . '%')
                    ->orWhere('customer_name', 'like', '%' . $search . '%');
            });
        }

        if ($startDate) {
            $posQuery->where('sold_at', '>=', $startDate . ' 00:00:00');
        }
        if ($endDate) {
            $posQuery->where('sold_at', '<=', $endDate . ' 23:59:59');
        }

        foreach ($posQuery->get() as $sale) {
            $rows->push(self::mapSale($sale));
        }

        $mitraQuery = PartnerOrder::with(['partner', 'user'])
            ->where('payment_method', PartnerOrder::PAY_INVOICE)
            ->whereNotIn('status', [PartnerOrder::STATUS_CANCELLED]);

        if ($filterStatus === 'unpaid') {
            $mitraQuery->where('payment_status', PartnerOrder::PAYMENT_UNPAID);
        } elseif ($filterStatus === 'overdue') {
            $mitraQuery->creditOverdue();
        } elseif ($filterStatus === 'paid') {
            $mitraQuery->where('payment_status', PartnerOrder::PAYMENT_PAID);
        }

        if ($search !== '') {
            $mitraQuery->where(function ($q) use ($search) {
                $q->where('order_no', 'like', '%' . $search . '%')
                    ->orWhereHas('partner', fn ($p) => $p->where('name', 'like', '%' . $search . '%'));
            });
        }

        if ($startDate) {
            $mitraQuery->where('created_at', '>=', $startDate . ' 00:00:00');
        }
        if ($endDate) {
            $mitraQuery->where('created_at', '<=', $endDate . ' 23:59:59');
        }

        foreach ($mitraQuery->get() as $order) {
            $rows->push(self::mapMitraOrder($order));
        }

        return $rows->sortByDesc(fn ($row) => $row['transacted_at']?->timestamp ?? 0)->values();
    }

    /** @return array<string, mixed> */
    private static function mapSale(Sale $sale): array
    {
        $isOverdue = $sale->payment_status === 'unpaid' && $sale->due_date && $sale->due_date->isPast();

        $buyerName = $sale->customer_name;
        if ($sale->partner_id) {
            $buyerName = ($sale->partner?->name ?: $sale->customer_name) . ' · Mitra';
        }

        return [
            'source'            => 'pos',
            'id'                => $sale->id,
            'ref'               => $sale->invoice_no,
            'customer_name'     => $buyerName,
            'cashier'           => $sale->user?->name ?? '-',
            'transacted_at'     => $sale->sold_at,
            'due_date'          => $sale->due_date,
            'total'             => (float) $sale->total,
            'payment_status'    => $sale->payment_status,
            'settlement_method' => $sale->settlement_method,
            'is_overdue'        => $isOverdue,
            'detail_url'        => route('sales.show', $sale->id),
            'print_url'         => route('invoices.print', $sale->id),
            'export_url'        => route('invoices.export', $sale->id),
            'can_pay'           => $sale->payment_status === 'unpaid' && $sale->status !== 'cancelled',
            'is_partner'        => (bool) $sale->partner_id,
        ];
    }

    /** @return array<string, mixed> */
    private static function mapMitraOrder(PartnerOrder $order): array
    {
        return [
            'source'            => 'mitra',
            'id'                => $order->id,
            'ref'               => $order->order_no,
            'customer_name'     => $order->partner?->name ?? '—',
            'cashier'           => $order->user?->name ?? 'Portal Mitra',
            'transacted_at'     => $order->created_at,
            'due_date'          => $order->due_date,
            'total'             => (float) $order->total,
            'payment_status'    => $order->payment_status === PartnerOrder::PAYMENT_PAID ? 'paid' : 'unpaid',
            'settlement_method' => $order->settlement_method,
            'is_overdue'        => $order->isCreditOverdue(),
            'detail_url'        => route('partner-orders.show', $order),
            'print_url'         => null,
            'export_url'        => null,
            'can_pay'           => $order->payment_status === PartnerOrder::PAYMENT_UNPAID,
            'pay_url'           => route('credits.pay-mitra', $order),
        ];
    }
}
