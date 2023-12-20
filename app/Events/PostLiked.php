<?php

namespace App\Events;

use App\Models\Like;
use App\Models\Notification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostLiked implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $like;

    /**
     * Create a new event instance.
     *
     * @param  Like  $like
     * @return void
     */
    public function __construct(Like $like)
    {
        $this->like = $like;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        // Broadcast to the author of the post
        return new Channel('author-channel.'.$this->like->post->author_id);
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        // Create a notification for the author of the post
        $notification = Notification::create([
            'user_id' => $this->like->post->author_id,
            'message' => 'Your post has been liked!',
        ]);

        return [
            'like' => $this->like,
            'notification' => $notification,
        ];
    }

    /**
     * Get the event name for broadcasting.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'post.liked';
    }
}
