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
        Schema::create('attributes', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('slug');
            $table->foreignUlid('attribute_family_id')
                ->constrained('attribute_families')
                ->onDelete('CASCADE');
            $table->string('type')->default('predefined'); // 'predefined' or 'manual'
            $table->text('description')->nullable();
            $table->foreignUuid('creator_id')
                ->references('id')
                ->on('users')
                ->onDelete('CASCADE')
                ->nullable();
            $table->unsignedBigInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(0);
            $table->timestamps();

            // Unique constraint: slug must be unique within an attribute family
            $table->unique(['slug', 'attribute_family_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attributes');
    }
};
