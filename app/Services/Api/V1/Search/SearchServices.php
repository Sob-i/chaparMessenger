<?php

namespace App\Services\Api\V1\Search;



use App\Models\ChatMessagesModel;
use App\Models\ChatModel;

class SearchServices
{
    /**
     * Create a new class instance.
     */

    public function Search(array $data)
    {
        if ($data['type'] == 'chat') {

            return ChatModel::whereIn('type' , ['PublicChannel' , 'PublicGroup'])->where('name' , 'LIKE'  , '%' . $data['searchKey'] . '%')->get();

        }elseif ($data['type'] == 'message') {

            return ChatMessagesModel::where('chat_id' , $data['chatId'])->where('message' , 'LIKE'  , '%' . $data['searchKey'] . '%')->get();

        }
        return false;
    }

}
