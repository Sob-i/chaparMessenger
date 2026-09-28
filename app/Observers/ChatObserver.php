<?php

namespace App\Observers;

use App\Models\ChatModel;
use App\Models\GroupOrChannelSettingModel;

class ChatObserver
{
    /**
     * Handle the ChatModel "created" event.
     */
    public function created(ChatModel $chatModel): void
    {
       if ($chatModel->type == 'PublicGroup' || $chatModel->type == 'PrivateGroup')
       {
           GroupOrChannelSettingModel::create([
               'chat_id' => $chatModel->id ,
               'type' => 'group'
           ]);
       }elseif ($chatModel->type == 'PrivateChannel' || $chatModel->type == 'PublicChannel')
       {
           GroupOrChannelSettingModel::create([
               'chat_id' => $chatModel->id ,
               'type' => 'channel' ,
               'users_can_send_message' => 'no' ,
               'users_can_send_photos' => 'no' ,
               'users_can_send_files' => 'no' ,
           ]);
       }
    }

    /**
     * Handle the ChatModel "updated" event.
     */
    public function updated(ChatModel $chatModel): void
    {
        //
    }

    /**
     * Handle the ChatModel "deleted" event.
     */
    public function deleted(ChatModel $chatModel): void
    {
        //
    }

    /**
     * Handle the ChatModel "restored" event.
     */
    public function restored(ChatModel $chatModel): void
    {
        //
    }

    /**
     * Handle the ChatModel "force deleted" event.
     */
    public function forceDeleted(ChatModel $chatModel): void
    {
        //
    }
}
