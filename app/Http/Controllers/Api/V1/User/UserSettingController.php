<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\BlockUserRequest;
use App\Http\Requests\Api\V1\UnBlockUserRequest;
use App\Services\Api\V1\User\UserServices;
use Illuminate\Http\Request;

class UserSettingController extends Controller
{
    public function __construct(protected UserServices $userServices)
    {

    }
    public function getBlockedUsers(Request $request)
    {
        $user = $request->user();
        $blockedUsers = $this->userServices->GetBlockedUser($user->id);
        return response()->json([
            'success' => true,
            'blockedUsers' => $blockedUsers
        ]);
    }

    public function blockUser(BlockUserRequest $request)
    {
        $data = [
            'user_id' => $request->user()->id,
            'blocked_id' =>$request->validated(['blocked_id'])
        ];

        $blockedUser = $this->userServices->BlockUser($data);

        if ($blockedUser) {
            return response()->json([
                'success' => true,
                'message' => 'User blocked',
            ],201);
        }
        return response()->json([
            'success' => false,
            'message' => 'something went wrong',
        ],406);
    }
    public function unblockUser(UnblockUserRequest $request)
    {
        $data = [
            'user_id' => $request->user()->id,
            'blocked_id' =>array_values($request->validated(['blocked_id']))
        ];

        $UnblockedUser = $this->userServices->UnBlockUser($data);

        if ($UnblockedUser) {
            return response()->json([
                'success' => true,
                'message' => 'User Unblocked',
            ],200);
        }
        return response()->json([
            'success' => false,
            'message' => 'something went wrong',
        ],406);
    }
}
