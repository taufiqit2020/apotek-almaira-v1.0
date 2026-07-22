<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Sale extends Model {
    protected $fillable = [
        'invoice_no','user_id','customer_name','payment_method',
        'subtotal','discount_percent','discount_amount',
        'ppn_active','ppn_percent','ppn_amount','ppn_bearer',
        'total','cash_received','change_amount','notes','status','sold_at',
        'customer_id','partner_id','points_earned','points_redeemed','prescription_id',
        'due_date','payment_status',
        'settlement_method','settled_at','settled_by',
    ];
    protected $casts = [
        'ppn_active'      => 'boolean',
        'subtotal'        => 'decimal:2',
        'discount_percent'=> 'decimal:2',
        'discount_amount' => 'decimal:2',
        'ppn_percent'     => 'decimal:2',
        'ppn_amount'      => 'decimal:2',
        'total'           => 'decimal:2',
        'cash_received'   => 'decimal:2',
        'change_amount'   => 'decimal:2',
        'sold_at'         => 'datetime',
        'due_date'        => 'date',
        'settled_at'      => 'datetime',
        'points_earned'   => 'integer',
        'points_redeemed' => 'integer',
    ];

    // ──────────────────────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────────────────────
    public function user()         { return $this->belongsTo(User::class); }
    public function customer()     { return $this->belongsTo(Customer::class); }
    public function partner()      { return $this->belongsTo(Partner::class); }
    public function prescription() { return $this->belongsTo(Prescription::class); }
    public function items()        { return $this->hasMany(SaleItem::class); }
    public function settledBy()    { return $this->belongsTo(User::class, 'settled_by'); }

    // ──────────────────────────────────────────────────────────────
    // Accessors & Mutators
    // ──────────────────────────────────────────────────────────────
    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->total, 0, ',', '.');
    }

    public function getPaymentMethodAttribute($value)
    {
        return match ($value) {
            'cash'     => 'Tunai',
            'qris'     => 'QRIS',
            'transfer' => 'Transfer',
            'invoice'  => 'Invoice',
            default    => $value,
        };
    }

    public function setPaymentMethodAttribute($value)
    {
        $this->attributes['payment_method'] = match ($value) {
            'Tunai'    => 'cash',
            'QRIS'     => 'qris',
            'Transfer' => 'transfer',
            'Invoice'  => 'invoice',
            default    => $value,
        };
    }

    public function getPpnBearerAttribute($value)
    {
        return $value === 'buyer' ? 'Ditanggung Pembeli' : ($value === 'seller' ? 'Ditanggung Penjual' : $value);
    }

    public function setPpnBearerAttribute($value)
    {
        $this->attributes['ppn_bearer'] = $value === 'Ditanggung Pembeli' ? 'buyer' : ($value === 'Ditanggung Penjual' ? 'seller' : $value);
    }

    // ──────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────
    public function isOverdue(): bool
    {
        return $this->attributes['payment_method'] === 'invoice'
            && $this->payment_status === 'unpaid'
            && $this->due_date !== null
            && $this->due_date->isPast();
    }

    public function isDueToday(): bool
    {
        return $this->attributes['payment_method'] === 'invoice'
            && $this->payment_status === 'unpaid'
            && $this->due_date !== null
            && $this->due_date->isToday();
    }

    public function isDueSoon(): bool
    {
        return $this->attributes['payment_method'] === 'invoice'
            && $this->payment_status === 'unpaid'
            && $this->due_date !== null
            && $this->due_date->isFuture()
            && $this->due_date->diffInDays(now()) <= 7;
    }

    // ──────────────────────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────────────────────
    public function scopeUnpaidInvoices($q)
    {
        return $q->where('payment_method', 'invoice')
                 ->where('payment_status', 'unpaid')
                 ->where('status', 'completed');
    }

    public function scopeOverdueInvoices($q)
    {
        return $q->where('payment_method', 'invoice')
                 ->where('payment_status', 'unpaid')
                 ->where('status', 'completed')
                 ->where('due_date', '<', now()->startOfDay());
    }

    public function scopePaidInvoices($q)
    {
        return $q->where('payment_method', 'invoice')
                 ->where('payment_status', 'paid')
                 ->where('status', 'completed');
    }

    // ──────────────────────────────────────────────────────────────
    // Document number generation & labels
    // ──────────────────────────────────────────────────────────────
    public function getRawPaymentMethod(): string
    {
        return $this->attributes['payment_method'] ?? '';
    }

    public function isInvoicePayment(): bool
    {
        return $this->getRawPaymentMethod() === 'invoice';
    }

    public function getDocumentLabelAttribute(): string
    {
        return $this->isInvoicePayment() ? 'PENJUALAN INVOICE' : 'NO FAKTUR PENJUALAN';
    }

    public function getDocumentDisplayAttribute(): string
    {
        return $this->document_label . ' : ' . $this->invoice_no;
    }

    public static function generateDocumentNo(string $paymentMethod): string
    {
        $isInvoice = in_array($paymentMethod, ['Invoice', 'invoice'], true);
        $code      = $isInvoice ? 'INV' : 'FPJ';
        $period    = now()->format('m/Y');
        $prefix    = "{$code}-";
        $suffix    = "-NMF/{$period}";

        $last = static::where('invoice_no', 'like', $prefix . '%' . $suffix)
            ->orderByDesc('id')
            ->first();

        $next = 1;
        if ($last && preg_match('/^' . preg_quote($prefix, '/') . '(\d{4})' . preg_quote($suffix, '/') . '$/', $last->invoice_no, $m)) {
            $next = (int) $m[1] + 1;
        }

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT) . $suffix;
    }

    /** @deprecated Use generateDocumentNo() */
    public static function generateInvoiceNo(string $paymentMethod = 'Tunai'): string
    {
        return static::generateDocumentNo($paymentMethod);
    }

    /**
     * Nomor Faktur Penjualan khusus Mitra Katalog untuk pembayaran Tunai, QRIS & Transfer.
     * Format: FTR-0001/NMF/07/2026 — satu urutan bersama antara Sale (tunai/qris)
     * dan PartnerOrder (transfer), reset otomatis setiap bulan.
     */
    public static function generateMitraInvoiceNo(): string
    {
        $prefix = 'FTR-';
        $period = now()->format('m/Y');
        $suffix = '/NMF/' . $period;
        $pattern = '/^' . preg_quote($prefix, '/') . '(\d+)' . preg_quote($suffix, '/') . '$/';

        $seq = 0;

        $lastSale = static::where('invoice_no', 'like', $prefix . '%' . $suffix)
            ->orderByDesc('id')
            ->value('invoice_no');
        if ($lastSale && preg_match($pattern, $lastSale, $m)) {
            $seq = max($seq, (int) $m[1]);
        }

        $lastOrder = PartnerOrder::where('order_no', 'like', $prefix . '%' . $suffix)
            ->orderByDesc('id')
            ->value('order_no');
        if ($lastOrder && preg_match($pattern, $lastOrder, $m)) {
            $seq = max($seq, (int) $m[1]);
        }

        return $prefix . str_pad((string) ($seq + 1), 4, '0', STR_PAD_LEFT) . $suffix;
    }
}
