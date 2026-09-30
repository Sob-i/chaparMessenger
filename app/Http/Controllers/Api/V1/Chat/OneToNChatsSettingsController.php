<?php

namespace App\Http\Controllers\Api\V1\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\OneToNSettingAdminRequest;
use App\Models\GroupOrChannelSettingModel;
use App\Services\Api\V1\chat\ChatSettingServices;
use Illuminate\Http\Request;

class OneToNChatsSettingsController extends Controller
{
    public function __construct(protected ChatSettingServices $chatSettingServices)
    {

    }

    public function getSettings(Request $request , $chatId)
    {
        $user = $request->user();

        $data = [
            'ownerOrAdmin' => $user->id,
            'chat_id' => $chatId
        ];

        if ($this->chatSettingServices->IsOwnerOrAdmin($data))
        {
           $settings = $this->chatSettingServices->GetChatSettings($chatId);
           return response()->json([
               'success' => true,
               'settings' => $settings
           ],200);
        }
        return response()->json([
            'success' => false,
            'message' => 'You are not authorized to access this resource'
        ],401);
    }
    public function chatAdmin(OneToNSettingAdminRequest $request , $chatId)
    {
        $owner = $request->user();

        $data = [
            'ownerOrAdmin' => $owner->id,
            'chat_id' => $chatId ,
            'user_id' => $request->user_id ,
            'type' => $request->type
        ];


            if ($this->chatSettingServices->IsOwnerOrAdmin($data))
            {
                $user = $this->chatSettingServices->ChatAdminStatus($data);

                if ($user)
                {
                    return response()->json([
                        'success' => true,
                        'message' => $data['type'] == 'admin' ? 'promoted successfully' : 'demoted successfully'
                    ],200);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to access this resource'
            ],401);
    }
}
