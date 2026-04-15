<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->string('image');
            $table->string('mobile_image')->nullable();
            $table->string('link')->nullable();
            $table->string('position')->default('home'); // home, category, product, cart, banner
            $table->enum('type', ['promotion', 'banner', 'featured', 'flash_sale', 'category'])->default('banner');
            $table->boolean('active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->integer('clicks')->default(0);
            $table->integer('impressions')->default(0);
            $table->foreignUuid('product_id')
                ->nullable()
                ->constrained('products')
                ->onDelete('set null');
            $table->foreignUlid('category_id')
                ->nullable()
                ->constrained('categories')
                ->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};
