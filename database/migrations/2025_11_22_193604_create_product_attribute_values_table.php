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
        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUuid('product_id')
                ->constrained('products')
                ->onDelete('CASCADE');
            $table->foreignUlid('attribute_id')
                ->constrained('attributes')
                ->onDelete('CASCADE');
            $table->text('value');
            $table->timestamps();

            // Unique constraint: one value per attribute per product
            $table->unique(['product_id', 'attribute_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_attribute_values');
    }
};
