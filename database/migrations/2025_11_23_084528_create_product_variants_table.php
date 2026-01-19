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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')
                ->constrained('products')
                ->onDelete('CASCADE');
            $table->string('sku')->unique();
            $table->string('name')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('compare_price', 10, 2)->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->integer('quantity')->default(0);
            $table->integer('low_stock_threshold')->nullable();
            $table->boolean('is_default')->default(false);
            $table->unsignedBigInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('product_id');
            $table->index('is_default');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
