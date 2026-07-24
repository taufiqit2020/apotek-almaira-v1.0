<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerOrderItem extends Model
{
    protected $fillable = [
        'partner_order_id', 'product_id',
        'product_name', 'product_code', 'unit_name',
        'price_type', 'unit_price', 'quantity', 'subtotal',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal'   => 'decimal:2',
        'quantity'   => 'integer',
    ];

    public function order()
    {
        return $this->belongsTo(PartnerOrder::class, 'partner_order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getPriceTypeLabelAttribute(): string
    {
        return match ($this->price_type) {
            'invoice' => 'Invoice',
            'grosir'  => 'Grosir',
            default   => 'Eceran',
        };
    }

    /**
     * Meta produk untuk daftar item PO & cetakan (kode, kategori, satuan, kandungan, bentuk).
     *
     * @return array{code: string, category: string, unit: string, kandungan: string, bentuk: string}
     */
    public function catalogDisplay(): array
    {
        $product = $this->product;
        $meta = $product?->catalogMeta() ?? [];

        $code = trim((string) ($this->product_code ?: $product?->code ?: ''));
        $category = trim((string) ($product?->category?->name ?? ''));
        $unit = trim((string) ($this->unit_name ?: $product?->unit?->name ?: ''));
        $kandungan = trim((string) ($meta['kandungan'] ?? ''));
        $bentuk = trim((string) ($meta['bentuk_sediaan'] ?? ''));

        return [
            'code' => $code !== '' ? $code : '—',
            'category' => $category !== '' ? $category : '—',
            'unit' => $unit !== '' ? $unit : '—',
            'kandungan' => $kandungan !== '' ? $kandungan : '—',
            'bentuk' => $bentuk !== '' ? $bentuk : '—',
        ];
    }
}
