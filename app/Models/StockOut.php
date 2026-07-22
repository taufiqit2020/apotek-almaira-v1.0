<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class StockOut extends Model {
    protected $fillable = ['product_id','user_id','product_name','quantity','reason','notes','out_date'];
    protected $casts = ['out_date'=>'datetime'];
    public function product() { return $this->belongsTo(Product::class); }
    public function user() { return $this->belongsTo(User::class); }
}
