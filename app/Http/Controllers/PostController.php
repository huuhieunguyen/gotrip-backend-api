<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use App\Models\Post;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->query('perPage', 4);
        $posts = Post::orderBy('created_at', 'desc')->paginate($perPage);
        return response()->json($posts);
    }

    public function show(Request $request, $authorId)
    {
        try {
            $perPage = $request->query('perPage', 4);
            $posts = Post::where('author_id', $authorId)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            if ($posts->isEmpty() && $posts->currentPage() > 1) {
                return response()->json(['message' => 'No more posts available for the given author ID'], 404);
            }

            return response()->json($posts);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while retrieving posts'], 500);
        }
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'content' => 'nullable|string',
            'images' => 'array', //'images' field should be an array.
            'images.*' => 'url', //each item in the 'images' array should be a valid URL.
            'location' => 'nullable|string',
        ]);

        $post = $user->posts()->create($data);

        if (isset($data['images'])) {
            foreach ($data['images'] as $imageUrl) {
                $post->images()->create(['image_url' => $imageUrl]);
            }
        }

        $post->load(['author:id,name,avatar_url,is_active,last_active_time']);
        $post->load('images');
        return response()->json($post->toArray(), 201);
    }

    public function update(Request $request, $postId)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $data = $request->validate([
            'content' => 'nullable|string',
            'images' => 'array',
            'images.*' => 'url',
            'location' => 'nullable|string',
        ]);

        try {
            $post = $user->posts()->findOrFail($postId);
        } catch (ModelNotFoundException $exception) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $post->update($data);

        $post->images()->delete();

        if (isset($data['images'])) {
            foreach ($data['images'] as $imageUrl) {
                $post->images()->create(['image_url' => $imageUrl]);
            }
        }

        $post->load('images');
        return response()->json($post);
    }

    public function destroy(Request $request, $postId)
    {
        $user = $request->user();

        try {
            $post = $user->posts()->findOrFail($postId);
        } catch (ModelNotFoundException $exception) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $post->delete();

        return response()->json(['message' => 'Post deleted successfully']);
    }
}
