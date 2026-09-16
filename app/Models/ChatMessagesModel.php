<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessagesModel extends Model
{
        protected $table = 'chat_messages';
        protected $fillable = ['chat_id', 'sender_id', 'receiver_id', 'message', 'attachments' , 'type' , 'reply_to_message' , 'reply_to_user'];
        public function senderInfo()
        {
            return $this->belongsTo(User::class,'sender_id','id');
        }
        public function receiverInfo()
        {
            return $this->belongsTo(User::class,'receiver_id','id');
        }
        public function repliedMessageInfo()
        {
            return $this->belongsTo(ChatMessagesModel::class,'reply_to_message','id');
        }
}
