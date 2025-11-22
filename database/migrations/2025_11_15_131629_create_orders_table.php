<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->restrictOnDelete();
            $table->foreignId('shipping_address_id')
                  ->constrained('addresses');
            $table->foreignId('billing_address_id')
                  ->constrained('addresses');
            $table->string('order_number', 50)->unique();
            $table->enum('status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])
                  ->default('pending');
            $table->decimal('subtotal');
            $table->decimal('tax')->default(0);
            $table->decimal('shipping_cost')->default(0);
            $table->decimal('total_amount');
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])
                  ->default('pending');
            $table->string('payment_method', 50)->nullable();
            $table->text('notes')->nullable();
            $table->index('user_id', 'idx_user_id');
            $table->index('order_number', 'idx_order_number');
            $table->index('status', 'idx_status');
            $table->index('payment_status', 'idx_payment_status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
