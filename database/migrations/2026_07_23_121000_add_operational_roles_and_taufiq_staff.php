<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('roles')->where('slug', 'super_admin')->update([
            'name' => 'Kepala Operasional',
            'description' => 'Akses penuh kepala operasional',
            'updated_at' => now(),
        ]);

        $opsRoleId = DB::table('roles')->where('slug', 'staff_operasional')->value('id');
        if (! $opsRoleId) {
            $opsRoleId = DB::table('roles')->insertGetId([
                'name' => 'Staff Operasional',
                'slug' => 'staff_operasional',
                'description' => 'Akses staff operasional apotek',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('roles')->where('id', $opsRoleId)->update([
                'name' => 'Staff Operasional',
                'description' => 'Akses staff operasional apotek',
                'updated_at' => now(),
            ]);
        }

        DB::table('users')
            ->where('username', 'taufiq')
            ->update([
                'name' => 'Taufiqurrahman, S.Kom.',
                'role_id' => DB::table('roles')->where('slug', 'super_admin')->value('id'),
                'updated_at' => now(),
            ]);

        if (! DB::table('users')->where('username', 'taufiqop')->exists()) {
            DB::table('users')->insert([
                'name' => 'TAUFIQ',
                'username' => 'taufiqop',
                'email' => 'taufiqop@apotekalmaira.com',
                'password' => Hash::make('Almaira@2026'),
                'role_id' => $opsRoleId,
                'is_active' => 1,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('users')->where('username', 'taufiqop')->delete();

        DB::table('roles')->where('slug', 'super_admin')->update([
            'name' => 'Super Admin / IT',
            'description' => 'Akses penuh',
            'updated_at' => now(),
        ]);

        DB::table('roles')->where('slug', 'staff_operasional')->delete();
    }
};
