<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── Transaksi Penjualan (Header) ─────────────────────────
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no', 30)->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // kasir
            $table->string('customer_name', 150)->nullable();
            $table->enum('payment_method', ['cash', 'qris'])->default('cash');

            // Subtotal sebelum diskon & pajak
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);

            // PPN
            $table->boolean('ppn_active')->default(false);
            $table->decimal('ppn_percent', 5, 2)->default(11);
            $table->decimal('ppn_amount', 15, 2)->default(0);
            $table->enum('ppn_bearer', ['buyer', 'seller'])->default('buyer'); // ditanggung siapa?

            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('cash_received', 15, 2)->default(0); // uang diterima (cash)
            $table->decimal('change_amount', 15, 2)->default(0); // kembalian

            $table->text('notes')->nullable();
            $table->enum('status', ['completed', 'cancelled', 'refunded'])->default('completed');
            $table->timestamp('sold_at')->useCurrent();
            $table->timestamps();

            $table->index(['sold_at', 'status']);
            $table->index('invoice_no');
        });

        // ─── Detail Item Penjualan ────────────────────────────────
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name', 200);     // snapshot nama saat transaksi
            $table->string('product_code', 50)->nullable();
            $table->string('unit_name', 50)->nullable();
            $table->enum('price_type', ['retail', 'wholesale'])->default('retail'); // eceran/grosir
            $table->decimal('unit_price', 15, 2);    // harga satuan saat transaksi
            $table->integer('quantity');
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });

        // ─── Barang Masuk / Pembelian dari Supplier ──────────────
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no', 50)->nullable();  // no faktur supplier
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('purchase_date');
            $table->date('expired_date')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ─── Detail Barang Masuk ──────────────────────────────────
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name', 200);
            $table->integer('quantity');
            $table->decimal('purchase_price', 15, 2);
            $table->decimal('sell_price', 15, 2)->default(0);  // update harga jual saat masuk barang
            $table->decimal('subtotal', 15, 2);
            $table->date('expired_date')->nullable();
            $table->timestamps();
        });

        // ─── Barang Keluar (Non-penjualan: rusak, kadaluarsa, dll)
        Schema::create('stock_outs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('product_name', 200);
            $table->integer('quantity');
            $table->enum('reason', ['expired', 'damaged', 'returned', 'other'])->default('other');
            $table->text('notes')->nullable();
            $table->timestamp('out_date')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_outs');
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};
