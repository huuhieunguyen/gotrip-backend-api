<?php

namespace App\Listeners;

use App\Events\PostLiked;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PostLikedNotification;
use App\Models\Post;
use App\Models\User;

class PostLikedListener implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\PostLiked  $event
     * @return void
     */
    public function handle(PostLiked $event)
    {
        // extract the "postId" and "userId" from the event object.
        $postId = $event->postId;
        $userId = $event->userId;

        // Get the necessary data for the post and user
        $post = Post::find($postId);
        $user = User::find($userId);

        // Logic to send real-time notifications, update UI, etc.
        $message = "{$user->name} liked your post: {$post}";
        Notification::send($user, (new PostLikedNotification($message))->via('database'));
        
        // Notification::send($post->user, new PostLikedNotification($message));
    }
}
