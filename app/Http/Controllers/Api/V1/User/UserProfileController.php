<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\EditUserProfileRequest;
use App\Services\Api\V1\User\UserServices;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    public function __construct(protected UserServices $userServices)
    {

    }
    public function getProfile()
    {
        $userId = auth()->id();

        $userProfile = $this->userServices->GetProfile($userId);

        if ($userProfile) {
            return response()->json([
                'success' => true,
                'data' => $userProfile,
            ],200);
        }
        return response()->json([
            'success' => false,
            'message' => 'User not found',
        ],406);
    }
    public function editProfile(EditUserProfileRequest $request)
    {
        $data = $request->validated();

        $data['user_id'] = auth()->id();

        $editedUser = $this->userServices->EditProfile($data);

        if($editedUser){
            return response()->json([
                'success' => true,
                'message' => 'Profile edited successfully',
            ],201);
        }
        return response()->json([
            'success' => false,
            'message' => 'Profile not edited',
        ],406);
    }
}
