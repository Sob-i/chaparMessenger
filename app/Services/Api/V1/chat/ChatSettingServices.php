<?php

namespace App\Services\Api\V1\chat;



use App\Models\ChatBannedMembersModel;
use App\Models\ChatMembersModel;
use App\Models\ChatMessagesModel;
use App\Models\GroupOrChannelSettingModel;
use Illuminate\Support\Facades\DB;

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
    public function BanMember(array $data)
    {
        return DB::transaction(function () use ($data) {

            $member = ChatMembersModel::where('chat_id', $data['chat_id'])->where('user_id', $data['user_id'])->where('type', 'member')->firstOrFail();

            ChatBannedMembersModel::create([
                'user_id' => $member->user_id,
                'chat_id' => $member->chat_id,
            ]);

            return $member->delete();

        });
    }
}
