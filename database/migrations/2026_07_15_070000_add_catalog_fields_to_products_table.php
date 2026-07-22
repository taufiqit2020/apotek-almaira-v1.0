<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('show_in_catalog')->default(true)->after('is_active');
            $table->unsignedInteger('catalog_order')->default(0)->after('show_in_catalog');
            $table->index('show_in_catalog');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['show_in_catalog']);
            $table->dropColumn(['show_in_catalog', 'catalog_order']);
        });
    }
};
