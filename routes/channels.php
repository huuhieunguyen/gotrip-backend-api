<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Chat;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// make sure that only these chat participants can subscribe to this channel 
// so no one else can see the chat messages
Broadcast::channel('chat.{id}', function ($user, $id) {
    $chat = Chat::find($id);
    if($chat->isParticipant($user->id)){
        return ['id' => $user->id, 'name' => $user->first_name];
    }
});
