<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Role extends Model {
    protected $fillable = ['name', 'slug', 'description'];
    public function users() { return $this->hasMany(User::class); }
    const SUPER_ADMIN = 'super_admin';
    const ADMIN_KEUANGAN = 'admin_keuangan';
    const KASIR = 'kasir';
    const MITRA = 'mitra';
}
