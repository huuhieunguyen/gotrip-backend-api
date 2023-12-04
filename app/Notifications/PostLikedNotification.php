<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;


class PostLikedNotification extends Notification
{
    use Queueable;

    protected $message;

    
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line($this->message)
                    ->action('View Post', url('/posts/' . $notifiable->id))
                    ->line('Thank you for your Like!');
    }

    /**
     * Get the array representation of the notification.
     * This allows you to store the notification in the database for later retrieval or display.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'message' => $this->message,
            'post_id' => $notifiable->id,
        ];
    }

    /**
     * This method returns an array with the message and the post ID,
     * which will be broadcasted to the listening clients via Laravel WebSockets.
     */
    public function toBroadcast($notifiable)
    {
        return [
            'data' => [
                'message' => $this->message,
                'post_id' => $notifiable->id,
            ],
        ];
    }

    public function toDatabase($notifiable)
    {
        return new DatabaseMessage([
            'message' => $this->message,
        ]);
    }
}
