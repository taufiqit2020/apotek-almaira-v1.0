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
        return $this->price_type === 'grosir' ? 'Grosir' : 'Eceran';
    }
}
