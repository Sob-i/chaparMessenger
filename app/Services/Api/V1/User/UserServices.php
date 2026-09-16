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
            $user = auth()->user()->load('userInfo');

            return UserProfileModel::where('user_id' , $data['user_id'])->update([
                'user_name' => isset($data['user_name']) ? '@'.$data['user_name'] : $user->userInfo->user_name,
                'avatar' => $data['avatar'] ?? $user->userInfo->avatar,
                'bio' => $data['bio'] ?? $user->userInfo->bio,
                'phone' => $data['phone'] ?? $user->userInfo->phone,
            ]);
       }
}
