<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->decimal('overtime', 15, 2)->default(0)->after('basic_salary');
            $table->decimal('bpjs_kesehatan', 15, 2)->default(0)->after('allowance');
            $table->decimal('bpjs_ketenagakerjaan', 15, 2)->default(0)->after('bpjs_kesehatan');
        });
    }

    public function down(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->dropColumn(['overtime', 'bpjs_kesehatan', 'bpjs_ketenagakerjaan']);
        });
    }
};
