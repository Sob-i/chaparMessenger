<?php

namespace App\Http\Controllers\Api\V1\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DeleteMessageRequest;
use App\Http\Requests\Api\V1\EditMessageRequest;
use App\Http\Requests\Api\V1\SendMessageRequest;
use App\Services\Api\V1\chat\ChatServices;
use Illuminate\Http\Request;
use App\Events\MessageSent;

class ChatMessageController extends Controller
{
    public function __construct(protected ChatServices $chatServices)
    {

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
            broadcast(new MessageSent($sentMessage));
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
