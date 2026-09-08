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
        Schema::create('chat_messages_read', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('message_id')->unique();
            $table->unsignedBigInteger('user_id')->unique();
            $table->foreign('message_id')->references('id')->on('chat_messages');
            $table->foreign('user_id')->references('id')->on('users');
            $table->timestamp('read_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages_read');
    }
};
