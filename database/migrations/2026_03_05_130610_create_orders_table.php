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
        Schema::create('orders', function (Blueprint $table) {

    // Primary UUID
    $table->uuid('id')->primary();

    // Reference to user
    $table->uuid('user_id');

$table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();


    // Order details
    
    $table->decimal('total_amount', 10, 2);
    $table->string('status')->default('pending'); 
    $table->string('payment_status')->default('unpaid');
    $table->string('order_number')->nullable();
    $table->string('payment_method')->nullable();
     $table->boolean('archived')->default(false);

    $table->timestamps();

    // Foreign key
    $table->foreign('user_id')
          ->references('id')
          ->on('users')
          ->onDelete('cascade');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};