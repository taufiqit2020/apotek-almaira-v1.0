<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no', 30)->unique();
            $table->foreignId('partner_id')->constrained('partners')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['submitted', 'confirmed', 'fulfilled', 'cancelled'])->default('submitted');
            $table->enum('payment_method', ['transfer', 'cod', 'invoice']);
            $table->enum('payment_status', ['unpaid', 'awaiting_confirmation', 'paid', 'cancelled'])->default('unpaid');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->string('price_mode_snapshot', 20)->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('pic_name', 150)->nullable();
            $table->string('pic_phone', 30)->nullable();
            $table->text('notes')->nullable();
            $table->string('transfer_proof', 255)->nullable();
            $table->timestamp('transfer_proof_at')->nullable();
            $table->date('due_date')->nullable();
            $table->text('admin_notes')->nullable();
            $table->string('cancel_reason', 255)->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['partner_id', 'status']);
            $table->index(['status', 'payment_status']);
            $table->index('created_at');
        });

        Schema::create('partner_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_order_id')->constrained('partner_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name', 200);
            $table->string('product_code', 50)->nullable();
            $table->string('unit_name', 50)->nullable();
            $table->enum('price_type', ['eceran', 'grosir'])->default('eceran');
            $table->decimal('unit_price', 15, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_order_items');
        Schema::dropIfExists('partner_orders');
    }
};
