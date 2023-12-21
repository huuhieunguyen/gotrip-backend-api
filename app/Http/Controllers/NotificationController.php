<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function getNotifications(Request $request)
    {
        $perPage = $request->query('perPage', 10);

        $user = Auth::user();
        
        // $notifications = $user->notifications()->paginate($perPage);
        $notifications = Notification::where('user_id', $user->id)->paginate($perPage);

        $responseData = [
            'notifications' => $notifications,
            // 'current_page' => $notifications->currentPage(),
            // 'last_page' => $notifications->lastPage(),
            // 'per_page' => $notifications->perPage(),
            // 'total' => $notifications->total(),
        ];

        return response()->json($responseData);
    }
}
