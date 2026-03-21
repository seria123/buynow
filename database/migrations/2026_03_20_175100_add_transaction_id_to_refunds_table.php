<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->uuid('transaction_id')->nullable()->after('order_id')->index();
            $table->string('refunded_by')->nullable()->after('user_id');
            $table->timestamp('processed_at')->nullable()->after('mpesa_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->dropColumn(['transaction_id', 'refunded_by', 'processed_at']);
        });
    }
};
