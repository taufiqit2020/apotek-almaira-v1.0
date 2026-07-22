<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->string('entity', 20)->default('pt')->after('user_id');
            // Unique di aplikasi (soft delete); index untuk filter cepat.
            $table->index(['user_id', 'entity', 'period_year', 'period_month'], 'salaries_user_entity_period_idx');
        });
    }

    public function down(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->dropIndex('salaries_user_entity_period_idx');
            $table->dropColumn('entity');
        });
    }
};
