<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes;

    protected $fillable = ['name', 'username', 'email', 'password', 'role_id', 'avatar', 'is_active', 'last_login'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function partner()
    {
        return $this->hasOne(Partner::class);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function hasRole(string $slug): bool
    {
        return $this->role?->slug === $slug;
    }

    public function allows(string $permission): bool
    {
        return (bool) $this->role?->allows($permission);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(Role::SUPER_ADMIN) || (bool) $this->role?->isFullAccess();
    }

    public function isKepalaIt(): bool
    {
        return $this->hasRole(Role::SUPER_ADMIN);
    }

    public function isStaffIt(): bool
    {
        return $this->hasRole(Role::STAFF_IT);
    }

    public function isKepalaOperasional(): bool
    {
        return $this->hasRole(Role::KEPALA_OPERASIONAL);
    }

    public function isStaffOperasional(): bool
    {
        return $this->hasRole(Role::STAFF_OPERASIONAL);
    }

    public function isAdminKeuangan(): bool
    {
        return $this->hasRole(Role::ADMIN_KEUANGAN);
    }

    public function isStaffKeuangan(): bool
    {
        return $this->isAdminKeuangan();
    }

    public function isKasir(): bool
    {
        return $this->hasRole(Role::KASIR)
            || ($this->canAccessPos() && ! $this->canAccessInventory() && ! $this->canAccessFinance() && ! $this->canManageUsers());
    }

    public function isMitra(): bool
    {
        return $this->hasRole(Role::MITRA);
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function canAccessAdmin(): bool
    {
        return $this->isSuperAdmin();
    }

    public function canManageUsers(): bool
    {
        return $this->allows('users');
    }

    public function canManageBackup(): bool
    {
        return $this->allows('backup');
    }

    public function canViewActivityLog(): bool
    {
        return $this->allows('activity_log');
    }

    public function canAccessPos(): bool
    {
        return $this->allows('pos');
    }

    public function canAccessInventory(): bool
    {
        return $this->allows('inventory');
    }

    public function canAccessPurchases(): bool
    {
        return $this->allows('purchases');
    }

    public function canAccessMasterData(): bool
    {
        return $this->allows('master_data');
    }

    public function canAccessFinance(): bool
    {
        return $this->allows('finance');
    }

    public function canAccessInvoices(): bool
    {
        return $this->allows('invoices') || $this->allows('finance');
    }

    public function canAccessReports(): bool
    {
        return $this->allows('reports') || $this->allows('activity_log');
    }

    public function canAccessSettings(): bool
    {
        return $this->allows('settings');
    }

    public function isStaff(): bool
    {
        return $this->role && ! $this->isMitra();
    }

    public function avatarPath(): ?string
    {
        if ($this->avatar && is_file(public_path($this->avatar))) {
            return $this->avatar;
        }

        $employeePhoto = $this->employee?->photo;
        if ($employeePhoto && is_file(public_path($employeePhoto))) {
            return $employeePhoto;
        }

        return null;
    }

    public function avatarUrl(): ?string
    {
        $path = $this->avatarPath();

        return $path ? asset($path) : null;
    }

    public function initials(): string
    {
        $name = trim((string) $this->name);
        if ($name === '') {
            return '?';
        }

        $parts = preg_split('/\s+/', $name) ?: [];
        $initials = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $initials .= mb_strtoupper(mb_substr($part, 0, 1));
        }

        return $initials !== '' ? $initials : mb_strtoupper(mb_substr($name, 0, 1));
    }
}
