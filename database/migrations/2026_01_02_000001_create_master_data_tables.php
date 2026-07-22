<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── Kategori Produk ──────────────────────────────────────
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ─── Supplier ─────────────────────────────────────────────
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('contact_person', 100)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ─── Unit Satuan ──────────────────────────────────────────
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique(); // pcs, box, strip, tablet, ml, dll
            $table->string('symbol', 20)->nullable();
            $table->timestamps();
        });

        // ─── Produk / Obat ────────────────────────────────────────
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('barcode', 50)->unique()->nullable();
            $table->string('code', 50)->unique()->nullable();
            $table->string('name', 200);
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->text('description')->nullable();
            $table->string('composition')->nullable();      // kandungan / komposisi
            $table->string('manufacturer')->nullable();     // pabrik/merk
            $table->boolean('requires_prescription')->default(false); // butuh resep dokter?

            // Harga
            $table->decimal('purchase_price', 15, 2)->default(0);   // harga beli
            $table->decimal('sell_price', 15, 2)->default(0);        // harga jual (eceran)
            $table->decimal('wholesale_price', 15, 2)->default(0);   // harga grosir
            $table->decimal('het_price', 15, 2)->default(0);         // HET (Harga Eceran Tertinggi)

            // Stok
            $table->integer('stock')->default(0);
            $table->integer('stock_min')->default(10);     // stok minimum (warning)
            $table->date('expired_date')->nullable();

            // Status
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Index untuk pencarian cepat
            $table->index(['name', 'is_active']);
            $table->index('barcode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('units');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('categories');
    }
};
