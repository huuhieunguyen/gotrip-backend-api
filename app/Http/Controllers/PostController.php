<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Post;

class PostController extends Controller
{
    public function getPosts(Request $request)
    {
        $perPage = $request->query('perPage', 4);
        $posts = Post::orderBy('created_at', 'desc')->paginate($perPage);

        $posts->load(['author:id,name,avatar_url,is_active,last_active_time']);
        $posts->load('images');
        return response()->json($posts);
    }

    public function getPostsWithLikes(Request $request)
    {
        $perPage = $request->query('perPage', 4);
        $posts = Post::with('likesWithUsers')
                        ->orderBy('created_at', 'desc')->paginate($perPage);

        $posts->load(['author:id,name,avatar_url,is_active,last_active_time']);
        $posts->load('images');
        return response()->json($posts);
    }

    // public function index(Request $request)
    // {
    //     $perPage = $request->query('perPage', 4);
        
    //     $posts = Post::with('likesWithUsers', 
    //                         'author:id,name,avatar_url,is_active,last_active_time', 
    //                         'images')
    //                 ->orderBy('created_at', 'desc')
    //                 ->paginate($perPage);

    //     // $posts = Post::with([
    //     //             'likesWithUsers' => function ($query) {
    //     //                 $query->select('id')
    //     //                     ->with('user:id,name,avatar_url,cover_image_url,is_active');
    //     //             },
    //     //             'author:id,name,avatar_url,is_active,last_active_time',
    //     //             'images',
    //     //         ])->orderBy('created_at', 'desc')->paginate($perPage);
        
    //     return response()->json($posts);        
    // }

    public function getPostsByAuthorID(Request $request, $authorId)
    {
        try {
            $perPage = $request->query('perPage', 4);
            $posts = Post::where('author_id', $authorId)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            if ($posts->isEmpty() && $posts->currentPage() > 1) {
                return response()->json(['message' => 'No more posts available for the given author ID'], 404);
            }

            $posts->load('images');

            $author = $posts->first()->author;
            $posts->getCollection()->transform(function ($post) {
                $post->unsetRelation('author');
                return $post;
            });

            $responseData = [
                'author' => $author,
                // 'posts' => $posts->items(),
                'posts' => $posts,
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ];

            return response()->json($responseData);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while retrieving posts'], 500);
        }
    }

    public function getPostsByAuthorIdWithLikes(Request $request, $authorId)
    {
        try {
            $perPage = $request->query('perPage', 4);
            $posts = Post::with('likesWithUsers')
                ->where('author_id', $authorId)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            // $posts = Post::with('likesWithUsers');

            if ($posts->isEmpty() && $posts->currentPage() > 1) {
                return response()->json(['message' => 'No more posts available for the given author ID'], 404);
            }

            $posts->load('images');

            $author = $posts->first()->author;
            $posts->getCollection()->transform(function ($post) {
                $post->unsetRelation('author');
                return $post;
            });

            $responseData = [
                'author' => $author,
                // 'posts' => $posts->items(),
                'posts' => $posts,
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ];

            return response()->json($responseData);
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
        // $post->load('author', 'images');
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
            'like_count' => 'nullable|integer'
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
