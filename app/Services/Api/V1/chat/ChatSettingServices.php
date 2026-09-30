<?php

namespace App\Services\Api\V1\chat;



use App\Models\ChatMembersModel;
use App\Models\ChatMessagesModel;
use App\Models\GroupOrChannelSettingModel;

class ChatSettingServices
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {

    }

    public function IsOwnerOrAdmin($data)
    {
      return ChatMembersModel::where('chat_id', $data['chat_id'])->where('user_id', $data['ownerOrAdmin'])->exists();
    }
    public function GetChatSettings($chatId)
    {
        return GroupOrChannelSettingModel::where('chat_id', $chatId)->firstOrFail()->toArray();
    }
    public function ChatAdminStatus(array $data)
    {
        return ChatMembersModel::where('chat_id' , $data['chat_id'])->where('user_id', $data['user_id'])->update(['type' => $data['type']]);
    }
}
