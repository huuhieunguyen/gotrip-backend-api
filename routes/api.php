<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;

use App\Http\Controllers\AuthenController;
use App\Http\Controllers\Authen\ChangePasswordController;
// use App\Http\Controllers\Authen\ForgotPasswordController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\FollowController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostLikeController;
use App\Http\Controllers\CommentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Broadcast::routes(['middleware' => ['auth:sanctum']]);
// Broadcast::routes(["prefix" => "api", "middleware" => ['auth:sanctum']]);

Route::prefix('v1/authen')->group(function () {
    Route::post('/register', [AuthenController::class, 'register']);
    Route::post('/login', [AuthenController::class, 'login']);

    Route::post('/forgot-password', [App\Http\Controllers\Authentication\ForgotPasswordController::class, 'forgotPassword']);
    Route::post('/verify/pin', [App\Http\Controllers\Authentication\ForgotPasswordController::class, 'verifyPin']);
    Route::post('/reset-password', [App\Http\Controllers\Authentication\ResetPasswordController::class, 'resetPassword']);
});

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('v1/authen')->group(function () {
        Route::post('/logout', [AuthenController::class, 'logout']);
        Route::patch('/user', [UserController::class, 'update']);
        Route::patch('/change-password', [ChangePasswordController::class, 'changePassword']);
    });

    Route::prefix('v1/posts')->group(function () {
        Route::post('/', [PostController::class, 'store']);
        Route::patch('/{postId}', [PostController::class, 'update']);
        Route::delete('/{postId}', [PostController::class, 'destroy']);

        Route::post('/like', [PostLikeController::class, 'store']);
        Route::delete('/{postId}/unlike', [PostLikeController::class, 'destroy']);
    });

    Route::prefix('v1/user-relationships')->group(function () {
        Route::get('/followers', [FollowController::class, 'getFollowers']);
        Route::get('/followees', [FollowController::class, 'getFollowees']);
        Route::get('/{userId}', [FollowController::class, 'getRelationshipStatus']);

        Route::post('/follow', [FollowController::class, 'follow']);
        Route::delete('/unfollow', [FollowController::class, 'unfollow']);
    });

    // Chat routes
    Route::prefix('v1/chat')->group(function () {
        Route::post('/create-chat',[ChatController::class, 'createChat']);
        Route::get('/get-chats',[ChatController::class, 'getChats']);
        Route::get('/search-user',[ChatController::class, 'searchUsers']);
        Route::post('/send-text-message',[ChatController::class, 'sendTextMessage']);
        Route::patch('/message-status/{message}',[ChatController::class, 'messageStatus']);
        Route::get('/get-messages-by-id/{chat}',[ChatController::class, 'getMessagesById']);
    });

    Route::prefix('v1/comment')->group(function () {
        Route::post('/create-comment', [CommentController::class, 'createComment']);

        Route::put('/{commentId}', 'CommentController@updateCommentById');
        Route::delete('/{commentId}', 'CommentController@deleteCommentById');
    });
});

// Users
Route::prefix('v1/users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::get('/{id}', [UserController::class, 'show']);
    Route::delete('/{id}', [UserController::class, 'destroy']);

});

// Posts
Route::prefix('v1/posts')->group(function () {
    Route::get('/', [PostController::class, 'getPostsWithLikes']);
    Route::get('/posts_without_users_like', [PostController::class, 'getPosts']);
    Route::get('/{authorId}', [PostController::class, 'getPostsByAuthorIdWithLikes']);
    Route::get('/by_author_without_users_like/{authorId}', [PostController::class, 'getPostsByAuthorID']);
});

Route::prefix('v1/comment')->group(function () {
    Route::get('/get-comments/{postId}', [CommentController::class, 'getCommentsByPost']);

});

/*
    Just a test
*/
Route::get('/hello_world/', function (Request $request) {
    return ['msg' => 'hello_world'];
});

/*
    Laravel check on the Postgres connection
*/
Route::get('/test_postgres/', function (Request $request) {
    try {
        DB::connection()->getPdo();
        return ['status' => 'executed', 'data' => 'Successfully connected to the DB.' ];
    } catch (\Exception $e) {
        return ['status' => 'FAIL. exception', 'data' => $e ];
    }
});
