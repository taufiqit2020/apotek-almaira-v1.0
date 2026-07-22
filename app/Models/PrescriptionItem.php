<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrescriptionItem extends Model {
    protected $fillable = ['prescription_id', 'product_id', 'product_name', 'dosage', 'signa', 'quantity'];

    public function prescription() {
        return $this->belongsTo(Prescription::class);
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }
}
