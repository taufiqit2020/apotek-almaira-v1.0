<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prescription extends Model {
    protected $fillable = ['doctor_name', 'doctor_sip', 'patient_name', 'prescription_date', 'status'];
    protected $casts = [
        'prescription_date' => 'date',
    ];

    public function items() {
        return $this->hasMany(PrescriptionItem::class);
    }

    public function sales() {
        return $this->hasMany(Sale::class);
    }
}
