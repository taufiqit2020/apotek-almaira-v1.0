<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partner_orders', function (Blueprint $table) {
            $table->string('settlement_proof', 255)->nullable()->after('settlement_method');
            $table->timestamp('settlement_proof_at')->nullable()->after('settlement_proof');
        });
    }

    public function down(): void
    {
        Schema::table('partner_orders', function (Blueprint $table) {
            $table->dropColumn(['settlement_proof', 'settlement_proof_at']);
        });
    }
};
