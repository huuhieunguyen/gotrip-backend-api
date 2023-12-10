<?php

namespace App\Utils;

use App\Events\ChatMessageSent;
use App\Notifications\NewMessage;
use App\Http\Resources\MessageResource;

class ChatUtils {
    public function broadcastMessage(MessageResource $message)
    {
      broadcast(new ChatMessageSent($message))->toOthers();
    }

    public function sendNotification(MessageResource $message)
    {
      $participants = $message->chat->participants;

      foreach ($participants as $participant) {
        if ($participant->id != $message->user_id) {
            $participant->notify(new NewMessage($message));
        }
      }
    }
}
