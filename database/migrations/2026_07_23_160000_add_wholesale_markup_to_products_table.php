<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('wholesale_markup')->default(0)->after('het_markup');
        });

        // Produk lama: pakai nilai het_markup sebagai default markup grosir
        // agar perilaku otomatis tetap konsisten setelah dipisah.
        if (Schema::hasColumn('products', 'het_markup')) {
            DB::table('products')->update([
                'wholesale_markup' => DB::raw('het_markup'),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('wholesale_markup');
        });
    }
};
