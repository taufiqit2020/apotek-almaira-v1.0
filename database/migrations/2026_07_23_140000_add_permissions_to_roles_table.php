<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->json('permissions')->nullable()->after('description');
            $table->boolean('is_system')->default(false)->after('permissions');
            $table->boolean('is_active')->default(true)->after('is_system');
        });

        $defaults = [
            'super_admin' => ['*'],
            'staff_it' => ['inventory', 'users', 'backup', 'activity_log'],
            'kepala_operasional' => ['pos', 'inventory', 'purchases', 'master_data', 'settings', 'invoices'],
            'staff_operasional' => ['pos', 'inventory'],
            'admin_keuangan' => ['pos', 'inventory', 'purchases', 'master_data', 'finance', 'settings', 'invoices', 'reports'],
            'kasir' => ['pos'],
            'mitra' => [],
        ];

        foreach ($defaults as $slug => $perms) {
            DB::table('roles')->where('slug', $slug)->update([
                'permissions' => json_encode(array_values($perms)),
                'is_system' => true,
                'is_active' => true,
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['permissions', 'is_system', 'is_active']);
        });
    }
};
