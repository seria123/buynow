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
        Schema::create('attribute_values', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('attribute_id')
                ->constrained('attributes')
                ->onDelete('CASCADE');
            $table->string('value');
            $table->string('slug');
            $table->foreignUuid('creator_id')
                ->references('id')
                ->on('users')
                ->onDelete('CASCADE')
                ->nullable();
            $table->unsignedBigInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(0);
            $table->timestamps();

            // Unique constraint: slug must be unique within an attribute
            $table->unique(['slug', 'attribute_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attribute_values');
    }
};
