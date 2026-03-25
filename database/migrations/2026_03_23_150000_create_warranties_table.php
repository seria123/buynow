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
        Schema::create('warranties', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('product_id')->constrained()->onDelete('cascade');
            $table->foreignUlid('user_id')->constrained()->onDelete('cascade');
            $table->string('warranty_number')->unique();
            $table->enum('warranty_type', ['standard', 'extended', 'lifetime', 'manufacturer'])->default('standard');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'expired', 'claimed', 'cancelled'])->default('active');
            $table->text('terms')->nullable();
            $table->text('claim_instructions')->nullable();
            $table->string('document_path')->nullable();
            $table->json('coverage_details')->nullable();
            $table->foreignUlid('order_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignUlid('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['status', 'end_date']);
            $table->index(['user_id', 'status']);
            $table->index('warranty_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warranties');
    }
};