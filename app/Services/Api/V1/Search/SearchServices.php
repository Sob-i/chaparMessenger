<?php

namespace App\Services\Api\V1\Search;



use App\Models\ChatMessagesModel;
use App\Models\ChatModel;
use App\Models\UserProfileModel;
use function PHPUnit\Framework\stringContains;

class SearchServices
{
    /**
     * Create a new class instance.
     */

    public function Search(array $data)
    {
        if ($data['type'] == 'chat') {
            $chatIds = $this->ChatIds($data['user']['chats']);
            return [
                'chats' => ChatModel::whereIn('type' , ['PublicChannel' , 'PublicGroup'])->where('name' , 'LIKE'  , '%' . $data['searchKey'] . '%')->paginate(10)
                ,
                'users' => stringContains($data['searchKey'],'@') ? UserProfileModel::where('user_name' , 'LIKE'  , '%' . $data['searchKey'] . '%')->paginate(10) : null
                ,
                'chatMessages' => ChatMessagesModel::whereIn('chat_id' , $chatIds)->paginate(10),
                ];
        }
        elseif ($data['type'] == 'message') {

            return ChatMessagesModel::where('chat_id' , $data['chatId'])->where('message' , 'LIKE'  , '%' . $data['searchKey'] . '%')->paginate(20);

        }
        return false;
    }
    private function ChatIds($data)
    {
        foreach ($data as $chatId) {
            $ids[] = $chatId->id;
        }
        return $ids;
    }

}
