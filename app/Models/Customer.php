<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model {
    protected $fillable = ['name', 'phone', 'address', 'dob', 'nik', 'points', 'is_active'];
    protected $casts = [
        'dob'       => 'date',
        'points'    => 'integer',
        'is_active' => 'boolean'
    ];

    public function sales() {
        return $this->hasMany(Sale::class);
    }

    public function scopeActive($q) {
        return $q->where('is_active', true);
    }

    // Check if customer has any overdue (past due_date, unpaid) invoice
    public function hasOverdueInvoice(): bool {
        return $this->sales()
            ->where('payment_method', 'invoice')
            ->where('payment_status', 'unpaid')
            ->where('due_date', '<', now()->startOfDay())
            ->where('status', 'completed')
            ->exists();
    }

    // Count of all unpaid invoices (including overdue)
    public function unpaidInvoiceCount(): int {
        return $this->sales()
            ->where('payment_method', 'invoice')
            ->where('payment_status', 'unpaid')
            ->where('status', 'completed')
            ->count();
    }

    // Total amount of all unpaid invoices
    public function totalUnpaidAmount(): float {
        return (float) $this->sales()
            ->where('payment_method', 'invoice')
            ->where('payment_status', 'unpaid')
            ->where('status', 'completed')
            ->sum('total');
    }
}
