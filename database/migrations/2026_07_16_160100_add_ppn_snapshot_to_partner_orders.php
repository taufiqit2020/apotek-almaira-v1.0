<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partner_orders', function (Blueprint $table) {
            $table->decimal('discount_amount', 15, 2)->default(0)->after('subtotal');
            $table->boolean('ppn_enabled')->nullable()->after('discount_amount');
            $table->decimal('ppn_percent', 5, 2)->nullable()->after('ppn_enabled');
            $table->decimal('ppn_amount', 15, 2)->default(0)->after('ppn_percent');
            $table->string('ppn_bearer', 10)->nullable()->after('ppn_amount');
        });
    }

    public function down(): void
    {
        Schema::table('partner_orders', function (Blueprint $table) {
            $table->dropColumn(['discount_amount', 'ppn_enabled', 'ppn_percent', 'ppn_amount', 'ppn_bearer']);
        });
    }
};
