<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMembersModel extends Model
{
    protected $table = 'chat_members';

    protected $fillable = ['chat_id' , 'user_id' , 'type' , 'last_read_message'];
}
