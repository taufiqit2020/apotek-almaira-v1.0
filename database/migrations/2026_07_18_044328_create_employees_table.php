<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 150);
            $table->string('position', 100)->nullable();
            $table->string('entity_scope', 20)->default('both'); // pt | apotek | both
            $table->string('phone', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('address')->nullable();
            $table->date('join_date')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender', 20)->nullable(); // laki-laki | perempuan
            $table->string('nik', 30)->nullable();
            $table->string('bank_name', 80)->nullable();
            $table->string('bank_account', 50)->nullable();
            $table->string('bank_holder', 150)->nullable();
            $table->string('photo')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'name']);
            $table->index('entity_scope');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
