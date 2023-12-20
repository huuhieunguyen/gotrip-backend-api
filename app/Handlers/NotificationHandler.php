<?php

namespace App\Handlers;

use Pusher\Pusher;
use App\Models\Notification;

class NotificationHandler
{
    protected $pusher;

    public function __construct()
    {
        $this->pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'useTLS' => true, // Enable if using HTTPS
            ]
        );
    }

    public function sendNotification($channel, $event, $userId, $notification)
    {
        $this->pusher->trigger($channel, $event, [
            'user_id' => $userId,
            'notification' => $notification,
        ]);
    }

    public function storeNotification($userId, $message)
    {
        Notification::create([
            'user_id' => $userId,
            'message' => $message,
        ]);
    }
}