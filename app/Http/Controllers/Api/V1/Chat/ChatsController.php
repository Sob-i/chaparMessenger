<?php

namespace App\Http\Controllers\Api\V1\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CreateChatRequest;
use App\Http\Requests\Api\V1\DeleteMessageRequest;
use App\Http\Requests\Api\V1\EditMessageRequest;
use App\Http\Requests\Api\V1\SendMessageRequest;
use App\Services\Api\V1\chat\ChatServices;
use Illuminate\Http\Request;

class ChatsController extends Controller
{
    public function __construct(protected ChatServices $chatServices)
    {

    }
    public function createChat(CreateChatRequest $request , $memberId)
    {
        $data = $request->validated();

        if (($data['type'] == 'group' || $data['type'] == 'channel') && empty($data['name'])) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a name for group or channel',
            ], 422);
        }

        $Ids = ['user_id' =>$request->user()->id ,'members' => json_decode($memberId,true)];

        $createdChat = $this->chatServices->CreateChat($data,$Ids);

        if ($createdChat === null) {
            return response()->json([
                'success' => false,
                'message' => 'Chat already exists',
            ], 409);
        }

        if ($createdChat) {
            $type = 'private';
            if ($createdChat->type == 'channel'|| $createdChat->type == 'group') {
                $members = $this->chatServices->CreateChatMembers($createdChat->id,$Ids,'nonePrivate');
            }else{
                $members = $this->chatServices->CreateChatMembers($createdChat->id,$Ids,$type);
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
    public function getChatMessages($chatId , Request $request)
    {
        $user = $request->user()->id;

        $messages = $this->chatServices->GetChatMessages($chatId , $user);

        if ($messages) {
            return response()->json([
                'success' => true,
                'messages' => $messages
            ],200);
        }
        return response()->json([
            'success' => false,
            'message' => 'could not find any message',
        ],401);
    }
    public function sendMessage(SendMessageRequest $request)
    {
        $data = $request->validated();

        $data['sender_id'] = $request->user()->id;

        $sentMessage = $this->chatServices->SendMessage($data);

        if ($sentMessage) {
            return response()->json([
                'success' => true,
                'message' => 'message sent successfully',
                'data' => $sentMessage,
            ],201);
        }

        return response()->json([
            'success' => false,
            'message' => 'could not create message',
        ],401);
    }
    public function editMessage(EditMessageRequest $request)
    {
        $data = $request->validated();

        $data['sender_id'] = $request->user()->id;

        $updatedMessage = $this->chatServices->EditMessage($data);

        if ($updatedMessage) {
            return response()->json([
                'success' => true,
                'message' => $updatedMessage,
            ],201);
        }
        return response()->json([
            'success' => false,
            'message' => 'could not edit message',
        ],406);
    }
    public function deleteMessage(DeleteMessageRequest $request)
    {
        $data = $request->validated();

        $data['user_id'] = $request->user()->id;

        $deletedMessage = $this->chatServices->DeleteMessage($data);

        if ($deletedMessage) {
            return response()->json([
                'success' => true,
                'message' => 'message deleted successfully',
            ],201);
        }
        return response()->json([
            'success' => false,
            'message' => 'could not delete message',
        ],406);
    }
}
