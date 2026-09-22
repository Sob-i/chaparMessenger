<?php

namespace App\Services\Api\V1\User;


use App\Models\BlockedUsersModel;
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
    public function BlockUser(array $data)
    {
        foreach ($data['blocked_id'] as $key => $blocked_id) {
            $ids[] = BlockedUsersModel::create([
                'user_id' => $data['user_id'],
                'blocked_id' => $blocked_id,
            ]);
        }
        return $ids;
    }
    public function GetBlockedUser($userId)
    {
        return BlockedUsersModel::where('user_id', $userId)->get();
    }
    public function UnBlockUser(array $data)
    {
        $deleted = 0;

        foreach ($data['blocked_id'] as $blocked_id) {
            $deleted += BlockedUsersModel::where('user_id', $data['user_id'])
                ->where('blocked_id', $blocked_id)
                ->delete();
        }

        return $deleted > 0;
    }
}
