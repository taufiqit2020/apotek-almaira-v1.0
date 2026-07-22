<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PurchaseItem extends Model {
    protected $fillable = ['purchase_id','product_id','product_name','quantity','purchase_price','sell_price','subtotal','expired_date'];
    protected $casts = ['purchase_price'=>'decimal:2','sell_price'=>'decimal:2','subtotal'=>'decimal:2','expired_date'=>'date'];
    public function purchase() { return $this->belongsTo(Purchase::class); }
    public function product() { return $this->belongsTo(Product::class); }
}
