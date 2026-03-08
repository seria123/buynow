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
         Schema::create('order_items', function (Blueprint $table) {

    // Primary UUID
    $table->uuid('id')->primary();

    // Foreign UUID references
    $table->uuid('order_id');
    $table->uuid('product_id');

    // Item details
    $table->string('product_name');
    $table->integer('quantity')->default(1);
    $table->decimal('price', 10, 2);
    $table->decimal('subtotal', 10, 2);

    $table->timestamps();

    // Foreign keys
    $table->foreign('order_id')
          ->references('id')
          ->on('orders')
          ->onDelete('cascade');

    $table->foreign('product_id')
          ->references('id')
          ->on('products')
          ->onDelete('cascade');

    // Prevent duplicate product in same order
    $table->unique(['order_id', 'product_id']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};