<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Purchase extends Model {
    protected $fillable = ['reference_no','supplier_id','user_id','purchase_date','expired_date','total_amount','notes','status'];
    protected $casts = ['purchase_date'=>'date','expired_date'=>'date','total_amount'=>'decimal:2'];
    public function isDraft() { return $this->status === 'draft'; }
    public function isSent() { return $this->status === 'sent'; }
    public function isReceived() { return $this->status === 'received'; }
    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function items() { return $this->hasMany(PurchaseItem::class); }
}
