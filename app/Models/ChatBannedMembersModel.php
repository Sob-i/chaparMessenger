<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatBannedMembersModel extends Model
{
    protected $table = 'chat_banned_members';

    protected $fillable = [
        'chat_id',
        'user_id',
    ];
}
