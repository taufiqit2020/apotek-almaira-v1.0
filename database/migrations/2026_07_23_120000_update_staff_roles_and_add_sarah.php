<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('roles')->where('slug', 'admin_keuangan')->update([
            'name' => 'Kepala Keuangan dan Administrasi',
            'description' => 'Akses kepala keuangan dan administrasi',
            'updated_at' => now(),
        ]);

        DB::table('roles')->where('slug', 'kasir')->update([
            'name' => 'Staff Administrasi dan Kasir',
            'description' => 'Akses staff administrasi dan kasir',
            'updated_at' => now(),
        ]);

        $kasirRoleId = DB::table('roles')->where('slug', 'kasir')->value('id');
        if (! $kasirRoleId) {
            return;
        }

        if (! DB::table('users')->where('username', 'sarah')->exists()) {
            DB::table('users')->insert([
                'name' => 'SARAH',
                'username' => 'sarah',
                'email' => 'sarah@apotekalmaira.com',
                'password' => Hash::make('Almaira@2026'),
                'role_id' => $kasirRoleId,
                'is_active' => 1,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Pastikan Siti tetap pada role kepala keuangan
        DB::table('users')
            ->where('username', 'siti')
            ->update([
                'name' => 'Siti Kamariah, S.Pd.',
                'role_id' => DB::table('roles')->where('slug', 'admin_keuangan')->value('id'),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('roles')->where('slug', 'admin_keuangan')->update([
            'name' => 'Admin Keuangan',
            'description' => 'Akses admin keuangan',
            'updated_at' => now(),
        ]);

        DB::table('roles')->where('slug', 'kasir')->update([
            'name' => 'Kasir',
            'description' => 'Akses kasir',
            'updated_at' => now(),
        ]);

        DB::table('users')->where('username', 'sarah')->delete();
    }
};
