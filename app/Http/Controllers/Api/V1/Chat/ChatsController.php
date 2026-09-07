<?php

namespace App\Http\Controllers\Api\V1\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CreateChatRequest;
use App\Services\Api\V1\chat\ChatServices;

class ChatsController extends Controller
{
    public function __construct(protected ChatServices $chatServices)
    {

    }

    public function createChat(CreateChatRequest $request , $memberId = null)
    {
        $data = $request->validated();

        if (($data['type'] == 'group' || $data['type'] == 'channel') && empty($data['name'])) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a name for group or channel',
            ], 422);
        }

        $createdChat = $this->chatServices->CreateChat($data);

        if ($createdChat) {
            if ($createdChat->type == 'channel') {
                $members = $this->chatServices->CreateChnnelAdmins($createdChat->id,$memberId);
            }else{
                $members = $this->chatServices->CreateChatMembers($createdChat->id,$memberId);
            }

            return response()->json([
                'success' => true,
                'message' => 'chat created successfully',
                'data' => [
                    'id' => $createdChat->id,
                    'type' => $createdChat->type,
                    'name' => $createdChat->name,
                ],
                'members' => $members ?? 'no members yet'
            ],201);
        }
        return response()->json([
            'success' => false,
            'message' => 'could not create chat',
        ],500);
    }
    public function getChats()
    {
        $user = auth()->user();

        $chats = $this->chatServices->Chats($user->id);

        if ($chats) {
            return response()->json([
                'success' => true,
                'chats' => $chats
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'could not find any chats',
        ]);
    }
}
