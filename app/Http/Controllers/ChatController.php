<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Chat;
use App\Http\Requests\Chat\CreateChatRequest;
use App\Http\Requests\Chat\SendTextMessageRequest;
use App\Models\ChatMessages;
use App\Events\ChatMessageSent;
use App\Events\ChatMessageStatus;
use App\Http\Resources\ChatResource;
use App\Http\Resources\MassageResource;
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

    // get all conversations of one user
    public function getChats(Request $request){
        $user = $request->user();
        $chats = $user->chats()->with('participants')->get();
        $success = true;
        return response()->json( [
            'chats' => $chats,
            'success' => $success
        ],200);
    }

    // the function for sending a message, 
    // we should check if the sender is a participant in this conversation or not,
    // then we create a new message with the status sent
    public function sendTextMessage(SendTextMessageRequest $request){
        $chat = Chat::find($request->chat_id);
        if($chat->isParticipant($request->user()->id)){
            $message = ChatMessages::create([
                'message' => $request->message,
                'chat_id' => $request->chat_id,
                'user_id' => $request->user()->id,
                'data' => json_encode(['seenBy'=>[],'status'=>'sent']) //sent, delivered,seen
            ]);
            $success = true;
            $message =  new MassageResource($message);
        
            // broadcast the message to all users 
            broadcast(new ChatMessageSent($message));

            foreach($chat->participants as $participant){
                if($participant->id != $request->user()->id){
                    $participant->notify(new NewMessage($message));
                }
            }
            
            return response()->json( [
                "message"=> $message,
                "success"=> $success
            ],200);
        } else{
            return response()->json([
                'message' => 'not found'
            ], 404);
        }
    }


    // When the users receive the message,
    // they will send a request to change the message status.
    // so we can add them to 'seenBy' array.
    public function messageStatus(Request $request,ChatMessages $message){
        if($message->chat->isParticipant($request->user()->id)){
            $messageData = json_decode($message->data);
            array_push($messageData->seenBy,$request->user()->id);
            $messageData->seenBy = array_unique($messageData->seenBy);
        
            //Check if all participant have seen or not
            if(count($message->chat->participants)-1 < count( $messageData->seenBy)){
                $messageData->status = 'delivered';
            } else{
                $messageData->status = 'seen';    
            }
            $message->data = json_encode($messageData);
            $message->save();
            $message =  new MassageResource($message);
            
            //triggering the event
            broadcast(new ChatMessageStatus($message));

            return response()->json([
                'message' =>  $message,
                'success' => true
            ], 200);
        } else{
            return response()->json([
                'message' => 'Not found',
                'success' => false
            ], 404); 
        }
    }

    // get a chat by id
    public function getChatById(Chat $chat,Request $request){
        if($chat->isParticipant($request->user()->id)){
            $messages = $chat->messages()->with('sender')->orderBy('created_at','asc')->paginate('150');
            return response()->json( [
               'chat' => new ChatResource($chat),
               'messages' => MassageResource::collection($messages)->response()->getData(true)
            ],200);
        }else{
            return response()->json([
                'message' => 'not found'
            ], 404);
        }
    }

    // search for a user by email so can start a chat with them, 
    // We will use 'like' for a quick search with suggestions 
    // and we will limit 3 results only.
    public function searchUsers(Request $request){
        $users = User::where('email','like',"%{$request->email}%")->limit(3)->get();
        return response()->json( [
            'users'=> $users ,
        ],200);
    }
}
