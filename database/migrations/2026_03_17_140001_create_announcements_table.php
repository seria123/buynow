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
        Schema::create('announcements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('content');
            $table->enum('type', ['general', 'promotional', 'order', 'account', 'newsletter'])->default('general');
            $table->enum('target', ['all', 'customer_group', 'individual'])->default('all');
            $table->foreignId('customer_group_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignUuid('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignUuid('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'sent', 'cancelled'])->default('draft');
            $table->integer('recipients_count')->default(0);
            $table->integer('sent_count')->default(0);
            $table->integer('opened_count')->default(0);
            $table->timestamps();
            
            $table->index(['status', 'scheduled_at']);
            $table->index(['target', 'customer_group_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
