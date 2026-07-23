<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Role extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'permissions', 'is_system', 'is_active'];

    protected $casts = [
        'permissions' => 'array',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    /** Kepala IT — akses penuh sistem */
    const SUPER_ADMIN = 'super_admin';
    const KEPALA_IT = 'super_admin';
    const STAFF_IT = 'staff_it';
    const KEPALA_OPERASIONAL = 'kepala_operasional';
    const STAFF_OPERASIONAL = 'staff_operasional';
    const ADMIN_KEUANGAN = 'admin_keuangan';
    const STAFF_KEUANGAN = 'admin_keuangan';
    const KASIR = 'kasir';
    const MITRA = 'mitra';

    /** Daftar hak akses yang bisa dikelola di master role */
    public const PERMISSION_LABELS = [
        'pos' => 'Kasir / POS & penjualan',
        'inventory' => 'Inventori & stok',
        'purchases' => 'Pengadaan / barang masuk',
        'master_data' => 'Master data, mitra & karyawan',
        'finance' => 'Keuangan (kredit, gaji)',
        'invoices' => 'Tagihan invoice',
        'reports' => 'Laporan',
        'settings' => 'Pengaturan aplikasi',
        'users' => 'Manajemen user',
        'backup' => 'Database backup',
        'activity_log' => 'Log aktivitas',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeStaffAssignable($query)
    {
        return $query->active()->where('slug', '!=', self::MITRA);
    }

    public function isFullAccess(): bool
    {
        if ($this->slug === self::SUPER_ADMIN) {
            return true;
        }

        $perms = $this->permissions ?? [];

        return in_array('*', $perms, true);
    }

    public function allows(string $permission): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->isFullAccess()) {
            return true;
        }

        return in_array($permission, $this->permissions ?? [], true);
    }

    public function allowsAny(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->allows($permission)) {
                return true;
            }
        }

        return false;
    }

    public function permissionLabels(): array
    {
        if ($this->isFullAccess()) {
            return ['Akses penuh semua modul'];
        }

        $labels = [];
        foreach ($this->permissions ?? [] as $key) {
            if (isset(self::PERMISSION_LABELS[$key])) {
                $labels[] = self::PERMISSION_LABELS[$key];
            }
        }

        return $labels;
    }

    public static function defaultPermissionsForSlug(string $slug): array
    {
        return match ($slug) {
            self::SUPER_ADMIN => ['*'],
            self::STAFF_IT => ['inventory', 'users', 'backup', 'activity_log'],
            self::KEPALA_OPERASIONAL => ['pos', 'inventory', 'purchases', 'master_data', 'settings', 'invoices'],
            self::STAFF_OPERASIONAL => ['pos', 'inventory'],
            self::ADMIN_KEUANGAN => ['pos', 'inventory', 'purchases', 'master_data', 'finance', 'settings', 'invoices', 'reports'],
            self::KASIR => ['pos'],
            self::MITRA => [],
            default => [],
        };
    }

    public static function makeSlug(string $name): string
    {
        $base = Str::slug($name, '_');
        if ($base === '') {
            $base = 'role';
        }

        $slug = $base;
        $i = 2;
        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'_'.$i;
            $i++;
        }

        return $slug;
    }
}
