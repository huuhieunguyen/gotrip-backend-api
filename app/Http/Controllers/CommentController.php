<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\CommentResource;
use App\Http\Resources\CommentWithoutPostResource;
use App\Events\CommentPosted;
use App\Events\CommentSent;
use App\Models\Comment;
use App\Models\Post;

class CommentController extends Controller
{
    public function createComment(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();

        // Validate the request data
        $validatedData = $request->validate([
            'content' => 'required',
            'post_id' => 'required|exists:posts,id',
        ]);

        // Create a new comment
        $comment = Comment::create([
            'user_id' => $user->id,
            'post_id' => $validatedData['post_id'],
            'content' => $validatedData['content'],
        ]);

        // broadcast(new CommentSent($comment));
        event(new CommentSent($comment));

        return response()->json([
            'message' => 'Comment sent successfully',
            'comment' => new CommentResource($comment),
        ],200);
    }

    public function getCommentById($id)
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }

        return response()->json([
            'comment' => new CommentResource($comment),
        ],200);
    }

    public function getCommentsByPost(Request $request, $postId)
    {
        $post = Post::find($postId);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $perPage = $request->query('perPage', 10);
        $comments = $post->comments()->paginate($perPage);

        return response()->json([
            'post' => $post,
            // 'author' => $post->author,
            'comments' => CommentWithoutPostResource::collection($comments),
            'pagination' => [
                'current_page' => $comments->currentPage(),
                'last_page' => $comments->lastPage(),
                'per_page' => $comments->perPage(),
                'total' => $comments->total(),
            ]
        ],200);
    }

    public function updateCommentById(Request $request, $commentId)
    {
        $comment = Comment::find($commentId);

        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }

        // Check if the authenticated user is the owner of the comment
        if (Auth::id() !== $comment->user_id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $comment->content = $request->input('content');
        $comment->save();

        return response()->json(['message' => 'Comment updated successfully', 'comment' => $comment], 200);
    }

    public function deleteCommentById($commentId)
    {
        $comment = Comment::find($commentId);

        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }

        // Check if the authenticated user is the author of the post or the owner of the comment
        if (Auth::id() !== $comment->post->user_id && Auth::id() !== $comment->user_id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $comment->delete();

        return response()->json(['message' => 'Comment deleted successfully'], 200);
    }
}
