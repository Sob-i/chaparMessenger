<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedUsersModel extends Model
{
    protected $table = 'blocked_users';
    protected $fillable = ['blocked_id', 'user_id'];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    public function BlockedUsers()
    {
        return $this->hasMany(User::class, 'blocked_id', 'id');
    }
}
