<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // ── Roles: rename + add ──
        DB::table('roles')->where('slug', 'super_admin')->update([
            'name' => 'Kepala IT',
            'description' => 'Akses penuh sistem (IT & seluruh modul)',
            'updated_at' => $now,
        ]);

        DB::table('roles')->where('slug', 'admin_keuangan')->update([
            'name' => 'Staff Keuangan',
            'description' => 'Akses keuangan, laporan, inventori, dan master data terkait',
            'updated_at' => $now,
        ]);

        DB::table('roles')->where('slug', 'kasir')->update([
            'name' => 'Khusus Kasir',
            'description' => 'Hanya akses kasir/POS, resep, pelanggan, dan riwayat penjualan sendiri',
            'updated_at' => $now,
        ]);

        DB::table('roles')->where('slug', 'staff_operasional')->update([
            'name' => 'Staff Operasional',
            'description' => 'Operasional harian: POS, inventori, stok (tanpa pengadaan & keuangan)',
            'updated_at' => $now,
        ]);

        $this->upsertRole('staff_it', 'Staff IT', 'Manajemen user, backup, log, dan dukungan inventori (bukan keuangan/operasional penuh)');
        $this->upsertRole('kepala_operasional', 'Kepala Operasional', 'Operasional apotek: POS, inventori, pengadaan, mitra, karyawan (bukan sistem IT & keuangan murni)');

        $roleIds = DB::table('roles')->pluck('id', 'slug');
        $password = Hash::make('Almaira@2026');

        // Kepala IT
        $this->upsertUser([
            'name' => 'TAUFIQURRAHMAN, S.Kom',
            'username' => 'taufiqit',
            'email' => 'taufiqit2020@gmail.com',
            'role_id' => $roleIds['super_admin'],
            'password' => $password,
        ]);

        // Staff IT
        $this->upsertUser([
            'name' => 'RAHMAN, S.Kom',
            'username' => 'rahman',
            'email' => 'rahman@apotekalmaira.com',
            'role_id' => $roleIds['staff_it'],
            'password' => $password,
        ]);

        // Kepala Operasional (akun lama taufiq digeser dari super_admin)
        if (isset($roleIds['kepala_operasional'])) {
            DB::table('users')->where('username', 'taufiq')->update([
                'name' => 'TAUFIQURRAHMAN, S.Kom',
                'role_id' => $roleIds['kepala_operasional'],
                'updated_at' => $now,
            ]);
        }

        // Staff Operasional
        DB::table('users')->where('username', 'taufiqop')->update([
            'name' => 'TAUFIQ',
            'role_id' => $roleIds['staff_operasional'] ?? null,
            'updated_at' => $now,
        ]);

        // Staff Keuangan
        $this->upsertUser([
            'name' => 'Staff Keuangan',
            'username' => 'keuangan1',
            'email' => 'keuangan1@apotekalmaira.com',
            'role_id' => $roleIds['admin_keuangan'],
            'password' => $password,
        ]);

        // Pastikan Siti tetap Staff Keuangan (jika ada)
        if (DB::table('users')->where('username', 'siti')->exists()) {
            DB::table('users')->where('username', 'siti')->update([
                'role_id' => $roleIds['admin_keuangan'],
                'updated_at' => $now,
            ]);
        }

        // Khusus Kasir 1–5
        $kasirRoleId = $roleIds['kasir'] ?? null;
        if ($kasirRoleId) {
            for ($i = 1; $i <= 5; $i++) {
                $this->upsertUser([
                    'name' => 'Kasir Almaira '.$i,
                    'username' => 'kasiralmaira'.$i,
                    'email' => 'kasiralmaira'.$i.'@apotekalmaira.com',
                    'role_id' => $kasirRoleId,
                    'password' => $password,
                ]);
            }

            // Kasir lama tetap role kasir
            DB::table('users')
                ->whereIn('username', ['alya', 'sarah'])
                ->update(['role_id' => $kasirRoleId, 'updated_at' => $now]);
        }
    }

    public function down(): void
    {
        DB::table('users')->whereIn('username', [
            'taufiqit', 'rahman', 'keuangan1',
            'kasiralmaira1', 'kasiralmaira2', 'kasiralmaira3', 'kasiralmaira4', 'kasiralmaira5',
        ])->delete();

        DB::table('roles')->whereIn('slug', ['staff_it', 'kepala_operasional'])->delete();

        DB::table('roles')->where('slug', 'super_admin')->update([
            'name' => 'Kepala Operasional',
            'description' => 'Akses penuh kepala operasional',
            'updated_at' => now(),
        ]);
    }

    private function upsertRole(string $slug, string $name, string $description): void
    {
        $existing = DB::table('roles')->where('slug', $slug)->first();
        if ($existing) {
            DB::table('roles')->where('id', $existing->id)->update([
                'name' => $name,
                'description' => $description,
                'updated_at' => now(),
            ]);

            return;
        }

        DB::table('roles')->insert([
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function upsertUser(array $data): void
    {
        $existing = DB::table('users')->where('username', $data['username'])->first();
        if ($existing) {
            DB::table('users')->where('id', $existing->id)->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'role_id' => $data['role_id'],
                'is_active' => 1,
                'updated_at' => now(),
            ]);

            return;
        }

        // Email unik — jika bentrok, sesuaikan
        if (DB::table('users')->where('email', $data['email'])->exists()) {
            $data['email'] = $data['username'].'+role@apotekalmaira.com';
        }

        DB::table('users')->insert([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role_id' => $data['role_id'],
            'is_active' => 1,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
