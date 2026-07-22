<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('settlement_method', 20)->nullable()->after('payment_status'); // cash/transfer saat pelunasan
            $table->timestamp('settled_at')->nullable()->after('settlement_method');
            $table->unsignedBigInteger('settled_by')->nullable()->after('settled_at');
            $table->foreign('settled_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['settled_by']);
            $table->dropColumn(['settlement_method', 'settled_at', 'settled_by']);
        });
    }
};
