<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_inventory_source', function (Blueprint $table) {
            // Foreign IDs - products uses UUID, inventory_sources uses bigInteger
            $table->uuid('product_id');
            $table->unsignedBigInteger('inventory_source_id');

            // Quantities
            $table->integer('quantity')->default(0);
            $table->integer('reserved_quantity')->default(0);

            $table->timestamps();

            // Foreign key constraints
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();

            $table->foreign('inventory_source_id')
                ->references('id')
                ->on('inventory_sources')
                ->cascadeOnDelete();

            // Prevent duplicates
            $table->unique(['product_id', 'inventory_source_id']);
            
            // Primary key for pivot
            $table->primary(['product_id', 'inventory_source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_inventory_source');
    }
};
