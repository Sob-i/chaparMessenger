<?php

namespace App\Services\Api\V1\chat;

use App\Models\ChatMembersModel;
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
}
