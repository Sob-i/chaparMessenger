<?php

namespace App\Http\Controllers\Api\V1\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CreateChatRequest;
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

        if (($data['type'] == 'PublicGroup' || $data['type'] == 'PrivateGroup' || $data['type'] == 'PublicChannel' || $data['type'] == 'PrivateChannel') && empty($data['name'])) {
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
            if ($createdChat->type == 'private') {
                $members = $this->chatServices->CreateChatMembers($createdChat->id,$Ids,$type);
            }else{
                $members = $this->chatServices->CreateChatMembers($createdChat->id,$Ids,'nonePrivate');
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
    public function getChats(Request $request)
    {
        $user = $request->user();

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
    public function searchChats(Request $request)
    {
        $data = [
            'type' => 'chat' ,
            'searchKey' => $request->headers->get('searchKey') ,
            ];

        $result = $this->chatServices->Search($data);

        if ($result->IsNotEmpty()) {
            return response()->json([
                'success' => true,
                'chats' => $result
            ],200);
        }
        return response()->json([
            'success' => false,
            'message' => 'could not find any chats',
        ],204);
    }
}
