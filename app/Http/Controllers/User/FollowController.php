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
    public function follow(User $followee)
    {
        /** @var \App\Models\User $user **/
        $user = auth()->user();
        echo $user;
        // Check if the user is already following the given user
        if ($user->followees()->where('followee_id', $followee->id)->exists()) {
            return response()->json(['message' => 'You are already following this user.']);
        }

        $user->followees()->attach($followee->id);
        dd($followee->id);
        return response()->json(['message' => 'Successfully followed user.']);
    }

    public function unfollow(User $followee)
    {
        /** @var \App\Models\User $user **/
        $user = auth()->user();

        // Check if the user is not following the given user
        if (!$user->followees()->where('followee_id', $followee->id)->exists()) {
            return response()->json(['message' => 'You are not following this user, so you can not unfollow!']);
        }

        $user->followees()->detach($followee->id);

        return response()->json(['message' => 'Successfully unfollowed user.']);
    }

    // public function follow(Request $request)
    // {
    //     $loggedInUser = $request->user();
    //     $userId = $request->input('user_id');

    //     // Check if the user is already following the given user
    //     if ($loggedInUser->followees()->where('followee_id', $userId)->exists()) {
    //         return response()->json(['message' => 'You are already following this user.']);
    //     }

    //     $loggedInUser->followees()->attach($userId);

    //     return response()->json(['message' => 'Successfully followed user.']);
    // }

    // public function unfollow(Request $request)
    // {
    //     $loggedInUser = $request->user();
    //     $userId = $request->input('user_id');

    //     // Check if the user is not following the given user
    //     if (!$loggedInUser->followees()->where('followee_id', $userId)->exists()) {
    //         return response()->json(['message' => 'You are not following this user.']);
    //     }

    //     $loggedInUser->followees()->detach($userId);

    //     return response()->json(['message' => 'Successfully unfollowed user.']);
    // }

    // public function follow(Request $request)
    // {
    //     /** @var \App\Models\User $user **/
    //     $user = Auth::user();
    //     $followee = User::findOrFail($request->input('followee_id'));
    //     $followeesArray = $user->followees->pluck('id')->toArray();
        
    //     if (!in_array($followee->id, $followeesArray)) {
    //         $user->push('followees', $followee->id, true);
    //         $followee->push('followers', $user->id, true);
    //         return response()->json(['message' => 'Following successful'], 200);
    //     } else {
    //         return response()->json(['message' => 'You are already following this user'], 400);       
    //     }

    //     // if (!in_array($followee->id, $followeesArray)) {
    //     //     $user->followees()->attach($followee->id);
    //     //     $followee->followers()->attach($user->id);
    //     //     return response()->json(['message' => 'Following successful'], 200);
    //     // } else {
    //     //     return response()->json(['message' => 'You are already following this user'], 400);       
    //     // }
    // }

    // public function unfollow(Request $request)
    // {
    //     /** @var \App\Models\User $user **/
    //     $user = Auth::user();
    //     $followee = User::findOrFail($request->input('followee_id'));
    //     $followeesArray = $user->followees->pluck('id')->toArray();

    //     if (in_array($followee->id, $followeesArray)) {
    //         $user->pull('followees', $followee->id);
    //         $followee->pull('followers', $user->id);
    //         return response()->json(['message' => 'Unfollow successfully'], 200);
    //     } else {
    //         return response()->json(['message' => 'You can\'t unfollow because you are not friends.'], 400);       
    //     }

    //     // if ($user->followees()->find($followee->id)) {
    //     //     $user->followees()->detach($followee->id);
    //     //     $followee->followers()->detach($user->id);
    //     //     return response()->json(['message' => 'Unfollow successfully'], 200);
    //     // } else {
    //     //     return response()->json(['message' => 'You can\'t unfollow because you are not friends.'], 400);       
    //     // }

    //     // return response()->json(['message' => 'You are not following this user'], 400);
    // }

    // public function getAllFollowers()
    // {
    //     /** @var \App\Models\User $user **/
    //     $user = Auth::user();

    //     return response()->json(['followers' => $user->followers], 200);
    // }

    // public function getAllFollowees()
    // {
    //     /** @var \App\Models\User $user **/
    //     $user = Auth::user();

    //     return response()->json(['followees' => $user->followees], 200);
    // }

    public function getFollowers(Request $request)
    {
        $user = $request->user();
        $followers = $user->getFollowers();

        return response()->json(['followers' => $followers]);
    }

    public function getFollowees(Request $request)
    {
        $user = $request->user();
        $followees = $user->getFollowees();

        return response()->json(['followees' => $followees]);
    }
    
    /* Easy Code */
	// public function follow(User $userToFollow) { 
    //     /** @var \App\Models\User $currentUser **/
	// 	$currentUser = Auth::user(); 
    //     $currentUser->follow($userToFollow);
	//     return response()->json(['message' => 'Followed successfully']);
	// }
    
    // public function unfollow(User $userToUnfollow)
	// {
	// 	  $currentUser = Auth::user();
	// 	  $currentUser->unfollow($userToUnfollow);

	// 	  return response()->json(['message' => 'Unfollowed successfully']);
	// }

    /* Bing AI */
    // public function follow(User $userToFollow)
    // {
    //     /** @var \App\Models\User $currentUser **/
    //     $currentUser = Auth::user();

    //     // Add the target user's id to the current user's following array
    //     $currentUser->following = array_add($currentUser->following, $userToFollow->_id);
    //     $currentUser->save();

    //     // Add the current user's id to the target user's followers array
    //     $userToFollow->followers = array_add($userToFollow->followers, $currentUser->_id);
    //     $userToFollow->save();

    //     return response()->json(['message' => 'Followed successfully']);
    // }

    // public function unfollow(User $userToUnfollow)
    // {
    //     /** @var \App\Models\User $currentUser **/
    //     $currentUser = Auth::user();

    //     // Remove the target user's id from the current user's following array
    //     $currentUser->following = array_remove($currentUser->following, $userToUnfollow->_id);
    //     $currentUser->save();

    //     // Remove the current user's id from the target user's followers array
    //     $userToUnfollow->followers = array_remove($userToUnfollow->followers, $currentUser->_id);
    //     $userToUnfollow->save();

    //     return response()->json(['message' => 'Unfollowed successfully']);
    // }
}
