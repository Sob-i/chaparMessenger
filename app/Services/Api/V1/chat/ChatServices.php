<?php

namespace App\Services\Api\V1\chat;

use App\Http\Requests\Api\V1\DeleteMessageRequest;
use App\Http\Requests\Api\V1\SendMessageRequest;
use App\Models\ChatMembersModel;
use App\Models\ChatMessagesModel;
use App\Models\ChatModel;
use Illuminate\Http\Request;

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
        return collect($chats)->flatten();
    }
    private function GetChats($userId)
    {
        return ChatMembersModel::where('user_id' , $userId)->orderBy('updated_at' , 'DESC')->get('chat_id')->toArray();
    }
    public function CreateChat(?array $data , $memberId)
    {
        if ($data['type'] == 'private' && $this->privateChatExists($memberId)) {
           return null;
        }

        return ChatModel::create([
            'type' => $data['type'],
            'name' => $data['name'] ?? null,
        ]);
    }
    public function CreateChatMembers($chatId, $MemberId, $type)
    {
        if (! $MemberId) {
            return null;
        }

        if ($type === 'private' && count($MemberId) > 2) {
            return false;
        }

        $rows = [];

        foreach ($MemberId as $key => $memberIds) {
            foreach ((array) $memberIds as $memberId) {
                $rows[] = [
                    'chat_id' => $chatId,
                    'user_id' => $memberId,
                    'type'    => $type === 'private'
                        ? 'PrivateMember'
                        : ($key === 'user_id' ? 'admin' : 'member'),
                ];
            }
        }

        $Ids = [];
        foreach ($rows as $row) {
            $Ids[] = ChatMembersModel::create($row)['user_id'];
        }

        return $Ids;
    }
    private function privateChatExists($memberIds)
    {
        return ChatMembersModel::where('type','PrivateMember')->whereIn('user_id' , $memberIds)->exists();
    }
    public function GetChatMessages($chatId , $userId)
    {
        $isChatMember = ChatMembersModel::where('chat_id' , $chatId)->where('user_id' , $userId)->exists();
        if ($isChatMember) {
            return ChatMessagesModel::where('chat_id' , $chatId)->orderBy('created_at' , 'DESC')->with(['senderInfo:id,name','receiverInfo:id,name','repliedMessageInfo:id,sender_id,message,attachments'])->get();
        }
        return null;
    }
    public function SendMessage(array $data)
    {
        if ($data['type'] == 'message') {
            return ChatMessagesModel::create([
                'chat_id' => $data['chat_id'],
                'sender_id' => $data['sender_id'],
                'receiver_id' => $data['receiver_id'],
                'message' => $data['message'] ?? null,
                'attachments' => $data['attachments'] ?? null,
                'type' => $data['type'],
            ]);
        }else{
            return ChatMessagesModel::create([
                'chat_id' => $data['chat_id'],
                'sender_id' => $data['sender_id'],
                'receiver_id' => $data['receiver_id'],
                'message' => $data['message'] ?? null,
                'attachments' => $data['attachments'] ?? null,
                'type' => $data['type'],
                'reply_to_message' => $data['reply_to_message'],
                'reply_to_user' => $data['reply_to_user'],
                ]);
        }

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
    public function DeleteMessage(array $data)
    {
        $message = ChatMessagesModel::where('id',$data['id'])->where('chat_id',$data['chat_id'])->where('sender_id' , auth()->id())->firstOrFail();

        return $message->delete();
    }
}
