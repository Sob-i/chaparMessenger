<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupOrChannelSettingModel extends Model
{
    protected $table = 'group_or_channel_setting';
    protected $fillable = [
        'chat_id'
        , 'type'
        , 'profile'
        , 'description'
        , 'users_can_send_message'
        , 'users_can_send_photos'
        , 'users_can_send_files'
        , 'users_can_add_members'
        , 'users_can_pin_messages'
        , 'users_can_change_settings'
        , 'slow_mode'
        , 'invite_links'
    ];
}
