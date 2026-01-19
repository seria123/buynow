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
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique();
            $table->foreignUlid('brand_id')
                ->nullable()
                ->constrained('brands')
                ->onDelete('SET NULL');
            $table->foreignUlid('category_id')
                ->nullable()
                ->constrained('categories')
                ->onDelete('SET NULL');
            $table->foreignUlid('attribute_family_id')
                ->nullable()
                ->constrained('attribute_families')
                ->onDelete('SET NULL');
            $table->text('short_description')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('compare_price', 10, 2)->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->integer('quantity')->default(0);
            $table->integer('low_stock_threshold')->nullable();
            $table->string('status')->default('draft'); // draft, pending, approved, rejected
            $table->boolean('published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->foreignUuid('creator_id')
                ->references('id')
                ->on('users')
                ->onDelete('CASCADE')
                ->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('published');
            $table->index('is_featured');
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
