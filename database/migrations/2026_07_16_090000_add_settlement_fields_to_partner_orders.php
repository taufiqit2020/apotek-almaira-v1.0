<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partner_orders', function (Blueprint $table) {
            $table->enum('settlement_method', ['cash', 'transfer'])->nullable()->after('payment_status');
            $table->timestamp('settled_at')->nullable()->after('due_date');
            $table->foreignId('settled_by')->nullable()->after('settled_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('partner_orders', function (Blueprint $table) {
            $table->dropForeign(['settled_by']);
            $table->dropColumn(['settlement_method', 'settled_at', 'settled_by']);
        });
    }
};
