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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')
                  ->constrained('shopping_carts')
                  ->onDelete('cascade');
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->decimal('price_at_addition');
            $table->timestamps();
            $table->unique(['cart_id', 'product_id'], 'unique_cart_product');
            $table->index('cart_id', 'idx_cart_id');
            $table->index('product_id', 'idx_product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
