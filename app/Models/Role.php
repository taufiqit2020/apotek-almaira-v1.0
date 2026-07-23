<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Role extends Model {
    protected $fillable = ['name', 'slug', 'description'];
    public function users() { return $this->hasMany(User::class); }

    /** Kepala IT — akses penuh sistem */
    const SUPER_ADMIN = 'super_admin';
    const KEPALA_IT = 'super_admin';

    /** Staff IT — user, log, backup (bukan operasional/keuangan penuh) */
    const STAFF_IT = 'staff_it';

    /** Kepala Operasional — operasional apotek (bukan sistem IT) */
    const KEPALA_OPERASIONAL = 'kepala_operasional';

    /** Staff Operasional — operasional harian terbatas */
    const STAFF_OPERASIONAL = 'staff_operasional';

    /** Staff Keuangan */
    const ADMIN_KEUANGAN = 'admin_keuangan';
    const STAFF_KEUANGAN = 'admin_keuangan';

    /** Khusus Kasir — hanya belanja/POS */
    const KASIR = 'kasir';

    const MITRA = 'mitra';
}
