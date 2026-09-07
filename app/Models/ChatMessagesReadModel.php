<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessagesReadModel extends Model
{
    protected $table = 'chat_messages_read';
    protected $fillable = ['message_id' , 'user_id' , 'read_at'];
}
