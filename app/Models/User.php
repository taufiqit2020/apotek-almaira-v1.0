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
    public function employee() { return $this->hasOne(Employee::class); }
    public function hasRole(string $slug): bool { return $this->role?->slug === $slug; }

    public function isSuperAdmin(): bool { return $this->hasRole(Role::SUPER_ADMIN); }
    public function isKepalaIt(): bool { return $this->isSuperAdmin(); }
    public function isStaffIt(): bool { return $this->hasRole(Role::STAFF_IT); }
    public function isKepalaOperasional(): bool { return $this->hasRole(Role::KEPALA_OPERASIONAL); }
    public function isStaffOperasional(): bool { return $this->hasRole(Role::STAFF_OPERASIONAL); }
    public function isAdminKeuangan(): bool { return $this->hasRole(Role::ADMIN_KEUANGAN); }
    public function isStaffKeuangan(): bool { return $this->isAdminKeuangan(); }
    public function isKasir(): bool { return $this->hasRole(Role::KASIR); }
    public function isMitra(): bool { return $this->hasRole(Role::MITRA); }
    public function isActive(): bool { return $this->is_active; }

    /** Akses penuh sistem (Kepala IT). */
    public function canAccessAdmin(): bool { return $this->isKepalaIt(); }

    /** Manajemen user & sistem IT. */
    public function canManageUsers(): bool { return $this->isKepalaIt() || $this->isStaffIt(); }
    public function canManageBackup(): bool { return $this->isKepalaIt() || $this->isStaffIt(); }
    public function canViewActivityLog(): bool { return $this->isKepalaIt() || $this->isStaffIt(); }

    /** Kasir & penjualan (belanja). */
    public function canAccessPos(): bool
    {
        return $this->isKepalaIt()
            || $this->isKepalaOperasional()
            || $this->isStaffOperasional()
            || $this->isKasir()
            || $this->isStaffKeuangan();
    }

    /** Inventori/stok (bukan khusus kasir). */
    public function canAccessInventory(): bool
    {
        return $this->isKepalaIt()
            || $this->isKepalaOperasional()
            || $this->isStaffOperasional()
            || $this->isStaffKeuangan()
            || $this->isStaffIt();
    }

    /** Pengadaan / barang masuk. */
    public function canAccessPurchases(): bool
    {
        return $this->isKepalaIt()
            || $this->isKepalaOperasional()
            || $this->isStaffKeuangan();
    }

    /** Master data & mitra B2B. */
    public function canAccessMasterData(): bool
    {
        return $this->isKepalaIt()
            || $this->isKepalaOperasional()
            || $this->isStaffKeuangan();
    }

    /** Keuangan (kredit, gaji, laporan). */
    public function canAccessFinance(): bool
    {
        return $this->isKepalaIt() || $this->isStaffKeuangan();
    }

    /** Pengaturan aplikasi. */
    public function canAccessSettings(): bool
    {
        return $this->isKepalaIt() || $this->isStaffKeuangan() || $this->isKepalaOperasional();
    }

    /** Staff apotek (bukan portal mitra). */
    public function isStaff(): bool
    {
        return $this->isKepalaIt()
            || $this->isStaffIt()
            || $this->isKepalaOperasional()
            || $this->isStaffOperasional()
            || $this->isStaffKeuangan()
            || $this->isKasir();
    }

    /** Path relatif foto profil di public/, atau null. */
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
