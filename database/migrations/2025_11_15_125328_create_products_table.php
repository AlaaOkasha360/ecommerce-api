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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')
                  ->constrained('categories')
                  ->restrictOnDelete();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->decimal('price');
            $table->decimal('compare_price')->nullable();
            $table->decimal('cost_per_item')->nullable();
            $table->string('sku', 100)->unique();
            $table->string('barcode', 100)->nullable();
            $table->integer('quantity')->default(0);
            $table->decimal('weight')->nullable();
            $table->string('dimensions', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('category_id', 'idx_category_id');
            $table->index('sku', 'idx_sku');
            $table->index('is_active', 'idx_is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
