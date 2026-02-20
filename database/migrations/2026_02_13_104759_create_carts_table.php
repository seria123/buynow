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
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained('users')->onDelete('CASCADE'); // Connect cart to a user
            $table->foreignUlid('product_id')->constrained('products')->onDelete('CASCADE'); // Product being added
            $table->string('product_name'); // Store product name at the time of adding
            $table->string('category_name')->nullable(); // Optional: product category
            $table->decimal('price', 10, 2); // Store price at the time of adding
            $table->integer('quantity')->default(1); // How many
            $table->timestamps();
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
