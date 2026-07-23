<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Product extends Model {
    use SoftDeletes;

    /** Path relatif di public/ untuk gambar default produk (logo Apotek Almaira). */
    public const DEFAULT_IMAGE = 'assets/images/default-product.png';

    protected $fillable = [
        'barcode','code','name','category_id','unit_id','supplier_id',
        'description','composition','manufacturer','drug_class','dosage_form','route',
        'requires_prescription',
        'purchase_price','sell_price','wholesale_price','het_price',
        'stock','stock_min','expired_date','is_active','het_markup','images',
        'show_in_catalog','catalog_order',
    ];
    protected $casts = [
        'requires_prescription' => 'boolean',
        'is_active' => 'boolean',
        'show_in_catalog' => 'boolean',
        'catalog_order' => 'integer',
        'purchase_price' => 'decimal:2',
        'sell_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'het_price' => 'decimal:2',
        'expired_date' => 'date',
        'het_markup' => 'integer',
        'images' => 'array',
    ];
    protected $appends = ['image_url', 'has_image'];

    public function category() { return $this->belongsTo(Category::class); }
    public function unit() { return $this->belongsTo(Unit::class); }
    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function saleItems() { return $this->hasMany(SaleItem::class); }
    public function purchaseItems() { return $this->hasMany(PurchaseItem::class); }
    public function stockOuts() { return $this->hasMany(StockOut::class); }
    public function scopeActive($q) { return $q->where('is_active', true); }
    public function scopeLowStock($q) { return $q->whereColumn('stock', '<=', 'stock_min'); }
    public function scopeInCatalog($q) { return $q->where('show_in_catalog', true); }

    /**
     * Harga grosir otomatis dari jual + HET markup (%).
     * Acuan bisnis: markup 30% pada jual 3.500 → turun 860 → grosir 2.640.
     * Proporsional untuk 5–30%.
     */
    public static function calcWholesaleFromMarkup(float $sell, int $markup): float
    {
        $sell = max(0, round($sell));
        $markup = max(0, $markup);
        if ($sell <= 0 || $markup <= 0) {
            return 0.0;
        }

        $drop = (int) round($sell * $markup * 860 / (30 * 3500));
        $wholesale = $sell - $drop;

        return (float) max(0, min($wholesale, $sell - 1));
    }

    /**
     * Jika harga jual melebihi HET: tutup jual ke HET.
     * Grosir tidak boleh melebihi jual setelah normalisasi.
     * (Tidak lagi menurunkan jual ke grosir dulu — grosir bisa jauh di bawah HET.)
     *
     * @return array{sell_price: float, wholesale_price: float, adjusted: bool}
     */
    public static function normalizeSellAgainstHet(float $sell, float $wholesale, float $het): array
    {
        $adjusted = false;

        if ($het > 0 && $sell > $het) {
            $sell = $het;
            $adjusted = true;
        }

        if ($sell > 0 && $wholesale > $sell) {
            $wholesale = $sell;
            $adjusted = true;
        }

        return [
            'sell_price' => round($sell, 2),
            'wholesale_price' => round(max(0, $wholesale), 2),
            'adjusted' => $adjusted,
        ];
    }

    /** True jika harga jual > HET (HET terisi). */
    public function exceedsHet(): bool
    {
        $het = (float) ($this->het_price ?? 0);

        return $het > 0 && (float) $this->sell_price > $het;
    }

    /** Scope: produk dengan harga jual melebihi HET. */
    public function scopeExceedsHet($query)
    {
        return $query->where('het_price', '>', 0)
            ->whereColumn('sell_price', '>', 'het_price');
    }

    /** Terapkan normalisasi HET pada atribut instance (belum di-save). */
    public function applyHetSellNormalization(): bool
    {
        $result = self::normalizeSellAgainstHet(
            (float) $this->sell_price,
            (float) ($this->wholesale_price ?? 0),
            (float) ($this->het_price ?? 0),
        );

        if (! $result['adjusted']) {
            return false;
        }

        $this->sell_price = $result['sell_price'];
        $this->wholesale_price = $result['wholesale_price'];

        return true;
    }

    /**
     * Pencarian produk konsisten (termasuk Indikasi / Fungsi = description).
     *
     * - catalog: nama, kandungan, indikasi/fungsi, nama kategori
     * - ops: nama, kode, barcode, kandungan, indikasi/fungsi (POS, master, picker)
     */
    public function scopeSearchKeyword($query, ?string $keyword, string $mode = 'ops')
    {
        $keyword = trim((string) $keyword);
        if ($keyword === '') {
            return $query;
        }

        $like = '%' . $keyword . '%';

        return $query->where(function ($q) use ($like, $mode) {
            $q->where('name', 'like', $like)
                ->orWhere('description', 'like', $like)
                ->orWhere('composition', 'like', $like);

            if ($mode === 'ops') {
                $q->orWhere('code', 'like', $like)
                    ->orWhere('barcode', 'like', $like);
            }

            if ($mode === 'catalog') {
                $q->orWhereHas('category', fn ($c) => $c->where('name', 'like', $like));
            }
        });
    }
    public function isLowStock(): bool { return $this->stock <= $this->stock_min; }
    public function isExpired(): bool { return $this->expired_date && $this->expired_date->isPast(); }
    public function isExpiringSoon(int $days = 30): bool { return $this->expired_date && $this->expired_date->isBefore(now()->addDays($days)); }
    public function getFormattedSellPriceAttribute(): string { return 'Rp ' . number_format($this->sell_price, 0, ',', '.'); }
    public function getFormattedWholesalePriceAttribute(): string { return 'Rp ' . number_format($this->wholesale_price, 0, ',', '.'); }

    /** Path gambar utama (foto produk atau logo default). */
    public function primaryImagePath(): string
    {
        if (is_array($this->images)) {
            foreach ($this->images as $img) {
                if (is_string($img) && $img !== '') {
                    return $img;
                }
            }
        }

        return self::DEFAULT_IMAGE;
    }

    public function getHasImageAttribute(): bool
    {
        return $this->primaryImagePath() !== self::DEFAULT_IMAGE;
    }

    public function getImageUrlAttribute(): string
    {
        return asset($this->primaryImagePath());
    }

    /**
     * Meta singkat untuk kartu katalog / detail.
     *
     * @return array{kandungan: ?string, indikasi: ?string, bentuk_sediaan: ?string, pabrik: ?string}
     */
    public function catalogMeta(): array
    {
        $bentuk = trim((string) ($this->dosage_form ?? '')) ?: null;
        $kandungan = trim((string) ($this->composition ?? '')) ?: null;

        // Fallback data lama yang masih menyimpan bentuk di composition.
        if (! $bentuk && $kandungan) {
            if (str_contains($kandungan, '/')) {
                $parts = array_values(array_filter(array_map('trim', explode('/', $kandungan))));
                if (isset($parts[0]) && $this->looksLikeDosageForm($parts[0])) {
                    $bentuk = $parts[0];
                    $kandungan = (isset($parts[1]) && ! $this->looksLikeDosageForm($parts[1]))
                        ? $parts[1]
                        : null;
                }
            } elseif ($this->looksLikeDosageForm($kandungan)) {
                $bentuk = $kandungan;
                $kandungan = null;
            }
        }

        $indikasi = trim((string) ($this->description ?? ''));
        $indikasi = preg_replace('/\s*\(Golongan:\s*[^)]+\)\s*$/iu', '', $indikasi);
        $indikasi = trim((string) $indikasi) ?: null;

        $pabrik = trim((string) ($this->manufacturer ?? '')) ?: null;
        $golongan = trim((string) ($this->drug_class ?? '')) ?: null;
        $rute = trim((string) ($this->route ?? '')) ?: null;

        return [
            'kandungan' => $kandungan,
            'indikasi' => $indikasi,
            'bentuk_sediaan' => $bentuk,
            'pabrik' => $pabrik,
            'golongan' => $golongan,
            'rute' => $rute,
        ];
    }

    /**
     * Badge stok informatif: hijau / kuning / merah + jumlah.
     *
     * @return array{state: string, emoji: string, dot: string, label: string, short: string, chip: string, badge: string}
     */
    public function stockBadge(): array
    {
        $qty = (int) $this->stock;
        $min = max(0, (int) $this->stock_min);

        if ($qty <= 0) {
            return [
                'state' => 'habis',
                'emoji' => '🔴',
                'dot' => 'bg-red-500',
                'label' => 'Habis',
                'short' => 'Habis',
                'chip' => 'bg-red-50 text-red-700 border-red-100',
                'badge' => 'bg-red-500 text-white',
            ];
        }

        if ($min > 0 && $qty <= $min) {
            return [
                'state' => 'terbatas',
                'emoji' => '🟡',
                'dot' => 'bg-amber-400',
                'label' => 'Tinggal '.$qty,
                'short' => 'Tinggal '.$qty,
                'chip' => 'bg-amber-50 text-amber-800 border-amber-100',
                'badge' => 'bg-amber-500 text-white',
            ];
        }

        return [
            'state' => 'tersedia',
            'emoji' => '🟢',
            'dot' => 'bg-emerald-500',
            'label' => 'Stok banyak ('.$qty.')',
            'short' => 'Stok '.$qty,
            'chip' => 'bg-emerald-50 text-emerald-800 border-emerald-100',
            'badge' => 'bg-emerald-500 text-white',
        ];
    }

    /**
     * Badge tanggal kadaluarsa untuk katalog / master.
     *
     * @return array{has_date: bool, state: string, date: ?string, label: string, note: ?string, chip: string, text: string, icon: string}
     */
    public function expiryBadge(): array
    {
        if (! $this->expired_date) {
            return [
                'has_date' => false,
                'state' => 'unknown',
                'date' => null,
                'label' => 'Belum diisi',
                'note' => null,
                'chip' => 'bg-slate-50 text-slate-500 border-slate-100',
                'text' => 'text-slate-500',
                'icon' => 'text-slate-400',
            ];
        }

        $date = $this->expired_date->timezone(config('app.timezone'))->startOfDay();
        $daysLeft = (int) now()->startOfDay()->diffInDays($date, false);
        $formatted = $this->expired_date->format('d/m/Y');

        if ($daysLeft < 0) {
            $ago = abs($daysLeft);

            return [
                'has_date' => true,
                'state' => 'expired',
                'date' => $formatted,
                'label' => $formatted,
                'note' => 'Sudah kadaluarsa ('.$ago.' hari)',
                'chip' => 'bg-red-50 text-red-700 border-red-100',
                'text' => 'text-red-700',
                'icon' => 'text-red-500',
            ];
        }

        if ($daysLeft <= 30) {
            return [
                'has_date' => true,
                'state' => 'critical',
                'date' => $formatted,
                'label' => $formatted,
                'note' => $daysLeft === 0 ? 'Kadaluarsa hari ini' : 'Tersisa '.$daysLeft.' hari',
                'chip' => 'bg-red-50 text-red-700 border-red-100',
                'text' => 'text-red-700',
                'icon' => 'text-red-500',
            ];
        }

        if ($daysLeft <= 90) {
            return [
                'has_date' => true,
                'state' => 'warning',
                'date' => $formatted,
                'label' => $formatted,
                'note' => 'Tersisa '.$daysLeft.' hari',
                'chip' => 'bg-amber-50 text-amber-800 border-amber-100',
                'text' => 'text-amber-700',
                'icon' => 'text-amber-500',
            ];
        }

        return [
            'has_date' => true,
            'state' => 'safe',
            'date' => $formatted,
            'label' => $formatted,
            'note' => 'Masih aman',
            'chip' => 'bg-emerald-50 text-emerald-800 border-emerald-100',
            'text' => 'text-emerald-700',
            'icon' => 'text-emerald-500',
        ];
    }

    private function looksLikeDosageForm(string $value): bool
    {
        $v = strtolower(trim($value));
        $forms = [
            'tablet', 'tab', 'kapsul', 'kaps', 'capsule', 'injeksi', 'inj', 'ampul', 'amp',
            'syrup', 'sirup', 'botol', 'btl', 'tube', 'krim', 'krem', 'salep', 'gel',
            'tetes', 'drop', 'inhaler', 'nebu', 'nebulizer', 'larutan', 'suspensi',
            'serbuk', 'powder', 'sachet', 'pcs', 'box', 'strip', 'vial', 'oftalmic',
            'tetes mata', 'topikal', 'kapsul lunak', 'pasang ampul',
        ];

        foreach ($forms as $form) {
            if ($v === $form || str_starts_with($v, $form.' ') || str_contains($v, $form)) {
                return true;
            }
        }

        return false;
    }
}
