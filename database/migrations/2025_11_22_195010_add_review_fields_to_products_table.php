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
        Schema::table('products', function (Blueprint $table) {
            $table->foreignUuid('reviewer_id')
                ->nullable()
                ->after('creator_id')
                ->references('id')
                ->on('users')
                ->onDelete('SET NULL');

            $table->timestamp('reviewed_at')
                ->nullable()
                ->after('reviewer_id');

            $table->text('review_notes')
                ->nullable()
                ->after('reviewed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['reviewer_id']);
            $table->dropColumn(['reviewer_id', 'reviewed_at', 'review_notes']);
        });
    }
};
