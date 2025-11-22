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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')
                  ->unique()
                  ->constrained('orders')
                  ->restrictOnDelete();
            $table->string('stripe_payment_id', 255)->nullable();
            $table->string('stripe_payment_intent', 255)->nullable();
            $table->decimal('amount');
            $table->string('currency', 3)->default('USD');
            $table->enum('status', ['pending', 'succeeded', 'failed', 'cancelled'])
                  ->default('pending');
            $table->string('payment_method_type', 50)->nullable();
            $table->timestamps();
            $table->index('order_id', 'idx_order_id');
            $table->index('stripe_payment_id', 'idx_stripe_payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
