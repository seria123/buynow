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
        Schema::create('promotions', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID primary key
            $table->string('name');         // required name field
            $table->string('code')->unique(); // unique promo code
            $table->enum('type', ['percentage', 'fixed']); // promotion type
            $table->decimal('value', 10, 2); // amount or percentage
            $table->decimal('minimum_order_amount', 10, 2)->nullable(); // minimum order amount
            $table->unsignedInteger('usage_limit')->nullable(); // usage limit
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('starts_at')->nullable(); // start date
            $table->timestamp('expires_at')->nullable(); // end date
            $table->boolean('is_active')->default(true);
            $table->timestamps(); // created_at + updated_at
        });

    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
