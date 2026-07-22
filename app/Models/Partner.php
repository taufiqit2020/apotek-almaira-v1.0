<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partner extends Model
{
    use SoftDeletes;

    public const TYPE_RUMAH_SAKIT = 'rumah_sakit';
    public const TYPE_KLINIK      = 'klinik';
    public const TYPE_APOTEK      = 'apotek';
    public const TYPE_UMKM        = 'umkm';
    public const TYPE_INSTANSI    = 'instansi';

    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_INACTIVE = 'inactive';

    public const PRICE_ECERAN = 'eceran';
    public const PRICE_GROSIR = 'grosir';
    public const PRICE_AUTO   = 'auto';

    public const SOURCE_ADMIN = 'admin';
    public const SOURCE_SELF  = 'self';

    protected $fillable = [
        'code', 'name', 'type',
        'npwp', 'nib', 'address', 'city',
        'pic_name', 'phone', 'email',
        'user_id', 'status', 'price_mode',
        'allow_transfer', 'allow_cod', 'invoice_enabled', 'credit_days',
        'ppn_enabled', 'ppn_percent', 'ppn_bearer',
        'notes', 'rejection_reason',
        'approved_at', 'approved_by', 'registration_source',
    ];

    protected $casts = [
        'allow_transfer'  => 'boolean',
        'allow_cod'       => 'boolean',
        'invoice_enabled' => 'boolean',
        'ppn_enabled'     => 'boolean',
        'ppn_percent'     => 'decimal:2',
        'credit_days'     => 'integer',
        'approved_at'     => 'datetime',
    ];

    public static function typeOptions(): array
    {
        return [
            self::TYPE_RUMAH_SAKIT => 'Rumah Sakit',
            self::TYPE_KLINIK      => 'Klinik',
            self::TYPE_APOTEK      => 'Apotek',
            self::TYPE_UMKM        => 'UMKM',
            self::TYPE_INSTANSI    => 'Instansi',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING  => 'Menunggu Approve',
            self::STATUS_APPROVED => 'Aktif',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_INACTIVE => 'Nonaktif',
        ];
    }

    public static function priceModeOptions(): array
    {
        return [
            self::PRICE_ECERAN => 'Eceran',
            self::PRICE_GROSIR => 'Grosir',
            self::PRICE_AUTO   => 'Otomatis (qty)',
        ];
    }

    /** Default komersial saat approve / buat admin, berdasarkan tipe. */
    public static function defaultsForType(string $type): array
    {
        $isWholesaleDefault = in_array($type, [
            self::TYPE_RUMAH_SAKIT,
            self::TYPE_KLINIK,
            self::TYPE_APOTEK,
        ], true);

        $invoiceDefault = in_array($type, [
            self::TYPE_RUMAH_SAKIT,
            self::TYPE_KLINIK,
            self::TYPE_APOTEK,
            self::TYPE_INSTANSI,
        ], true);

        return [
            'price_mode'      => $isWholesaleDefault ? self::PRICE_GROSIR : self::PRICE_ECERAN,
            'allow_transfer'  => true,
            'allow_cod'       => true,
            'invoice_enabled' => $invoiceDefault,
            'credit_days'     => 30,
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function orders()
    {
        return $this->hasMany(PartnerOrder::class);
    }

    public function scopeApproved($q)
    {
        return $q->where('status', self::STATUS_APPROVED);
    }

    public function scopePending($q)
    {
        return $q->where('status', self::STATUS_PENDING);
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function canUseInvoice(): bool
    {
        return $this->isApproved() && $this->invoice_enabled;
    }

    /** Tagihan tempo jatuh tempo: PO mitra + penjualan kasir atas mitra ini. */
    public function hasOverdueInvoice(): bool
    {
        $poOverdue = $this->orders()->creditOverdue()->exists();
        if ($poOverdue) {
            return true;
        }

        return Sale::query()
            ->where('partner_id', $this->id)
            ->where('payment_method', 'invoice')
            ->where('payment_status', 'unpaid')
            ->where('status', 'completed')
            ->whereDate('due_date', '<', now()->toDateString())
            ->exists();
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public static function ppnBearerOptions(): array
    {
        return [
            'buyer'  => 'Pembeli (ditambahkan ke total PO)',
            'seller' => 'PT NMF (ditanggung penjual / absorbed)',
        ];
    }

    public function getPpnBearerLabelAttribute(): ?string
    {
        if (!$this->ppn_bearer) {
            return null;
        }

        return self::ppnBearerOptions()[$this->ppn_bearer] ?? $this->ppn_bearer;
    }

    /**
     * @return array{subtotal: float, discount_amount: float, net_subtotal: float, ppn_enabled: bool, ppn_percent: float, ppn_amount: float, ppn_bearer: ?string, ppn_bearer_label: ?string, grand_total: float}
     */
    public function calculateOrderTotals(float $subtotal, float $discountAmount = 0): array
    {
        $discountAmount = max(0, $discountAmount);
        $netSubtotal = max(0, $subtotal - $discountAmount);

        if (!$this->ppn_enabled) {
            return [
                'subtotal'          => $subtotal,
                'discount_amount'   => $discountAmount,
                'net_subtotal'      => $netSubtotal,
                'ppn_enabled'       => false,
                'ppn_percent'       => 0.0,
                'ppn_amount'        => 0.0,
                'ppn_bearer'        => null,
                'ppn_bearer_label'  => null,
                'grand_total'       => (float) $netSubtotal,
            ];
        }

        $percent = (float) ($this->ppn_percent ?: Setting::get('pos_ppn_percent', 11));
        $bearer  = $this->ppn_bearer ?: Setting::get('pos_ppn_bearer', 'buyer');
        $ppnAmount = round($netSubtotal * $percent / 100);
        $grandTotal = $netSubtotal + ($bearer === 'buyer' ? $ppnAmount : 0);

        return [
            'subtotal'          => $subtotal,
            'discount_amount'   => $discountAmount,
            'net_subtotal'      => $netSubtotal,
            'ppn_enabled'       => true,
            'ppn_percent'       => $percent,
            'ppn_amount'        => (float) $ppnAmount,
            'ppn_bearer'        => $bearer,
            'ppn_bearer_label'  => self::ppnBearerOptions()[$bearer] ?? $bearer,
            'grand_total'       => (float) $grandTotal,
        ];
    }

    public function getTypeLabelAttribute(): string
    {
        return self::typeOptions()[$this->type] ?? $this->type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusOptions()[$this->status] ?? $this->status;
    }

    public function getPriceModeLabelAttribute(): string
    {
        return self::priceModeOptions()[$this->price_mode] ?? $this->price_mode;
    }

    /** Generate kode mitra MIT-0001 */
    public static function generateCode(): string
    {
        $lastId = (int) static::withTrashed()->max('id');
        return 'MIT-' . str_pad((string) ($lastId + 1), 4, '0', STR_PAD_LEFT);
    }

    /** Kode jenis mitra untuk nomor invoice PO (RS/KL/AP/UM/IN). */
    public function invoiceTypeCode(): string
    {
        return match ($this->type) {
            self::TYPE_RUMAH_SAKIT => 'RS',
            self::TYPE_KLINIK      => 'KL',
            self::TYPE_APOTEK      => 'AP',
            self::TYPE_UMKM        => 'UM',
            self::TYPE_INSTANSI    => 'IN',
            default                => 'MT',
        };
    }

    /**
     * Singkatan nama mitra untuk nomor invoice (inisial kata inti).
     * Contoh:
     * - "RSU Almansyur Medika" → AM
     * - "RSU Ratu Zuleha" → RZ
     * - "Klinik Sehat Banjarbaru" → SB
     * - "Apotek Almaira Farma" → AF
     */
    public function invoiceNameSlug(int $maxLen = 8): string
    {
        $name = mb_strtoupper(trim((string) $this->name), 'UTF-8');

        // Hapus kata jenis/badan hukum agar hanya nama inti yang diinisialkan
        $strip = [
            'RUMAH SAKIT', 'RSUD', 'RSU', 'RS',
            'KLINIK', 'APOTEK', 'UMKM', 'INSTANSI',
            'PT', 'CV', 'UD', 'YAYASAN', 'DR', 'DRS',
        ];

        foreach ($strip as $word) {
            $name = preg_replace('/\b' . preg_quote($word, '/') . '\b/u', ' ', $name) ?? $name;
        }

        $name = preg_replace('/[^A-Z0-9\s]+/u', ' ', $name) ?? $name;
        $words = preg_split('/\s+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        // Buang kata penghubung yang tidak bermakna untuk singkatan
        $stop = ['DAN', 'DENGAN', 'DI', 'KE', 'YANG', 'OF', 'THE', 'A', 'AN'];
        $words = array_values(array_filter($words, static fn ($w) => ! in_array($w, $stop, true)));

        if ($words === []) {
            return 'M' . (string) ($this->id ?: 'X');
        }

        if (count($words) === 1) {
            // Satu kata: ambil 2–3 huruf awal (mis. "SEHAT" → SEH)
            $slug = substr($words[0], 0, 3);
        } else {
            // Multi kata: inisial tiap kata (Almansyur Medika → AM)
            $slug = '';
            foreach ($words as $w) {
                $slug .= substr($w, 0, 1);
                if (strlen($slug) >= $maxLen) {
                    break;
                }
            }
        }

        $slug = preg_replace('/[^A-Z0-9]+/', '', $slug) ?: ('M' . (string) ($this->id ?: 'X'));

        return substr($slug, 0, max(2, $maxLen));
    }

    /** Segmen tengah nomor invoice: RS-AM */
    public function invoiceMidSegment(): string
    {
        return $this->invoiceTypeCode() . '-' . $this->invoiceNameSlug();
    }
}
