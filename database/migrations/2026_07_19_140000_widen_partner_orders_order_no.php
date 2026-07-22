<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Format invoice: INV-0001/NMF/RS-ALMANSYUR/07/2026
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement('ALTER TABLE partner_orders MODIFY order_no VARCHAR(80) NOT NULL');
            return;
        }

        // SQLite: panjang VARCHAR tidak ditegakkan; biarkan kolom tetap ada.
        // PostgreSQL:
        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE partner_orders ALTER COLUMN order_no TYPE VARCHAR(80)');
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement('ALTER TABLE partner_orders MODIFY order_no VARCHAR(30) NOT NULL');
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE partner_orders ALTER COLUMN order_no TYPE VARCHAR(30)');
        }
    }
};
