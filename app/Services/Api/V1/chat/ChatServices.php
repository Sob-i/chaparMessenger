<?php

namespace App\Services\Api\V1\chat;

use App\Http\Requests\Api\V1\SendMessageRequest;
use App\Models\ChatMembersModel;
use App\Models\ChatMessagesModel;
use App\Models\ChatModel;

class ChatServices
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {

    }

    public function Chats($userId)
    {
        $chatsId = $this->GetChats($userId);
        foreach ($chatsId as $chatId) {
            $chats [] = ChatModel::where('id' , $chatId)->get();
        }
        return $chats;
    }
    private function GetChats($userId)
    {
        return ChatMembersModel::where('user_id' , $userId)->orderBy('updated_at' , 'DESC')->get('chat_id')->toArray();
    }
    public function CreateChat(?array $data)
    {
        return ChatModel::create([
            'type' => $data['type'],
            'name' => $data['name'] ?? null,
        ]);
    }
    public function CreateChatMembers($chatId , $MemberId , $type)
    {
        if ($MemberId) {

            $memberIds = json_decode($MemberId,true);

            foreach ($memberIds as $memberId) {
                $createdMembers [] = ChatMembersModel::create([
                    'chat_id' => $chatId,
                    'user_id' => $memberId,
                    'type' => $type
                ]);
            }

            foreach ($createdMembers as $createdMember) {
                $Ids[] = $createdMember['user_id'];
            }


            return $Ids;
        }
        return null;
    }
    public function GetChatMessages($chatId)
    {
        return ChatMessagesModel::where('chat_id' , $chatId)->orderBy('created_at' , 'DESC')->with(['senderInfo:id,name','receiverInfo:id,name'])->get();
    }
    public function SendMessage(array $data)
    {
        return ChatMessagesModel::create([
            'chat_id' => $data['chat_id'],
            'sender_id' => $data['sender_id'],
            'receiver_id' => $data['receiver_id'],
            'message' => $data['message'] ?? null,
            'attachments' => $data['attachments'] ?? null,
            'type' => $data['type'],
        ]);
    }
    public function EditMessage(array $data)
    {
        $message = ChatMessagesModel::where('id',$data['id'])->where('chat_id',$data['chat_id'])->where('sender_id' , auth()->id())->firstOrFail();

        if ($message->message != $data['message']) {

            $message->update([
                'message' => $data['message']
            ]);
            return [
                'id' => $message['id'],
                'chat_id' => $message['chat_id'],
                'sender_id' => $message['sender_id'],
                'receiver_id' => $message['receiver_id'],
                'message' => $data['message'],
            ];
        }
    }
}
