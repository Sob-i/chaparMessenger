<?php

namespace App\Services\Api\V1\User;


use App\Models\UserProfileModel;

class UserServices
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {

    }
    public function GetProfile($userId)
        {
            return UserProfileModel::where('user_id', $userId)->first();
        }

    public function EditProfile(?array $data)
       {
            return UserProfileModel::where('user_id' , $data['user_id'])->update([
                'user_name' => isset($data['user_name']) ? '@'.$data['user_name'] : null,
                'avatar' =>  isset($data['avatar']) ? $data['avatar'] : null,
                'bio' =>  isset($data['bio']) ? $data['bio'] : null,
                'phone' =>  isset($data['phone']) ? $data['phone'] : null,
            ]);
       }
}
