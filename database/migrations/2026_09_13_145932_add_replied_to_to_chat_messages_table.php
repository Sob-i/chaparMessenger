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
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->unsignedBigInteger('reply_to_message')->nullable()->after('type');
            $table->unsignedBigInteger('reply_to_user')->nullable()->after('type');
            $table->foreign('reply_to_message')->references('id')->on('chat_messages');
            $table->foreign('reply_to_user')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropColumn('reply_to_message', 'reply_to_user');
        });
    }
};
