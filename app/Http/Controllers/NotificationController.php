<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function getNotifications()
    {
        /** @var \App\Models\User $user **/
        $user = Auth::user();

        $notifications = $user->sentNotifications()
            ->with(['user']) // Eager load the user relationship
            ->paginate(10);

        // Transform the notifications to include the avatar_url
        $transformedNotifications = $notifications->map(function ($notification) {
            return [
                'id' => $notification->id,
                'author_id' => $notification->author_id,
                'post' => $notification->post_id,
                'user_id' => $notification->user_id,
                'user_avatar_url' => $notification->user->avatar_url,
                'type' => $notification->type,
                'message' => $notification->message,
                'is_read' => $notification->is_read,
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at,
            ];
        });

        return response()->json([
            'notifications' => $transformedNotifications,
            'pagination' => [
                'total' => $notifications->total(),
                'per_page' => $notifications->perPage(),
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'from' => $notifications->firstItem(),
                'to' => $notifications->lastItem(),
            ],
        ], 200);
    }

    public function markAsRead($notificationId)
    {
        /** @var \App\Models\User $user **/
        $user = Auth::user();

        // Find the notification by ID
        try {
            $notification = Notification::findOrFail($notificationId);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Notification Not Found.',
            ], 404);
        }

        // Only allow the author of the notification to mark it as read
        if ($notification->author_id === $user->id) {
            // Update the is_read field to true
            $notification->is_read = true;
            $notification->save();
        }

        return response()->json([
            'message' => 'Notification marked as read',
            'is_read' => $notification->is_read
        ], 200);
    }
}
