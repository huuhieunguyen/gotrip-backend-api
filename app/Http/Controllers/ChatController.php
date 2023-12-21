<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\User;
use App\Models\Chat;
use App\Models\ChatMessages;
use App\Events\ChatMessageSent;
use App\Events\ChatMessageStatus;
use App\Http\Requests\Chat\CreateChatRequest;
use App\Http\Requests\Chat\SendTextMessageRequest;
use App\Http\Resources\ChatResource;
use App\Http\Resources\MessageResource;
use App\Notifications\NewMessage;

class ChatController extends Controller
{
    // function for creating a chat between 2 users
    public function createChat(CreateChatRequest $request){
        // retrieve the 'users' array from the CreateChatRequest object.
        $users = $request->users;
        // check if they had a chat before, return it
        $chat =  $request->user()->chats()->whereHas('participants',function($q) use($users){
            $q->where('user_id', $users[0]);
        })->first();

        if ($chat) {
            return response()->json([
                'message' => 'Users already had a chat',
                'chat_id' => $chat->id,
            ], 400);
        }

        //if not, create a new one
        if(empty($chat)){
            array_push($users,$request->user()->id);
            $chat = Chat::create()->makePrivate($request->isPrivate);
            $chat->participants()->attach($users); 
        }

        $success = true;
        return response()->json( [
            'chat' => new ChatResource($chat),
            'success' =>$success
        ],200);
    }

    // get all conversations of an authenticated user
    public function getChats(Request $request)
    {
        $user = $request->user();
        $perPage = $request->query('perPage', 4);

        $chats = $user->chats()
            ->with(['participants' => function ($query) {
                $query->select('user_id', 'name', 'email', 'avatar_url');
            }])
            ->paginate($perPage);

        if ($chats->isEmpty()) {
            return response()->json([
                'message' => 'Chat Conversations not found.',
            ], 404);
        }
        
        return response()->json([
            'chats' => $chats->items(),
            'pagination' => [
                'current_page' => $chats->currentPage(),
                'last_page' => $chats->lastPage(),
                'per_page' => $chats->perPage(),
                'total' => $chats->total(),
            ]
        ], 200);
    }

    // search for a user by name so can start a chat with them
    public function searchUsers(Request $request)
    {
        $perPage = $request->query('perPage', 4);
        $nameQuery = $request->query('name');
        $emailQuery = $request->query('email');
        $phoneQuery = $request->query('phone_number');
        
        $query = User::query();
        
        if (!empty($nameQuery)) {
            $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($nameQuery) . '%']);
        }
        
        if (!empty($emailQuery)) {
            $query->where('email', $emailQuery);
        }
        
        if (!empty($phoneQuery)) {
            $query->where('phone_number', $phoneQuery);
        }
        
        $users = $query->select('id', 'name', 'email', 'avatar_url', 'phone_number')
            ->orderBy('id', 'asc')
            ->paginate($perPage);
        
        if ($users->isEmpty()) {
            return response()->json([
                'message' => 'No users found.',
            ], 404);
        }
        
        return response()->json([
            'users' => $users,
        ], 200);
    }

    // the function for sending a message, 
    // we should check if the sender is a participant in this conversation or not,
    // then we create a new message with the status sent
    public function sendTextMessage(SendTextMessageRequest $request){
        try {
            $chat = Chat::findOrFail($request->chat_id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'ChatID Not Found.',
            ], 404);
        }
        
        if($chat->isParticipant($request->user()->id)){
            $message = ChatMessages::create([
                'message' => $request->message,
                'chat_id' => $request->chat_id,
                'user_id' => $request->user()->id,
                'data' => json_encode(['seenBy'=>[],'status'=>'sent']) //sent, received,seen
            ]);
            $success = true;
            $message =  new MessageResource($message);
        
            // broadcast the message to all users 
            broadcast(new ChatMessageSent($message));

            // foreach($chat->participants as $participant){
            //     if($participant->id != $request->user()->id){
            //         $participant->notify(new NewMessage($message));
            //     }
            // }
            
            return response()->json( [
                "message"=> $message,
                "success"=> $success
            ],200);
        } else {
            return response()->json([
                'message' => 'Not authorized to send messages in this chat.',
            ], 403);
        }
    }

//     public function sendTextMessage(SendTextMessageRequest $request)
// {
//     $senderId = $request->input('sender_id');
//     $chatId = $request->input('chat_id');
//     $messageContent = $request->input('message');

//     $chat = Chat::findOrFail($chatId);

//     // Check if the sender is a participant in the chat
//     if (!$chat->isParticipant($senderId)) {
//         return response()->json(['message' => 'Unauthorized'], 401);
//     }

//     // Create a new message
//     $message = new Message();
//     $message->chat_id = $chatId;
//     $message->sender_id = $senderId;
//     $message->content = $messageContent;
//     $message->status = 'sent';
//     $message->save();

//     // Broadcast the message to all users
//     broadcast(new TextMessageSent($message));

//     // Notify other participants
//     $otherParticipants = $chat->participants()->where('user_id', '!=', $senderId)->get();
//     foreach ($otherParticipants as $participant) {
//         $participant->user->notify(new NewMessageNotification($message));
//     }

//     return response()->json(['message' => 'Message sent successfully'], 200);
// }

    // When the users receive the message,
    // they will send a request to change the message status.
    // so we can add them to 'seenBy' array.
    public function messageStatus(Request $request, ChatMessages $message)
    {
        $userId = $request->user()->id;
        $messageUserId = $message->user_id;

        if ($userId === $messageUserId) {
            return response()->json([
                'message' => 'You cannot receive or see your own message',
                'success' => false
            ], 400);
        }

        if (!$message->chat->isParticipant($userId)) {
            return response()->json([
                'message' => 'Not found the conversation. Because you are not a participant in it',
                'success' => false
            ], 404);
        }

        $messageData = json_decode($message->data);
        $seenBy = collect($messageData->seenBy ?? []);

        if (!$seenBy->contains($userId)) {
            $seenBy->push($userId);

            if ($message->chat->participants->count() - 1 === $seenBy->count()) {
                $messageData->status = 'seen';
            } else {
                $messageData->status = 'received';
            }

            $messageData->seenBy = $seenBy->unique()->values()->toArray();

            $message->data = json_encode($messageData);
            $message->save();

            $message = new MessageResource($message);

            broadcast(new ChatMessageStatus($message));
        } else {
            return response()->json([
                'message' => 'You have already seen this message!',
                'success' => false
            ], 200);
        }

        $channel = 'private-chat.' . $message->chat_id;

        broadcast(new ChatMessageStatus($message));

        return response()->json([
            'message' => $message,
            'channel' => $channel,
            'success' => true
        ], 200);
    }

    // public function messageStatus(Request $request, ChatMessages $message)
    // {
    //     if (!$message->chat->isParticipant($request->user()->id)) {
    //         return response()->json([
    //             'message' => 'Not found the conversation',
    //             'success' => false
    //         ], 404);
    //     }

    //     $messageData = json_decode($message->data);
    //     $seenBy = collect($messageData->seenBy ?? [])
    //         ->push($request->user()->id)
    //         ->unique()
    //         ->toArray();

    //     $status = count($message->chat->participants->pluck('id')->except($message->user_id)) < count($seenBy) ? 'received' : 'seen';        $messageData->seenBy = $seenBy;
    //     $messageData->status = $status;

    //     $message->data = json_encode($messageData);
    //     $message->save();

    //     $message = new MessageResource($message);

    //     $channel = 'private-chat.' . $message->chat_id;

    //     broadcast(new ChatMessageStatus($message));

    //     return response()->json([
    //         'message' => $message,
    //         'channel' => $channel,
    //         'success' => true
    //     ], 200);
    // }

    // get messages in a chat by chat_id
    public function getMessagesById(Chat $chat,Request $request){
        $perPage = $request->query('perPage', 4);

        if($chat->isParticipant($request->user()->id)){
            $messages = $chat->messages()
                            ->with('sender')
                            ->orderBy('created_at','asc')
                            ->paginate($perPage);

            return response()->json( [
               'chat' => new ChatResource($chat),
               'messages' => MessageResource::collection($messages),
                'pagination' => [
                    'current_page' => $messages->currentPage(),
                    'last_page' => $messages->lastPage(),
                    'per_page' => $messages->perPage(),
                    'total' => $messages->total(),
                ]
            ],200);
        }else{
            return response()->json([
                'message' => 'Messages not found'
            ], 404);
        }
    }
}
