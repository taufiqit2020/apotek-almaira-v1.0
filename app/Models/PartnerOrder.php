<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerOrder extends Model
{
    public const STATUS_SUBMITTED  = 'submitted';
    public const STATUS_CONFIRMED  = 'confirmed';
    public const STATUS_FULFILLED  = 'fulfilled';
    public const STATUS_CANCELLED  = 'cancelled';

    public const PAY_TRANSFER = 'transfer';
    public const PAY_COD      = 'cod';
    public const PAY_INVOICE  = 'invoice';

    public const PAYMENT_UNPAID     = 'unpaid';
    public const PAYMENT_AWAITING  = 'awaiting_confirmation';
    public const PAYMENT_PAID      = 'paid';
    public const PAYMENT_CANCELLED = 'cancelled';

    protected $fillable = [
        'order_no', 'partner_id', 'user_id',
        'status', 'payment_method', 'payment_status',
        'subtotal', 'total', 'price_mode_snapshot',
        'discount_amount', 'ppn_enabled', 'ppn_percent', 'ppn_amount', 'ppn_bearer',
        'shipping_address', 'pic_name', 'pic_phone', 'notes',
        'transfer_proof', 'transfer_proof_at', 'due_date',
        'settlement_method', 'settlement_proof', 'settlement_proof_at', 'settled_at', 'settled_by',
        'admin_notes', 'cancel_reason',
        'confirmed_by', 'confirmed_at', 'fulfilled_at', 'cancelled_at',
    ];

    protected $casts = [
        'subtotal'          => 'decimal:2',
        'total'             => 'decimal:2',
        'discount_amount'   => 'decimal:2',
        'ppn_enabled'       => 'boolean',
        'ppn_percent'       => 'decimal:2',
        'ppn_amount'        => 'decimal:2',
        'due_date'          => 'date',
        'settled_at'        => 'datetime',
        'settlement_proof_at' => 'datetime',
        'transfer_proof_at' => 'datetime',
        'confirmed_at'      => 'datetime',
        'fulfilled_at'      => 'datetime',
        'cancelled_at'      => 'datetime',
    ];

    public static function statusOptions(): array
    {
        return [
            self::STATUS_SUBMITTED => 'Diajukan',
            self::STATUS_CONFIRMED => 'Dikonfirmasi',
            self::STATUS_FULFILLED => 'Selesai',
            self::STATUS_CANCELLED => 'Dibatalkan',
        ];
    }

    public static function paymentMethodOptions(): array
    {
        return [
            self::PAY_TRANSFER => 'Transfer Bank',
            self::PAY_COD      => 'COD (Bayar di Tempat)',
            self::PAY_INVOICE  => 'Invoice Tempo',
        ];
    }

    public static function paymentStatusOptions(): array
    {
        return [
            self::PAYMENT_UNPAID    => 'Belum Bayar',
            self::PAYMENT_AWAITING  => 'Menunggu Konfirmasi',
            self::PAYMENT_PAID      => 'Lunas',
            self::PAYMENT_CANCELLED => 'Dibatalkan',
        ];
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function confirmer()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function settler()
    {
        return $this->belongsTo(User::class, 'settled_by');
    }

    public function scopeCreditUnpaid($q)
    {
        return $q->where('payment_method', self::PAY_INVOICE)
            ->where('payment_status', self::PAYMENT_UNPAID)
            ->whereNotIn('status', [self::STATUS_CANCELLED]);
    }

    public function scopeCreditPaid($q)
    {
        return $q->where('payment_method', self::PAY_INVOICE)
            ->where('payment_status', self::PAYMENT_PAID)
            ->whereNotNull('settled_at')
            ->whereNotIn('status', [self::STATUS_CANCELLED]);
    }

    public function scopeCreditOverdue($q)
    {
        return $q->creditUnpaid()->where('due_date', '<', now()->startOfDay());
    }

    public function isCreditOverdue(): bool
    {
        return $this->payment_method === self::PAY_INVOICE
            && $this->payment_status === self::PAYMENT_UNPAID
            && $this->due_date
            && $this->due_date->isPast();
    }

    /**
     * @return array{subtotal: float, discount_amount: float, net_subtotal: float, ppn_enabled: bool, ppn_percent: float, ppn_amount: float, ppn_bearer: ?string, ppn_bearer_label: ?string, grand_total: float}
     */
    public function totalsBreakdown(): array
    {
        $subtotal = (float) $this->subtotal;
        $discount = (float) ($this->discount_amount ?? 0);
        $net      = max(0, $subtotal - $discount);
        $grand    = (float) $this->total;

        if ($this->ppn_enabled !== null) {
            $bearer      = $this->ppn_bearer;
            $bearerLabel = $bearer
                ? (Partner::ppnBearerOptions()[$bearer] ?? $bearer)
                : null;

            return [
                'subtotal'          => $subtotal,
                'discount_amount'   => $discount,
                'net_subtotal'      => $net,
                'ppn_enabled'       => (bool) $this->ppn_enabled,
                'ppn_percent'       => (float) ($this->ppn_percent ?? 0),
                'ppn_amount'        => (float) ($this->ppn_amount ?? 0),
                'ppn_bearer'        => $bearer,
                'ppn_bearer_label'  => $this->ppn_enabled ? $bearerLabel : null,
                'grand_total'       => $grand,
            ];
        }

        // PO lama tanpa snapshot PPN — infer dari selisih total vs net subtotal
        $inferredPpn = max(0, round($grand - $net, 2));
        if ($inferredPpn > 0.009) {
            $partner = $this->relationLoaded('partner') ? $this->partner : $this->partner()->first();
            $percent = $net > 0 ? round(($inferredPpn / $net) * 100, 2) : 0.0;
            $bearer  = $partner?->ppn_bearer;

            if ($partner?->ppn_enabled && (float) $partner->ppn_percent > 0) {
                $percent = (float) $partner->ppn_percent;
            }

            $bearerLabel = $bearer
                ? (Partner::ppnBearerOptions()[$bearer] ?? $bearer)
                : null;

            return [
                'subtotal'          => $subtotal,
                'discount_amount'   => $discount,
                'net_subtotal'      => $net,
                'ppn_enabled'       => true,
                'ppn_percent'       => $percent,
                'ppn_amount'        => $inferredPpn,
                'ppn_bearer'        => $bearer,
                'ppn_bearer_label'  => $bearerLabel,
                'grand_total'       => $grand,
            ];
        }

        return [
            'subtotal'          => $subtotal,
            'discount_amount'   => $discount,
            'net_subtotal'      => $net,
            'ppn_enabled'       => false,
            'ppn_percent'       => 0.0,
            'ppn_amount'        => 0.0,
            'ppn_bearer'        => null,
            'ppn_bearer_label'  => null,
            'grand_total'       => $grand,
        ];
    }

    public function items()
    {
        return $this->hasMany(PartnerOrderItem::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusOptions()[$this->status] ?? $this->status;
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return self::paymentMethodOptions()[$this->payment_method] ?? $this->payment_method;
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return self::paymentStatusOptions()[$this->payment_status] ?? $this->payment_status;
    }

    public function canBeCancelledByPartner(): bool
    {
        return $this->status === self::STATUS_SUBMITTED
            && $this->payment_status !== self::PAYMENT_PAID;
    }

    public function canUploadProof(): bool
    {
        return $this->payment_method === self::PAY_TRANSFER
            && !in_array($this->status, [self::STATUS_CANCELLED, self::STATUS_FULFILLED], true)
            && $this->payment_status !== self::PAYMENT_PAID;
    }

    /**
     * Nomor dokumen PO.
     * - Transfer: FTR-0001/NMF/07/2026 (satu urutan bersama dengan Sale Tunai/QRIS Mitra)
     * - COD: PO-YYYYMMDD-0001
     * - Invoice: INV-0001/NMF/RS-ALMANSYUR/07/2026 (berdasarkan jenis + nama mitra)
     */
    public static function generateOrderNo(?Partner $partner = null, ?string $paymentMethod = null): string
    {
        if ($partner && $paymentMethod === self::PAY_INVOICE) {
            return static::generateInvoiceOrderNo($partner);
        }

        if ($paymentMethod === self::PAY_TRANSFER) {
            return Sale::generateMitraInvoiceNo();
        }

        $prefix = 'PO-' . now()->format('Ymd') . '-';
        $last = static::where('order_no', 'like', $prefix . '%')
            ->orderByDesc('order_no')
            ->value('order_no');

        $seq = 1;
        if ($last && preg_match('/-(\d+)$/', $last, $m)) {
            $seq = ((int) $m[1]) + 1;
        }

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Format: INV-{seq}/NMF/{TIPE}-{SINGKATAN}/{bulan}/{tahun}
     * Sequence naik otomatis per mitra per bulan/tahun.
     */
    public static function generateInvoiceOrderNo(Partner $partner): string
    {
        $mid    = $partner->invoiceMidSegment();
        $period = now()->format('m/Y');
        $suffix = '/' . $mid . '/' . $period;

        $last = static::where('partner_id', $partner->id)
            ->where('payment_method', self::PAY_INVOICE)
            ->where('order_no', 'like', 'INV-%/' . $period)
            ->orderByDesc('id')
            ->value('order_no');

        $seq = 1;
        if ($last && preg_match('/^INV-(\d+)\//', $last, $m)) {
            $seq = ((int) $m[1]) + 1;
        }

        return 'INV-' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT) . '/NMF' . $suffix;
    }
}
