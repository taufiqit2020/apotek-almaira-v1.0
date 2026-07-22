<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SaleItem extends Model {
    protected $fillable = ['sale_id','product_id','product_name','product_code','unit_name','price_type','unit_price','quantity','discount_percent','discount_amount','subtotal'];
    protected $casts = ['unit_price'=>'decimal:2','discount_percent'=>'decimal:2','discount_amount'=>'decimal:2','subtotal'=>'decimal:2'];
    public function sale() { return $this->belongsTo(Sale::class); }
    public function product() { return $this->belongsTo(Product::class); }

    // Accessor & Mutator to bridge DB enum ('retail', 'wholesale') and display strings
    public function getPriceTypeAttribute($value) {
        return $value === 'retail' ? 'eceran' : ($value === 'wholesale' ? 'grosir' : $value);
    }
    public function setPriceTypeAttribute($value) {
        $this->attributes['price_type'] = $value === 'eceran' ? 'retail' : ($value === 'grosir' ? 'wholesale' : $value);
    }
}
