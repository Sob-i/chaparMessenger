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
        Schema::create('group_or_channel_setting', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chat_id');
            $table->enum('type', ['group', 'channel'])->default('group');
            $table->string('profile')->nullable();
            $table->text('description')->nullable();
            $table->enum('users_can_send_message', ['yes', 'no'])->default('yes');
            $table->enum('users_can_send_photos', ['yes', 'no'])->default('yes');
            $table->enum('users_can_send_files', ['yes', 'no'])->default('yes');
            $table->enum('users_can_add_members', ['yes', 'no'])->default('no');
            $table->enum('users_can_pin_messages', ['yes', 'no'])->default('no');
            $table->enum('users_can_change_settings', ['yes', 'no'])->default('no');
            $table->enum('slow_mode', ['off', '5s' , '10s' , '30s' , '1m' , '5m' , '30m'])->default('off');
            $table->string('invite_links')->nullable();
            $table->foreign('chat_id')->references('id')->on('chats')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_or_channel_setting');
    }
};
