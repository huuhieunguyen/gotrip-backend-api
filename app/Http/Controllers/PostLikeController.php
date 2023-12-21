<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use App\Models\Like;
use App\Models\Post;
use App\Models\Notification;
use Pusher\Pusher;
use App\Handlers\NotificationHandler;
use App\Events\PostLiked;
use App\Http\Requests\Notification\SendNotiRequest;

class PostLikeController extends Controller
{
    public function store(SendNotiRequest $request)
    {
        $user = Auth::user();

        try {
            $post = Post::findOrFail($request->post_id);
        } catch (ModelNotFoundException $exception) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        // Check if the user has already liked the post
        $existingLike = Like::where('user_id', $user->id)
                            ->where('post_id', $post->id)
                            ->first();
        
        if ($existingLike) {
            return response()->json(['message' => 'You have already liked the post'], 400);
        }

        // Create the post like
        $postLike = Like::create([
            'user_id' => $user->id,
            'post_id' => $request->post_id,
        ]);
        
        // Update the like_count field in the posts table
        $post->increment('like_count');

        $notification = Notification::create([
            'user_id' => $postLike->post->author_id,
            'message' => "{$user->name} liked your post.",
        ]);

        // Broadcast the like notification to the author of the post
        broadcast(new PostLiked($postLike));
        
        $post->load(['author:id,name,avatar_url,is_active,last_active_time']);
        $post->load('images');
        return response()->json([
            'message' => 'Post liked successfully',
            'user_like' => $user,
            'post' => $post
        ], 201);
    }

    public function destroy($postId)
    {
        $user = Auth::user();

        try {
            $post = Post::findOrFail($postId);
        } catch (ModelNotFoundException $exception) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $like = Like::where('user_id', $user->id)->where('post_id', $post->id)->first();
        if (!$like) {
            return response()->json(['message' => 'User has not liked the post'], 404);
        }

        $like->delete();

        // Update the like_count field in the posts table
        $post->decrement('like_count');

        return response()->json([
            'message' => 'Post unliked successfully',
            'post_id' => $post->id
        ]);
    }
}
