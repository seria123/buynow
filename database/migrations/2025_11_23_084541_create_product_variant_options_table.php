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
        Schema::create('product_variant_options', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUuid('product_variant_id')
                ->constrained('product_variants')
                ->onDelete('CASCADE');
            $table->foreignUlid('attribute_id')
                ->constrained('attributes')
                ->onDelete('CASCADE');
            $table->string('value');
            $table->timestamps();

            // Prevent duplicate attribute assignments to the same variant
            $table->unique(['product_variant_id', 'attribute_id']);
            $table->index('product_variant_id');
            $table->index('attribute_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variant_options');
    }
};
