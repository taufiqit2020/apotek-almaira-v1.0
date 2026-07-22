<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->boolean('ppn_enabled')->default(false)->after('credit_days');
            $table->decimal('ppn_percent', 5, 2)->nullable()->after('ppn_enabled');
            $table->string('ppn_bearer', 10)->nullable()->after('ppn_percent'); // buyer | seller
        });
    }

    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn(['ppn_enabled', 'ppn_percent', 'ppn_bearer']);
        });
    }
};
