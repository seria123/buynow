<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * users.id is a UUID, but sessions.user_id was created as a bigint. With
     * SESSION_DRIVER=database MySQL rejects the UUID on session save, so the
     * login is never persisted and the user is bounced back to /login.
     */
    public function up(): void
    {
        DB::table('sessions')->delete();

        Schema::table('sessions', function (Blueprint $table) {
            $table->char('user_id', 36)->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('sessions')->delete();

        Schema::table('sessions', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
    }
};
