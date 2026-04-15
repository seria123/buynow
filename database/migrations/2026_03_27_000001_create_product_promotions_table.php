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
        Schema::create('product_promotions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignUuid('promotion_id')->constrained('promotions')->cascadeOnDelete();
            $table->decimal('discounted_price', 10, 2)->nullable();
            $table->integer('stock_limit')->nullable()->comment('Limited stock for flash sales');
            $table->integer('sold_count')->default(0)->comment('Number of items sold under this promotion');
            $table->timestamps();

            // Unique constraint to prevent duplicate entries
            $table->unique(['product_id', 'promotion_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_promotions');
    }
};
