<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('products', function (Blueprint $table) {
            $table->index('name');
        });
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->index('patient_name');
            $table->index('doctor_name');
        });
    }

    public function down(): void {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropIndex(['patient_name']);
            $table->dropIndex(['doctor_name']);
        });
    }
};
