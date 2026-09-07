<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChannelMembersModel extends Model
{
    protected $table = 'channel_members';
    protected $fillable = [
        'chat_id',
        'user_id',
        'type',
        'last_read_message',
    ];
}
