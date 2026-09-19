<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\ChatMembersModel;


Broadcast::channel('chat.{chatId}', function ($user ,$chatId) {
    return ChatMembersModel::where('chat_id', $chatId)
        ->where('user_id', $user->id)
        ->exists();
});
