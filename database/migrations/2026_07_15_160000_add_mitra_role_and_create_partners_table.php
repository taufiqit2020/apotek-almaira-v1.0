<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Langkah 1 — Mitra E-Catalog:
 * - Role login `mitra`
 * - Tabel `partners` (RS, Klinik, Apotek, UMKM, Instansi)
 */
return new class extends Migration
{
    public function up(): void
    {
        // Role mitra (id=4) — aman untuk DB yang sudah ada
        DB::table('roles')->insertOrIgnore([
            [
                'id'          => 4,
                'name'        => 'Mitra Katalog',
                'slug'        => 'mitra',
                'description' => 'Akses portal mitra untuk order/PO dari e-catalog',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);

        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->nullable()->unique();
            $table->string('name', 200);
            $table->string('type', 30); // rumah_sakit|klinik|apotek|umkm|instansi

            $table->string('npwp', 40)->nullable();
            $table->string('nib', 50)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();

            $table->string('pic_name', 150)->nullable();
            $table->string('phone', 30);
            $table->string('email', 150)->nullable();

            // Akun login (role mitra) — diisi saat admin buat / setelah approve self-register
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // pending | approved | rejected | inactive
            $table->string('status', 20)->default('pending');

            // eceran | grosir | auto
            $table->string('price_mode', 20)->default('eceran');

            $table->boolean('allow_transfer')->default(true);
            $table->boolean('allow_cod')->default(true);
            $table->boolean('invoice_enabled')->default(false);
            $table->unsignedSmallInteger('credit_days')->default(30);

            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();

            // admin | self
            $table->string('registration_source', 20)->default('admin');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'status']);
            $table->index('phone');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partners');
        DB::table('roles')->where('slug', 'mitra')->delete();
    }
};
