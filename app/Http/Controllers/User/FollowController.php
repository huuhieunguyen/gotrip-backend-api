<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Utils\TimezoneConverter;
use App\Models\User;
use App\Models\Relationship;

class FollowController extends Controller
{
    public function follow(Request $request)
    {
        /** @var \App\Models\User $authUser **/
        $authUser = Auth::user();
        $userId = $request->input('user_id');

        // Check if the authenticated user is trying to follow their own account
        if ($authUser->id === $userId) {
            return response()->json(['message' => 'You cannot follow your own account'], 400);
        }

        // Check if the authenticated user is already following the user
        if ($authUser->followees()->where('followee_id', $userId)->exists()) {
            return response()->json(['message' => 'You are already following this user'], 400);
        }

        // Attach the user as a followee
        $authUser->followees()->attach($userId);

        return response()->json(['message' => 'User followed successfully']);
    }

    public function unfollow(Request $request)
    {
        /** @var \App\Models\User $authUser **/
        $authUser = Auth::user();
        $userId = $request->input('user_id');

        // Check if the authenticated user is trying to unfollow their own account
        if ($authUser->id === $userId) {
            return response()->json(['message' => 'You cannot unfollow your own account'], 400);
        }

        // Check if the authenticated user is currently following the user
        if (!$authUser->followees()->where('followee_id', $userId)->exists()) {
            return response()->json(['message' => 'You are not currently following this user'], 400);
        }

        // Detach the user as a followee
        $authUser->followees()->detach($userId);

        return response()->json(['message' => 'User unfollowed successfully']);
    }

    public function getfollowers()
    {
        $authUser = Auth::user();
        $followers = $authUser->followers;

        return response()->json(['followers' => $followers]);
    }

    public function getfollowees()
    {
        $authUser = Auth::user();
        $followees = $authUser->followees;

        return response()->json(['followees' => $followees]);
    }

    // public function getfollowers(Request $request)
    // {
    //     $perPage = $request->query('perPage', 4);

    //     /** @var \App\Models\User $authUser **/
    //     $authUser = Auth::user();
    //     $followers = $authUser->followers()->paginate($perPage);

    //     // return response()->json(['followers' => $followers]);
    //     return response()->json(['followers' => $followers->items(), 'pagination' => [
    //         'total' => $followers->total(),
    //         'per_page' => $followers->perPage(),
    //         'current_page' => $followers->currentPage(),
    //         'last_page' => $followers->lastPage(),
    //         'from' => $followers->firstItem(),
    //         'to' => $followers->lastItem(),
    //     ]]);
    // }

    // public function getfollowees(Request $request)
    // {
    //     $perPage = $request->query('perPage', 4);

    //     /** @var \App\Models\User $authUser **/
    //     $authUser = Auth::user();
    //     $followees = $authUser->followees->paginate($perPage);

    //     // return response()->json(['followees' => $followees]);
    //     return response()->json(['followees' => $followees->items(), 'pagination' => [
    //         'total' => $followees->total(),
    //         'per_page' => $followees->perPage(),
    //         'current_page' => $followees->currentPage(),
    //         'last_page' => $followees->lastPage(),
    //         'from' => $followees->firstItem(),
    //         'to' => $followees->lastItem(),
    //     ]]);
    // }
}
