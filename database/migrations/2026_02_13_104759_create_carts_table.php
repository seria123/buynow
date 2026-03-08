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
        
    
        Schema::create('carts', function (Blueprint $table) {
            // Use UUID for cart ID
            $table->uuid('id')->primary();

             

            // UUID references for user and product
            $table->uuid('user_id')->nullable();
            $table->uuid('product_id');

            // Cart item details
            $table->string('product_name');
            $table->string('category_name')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2);
              $table->string('status')->default('pending');

            $table->timestamps();

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            // Optional: ensure uniqueness of same product per user
            $table->unique(['user_id', 'product_id']);
        });
       }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
