<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
class User extends Authenticatable {
    use Notifiable, SoftDeletes;
    protected $fillable = ['name', 'username', 'email', 'password', 'role_id', 'avatar', 'is_active', 'last_login'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];
    public function role() { return $this->belongsTo(Role::class); }
    public function activityLogs() { return $this->hasMany(ActivityLog::class); }
    public function partner() { return $this->hasOne(Partner::class); }
    public function hasRole(string $slug): bool { return $this->role?->slug === $slug; }
    public function isSuperAdmin(): bool { return $this->hasRole(Role::SUPER_ADMIN); }
    public function isAdminKeuangan(): bool { return $this->hasRole(Role::ADMIN_KEUANGAN); }
    public function isKasir(): bool
    {
        // Staff operasional memakai akses lantai yang sama dengan kasir.
        return $this->hasRole(Role::KASIR) || $this->hasRole(Role::STAFF_OPERASIONAL);
    }
    public function isStaffOperasional(): bool { return $this->hasRole(Role::STAFF_OPERASIONAL); }
    public function isMitra(): bool { return $this->hasRole(Role::MITRA); }
    public function isActive(): bool { return $this->is_active; }
    public function canAccessAdmin(): bool { return $this->isSuperAdmin() || $this->isAdminKeuangan(); }
    /** Staff apotek (bukan portal mitra). */
    public function isStaff(): bool {
        return $this->isSuperAdmin() || $this->isAdminKeuangan() || $this->isKasir();
    }
}
