<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessagesModel extends Model
{
        protected $table = 'chat_messages';
        protected $fillable = ['chat_id', 'sender_id', 'receiver_id', 'message', 'attachments' , 'type'];
}
