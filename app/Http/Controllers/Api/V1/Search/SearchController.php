<?php

namespace App\Http\Controllers\Api\V1\Search;

use App\Http\Controllers\Controller;
use App\Services\Api\V1\chat\ChatServices;
use App\Services\Api\V1\Search\SearchServices;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(protected ChatServices $chatServices , protected SearchServices $searchServices)
    {

    }
    public function search(Request $request)
    {
        $user = $request->user()->load(['chats' => function ($query) {
            $query->whereIn('chats.type', ['PublicGroup', 'PublicChannel']);
        }]);

        $data = [
            'type' => 'chat' ,
            'searchKey' => $request->headers->get('searchKey') ,
            'user' => $user
        ];

        $result = $this->searchServices->Search($data);

        if ($result) {
            return response()->json([
                'success' => true,
                'result' => $result
            ],200);
        }
        return response()->json([
            'success' => false,
            'message' => 'could not find any chats',
        ],204);
    }
    public function searchChatMessages(Request $request)
    {
        $data = [
            'type' => 'message' ,
            'chatId' => $request->id ,
            'searchKey' => $request->headers->get('searchKey') ,
            'userId' => $request->user()->id,
        ];

        if ($this->chatServices->IsMember($data))
        {
            $result = $this->searchServices->Search($data);

            if ($result->IsNotEmpty()) {
                return response()->json([
                    'success' => true,
                    'messages' => $result
                ],200);
            }
            return response()->json([
                'success' => false,
                'message' => 'could not find any message',
            ],204);
        }
        return response()->json([
            'success' => false,
            'message' => 'unauthorized',
        ],401);
    }


}
