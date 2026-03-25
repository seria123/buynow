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
        Schema::create('transactions', function (Blueprint $table) {
            // Primary UUID
            $table->uuid('id')->primary();

            // Reference to order
            $table->uuid('order_id');
            $table->foreign('order_id')
                  ->references('id')
                  ->on('orders')
                  ->onDelete('cascade');

            // Reference to user (customer)
            $table->uuid('user_id')->nullable();
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');

            // Transaction details
            $table->string('transaction_number')->unique();
            $table->decimal('amount', 10, 2);
            $table->string('currency')->default('USD');
            $table->string('type')->default('payment'); // payment, refund
            $table->string('status')->default('pending'); // pending, processing, completed, failed, cancelled, refunded, expired
            $table->string('payment_method')->nullable(); // credit_card, paypal, stripe, paystack, etc.
            
            // Payment gateway details
            $table->string('gateway')->nullable(); // stripe, paystack, flutterwave, etc.
            $table->string('gateway_transaction_id')->nullable(); // Transaction ID from payment provider
            $table->string('gateway_response_code')->nullable();
            $table->string('gateway_response_message')->nullable();
            $table->json('gateway_response_data')->nullable(); // Store full response from gateway
            
            // Customer payment info (for reference)
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            
            // Metadata
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            
            // Timestamps
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('order_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('transaction_number');
            $table->index('gateway_transaction_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
