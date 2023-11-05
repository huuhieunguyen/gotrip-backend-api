<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// use App\Http\Controllers\Authentication\RegisterController;
// use App\Http\Controllers\Authentication\AuthenController;
// use App\Http\Controllers\Authentication\ForgotPasswordController;
// use App\Http\Controllers\Authentication\ResetPasswordController;

// use App\Http\Controllers\Api\AuthController;
// use App\Http\Controllers\Api\EmailVerificationController;
// use App\Http\Controllers\Api\NewPasswordController;
use App\Models\User;
use App\Http\Controllers\AuthenController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\FollowController;

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

Route::prefix('v1/authen')->group(function () {
    Route::post('/register', [AuthenController::class, 'register']);
    Route::post('/login', [AuthenController::class, 'login']);
});

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('v1/authen')->group(function () {
        Route::post('/logout', [AuthenController::class, 'logout']);
        Route::patch('/user', [UserController::class, 'update']);
    });
    
    Route::prefix('v1/user-relationships')->group(function () {
        Route::post('/follow/{id}', [FollowController::class, 'follow']);
        Route::post('/unfollow', [FollowController::class, 'unfollow']);
        Route::get('/followers', [FollowController::class, 'getFollowers']);
        Route::get('/followees', [FollowController::class, 'getFollowees']);
    });
});

// Users
Route::prefix('v1/users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::get('/{id}', [UserController::class, 'show']);
    Route::delete('/{id}', [UserController::class, 'destroy']);

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

// /*
//    Send a ping to our postgre cluster to see if our connection settings are correct
// */
// Route::get('/test_mongodb/', function (Request $request) {

//     $connection = DB::connection('mongodb');
//     $msg = 'MongoDB is accessible!';
//     try {
//         $connection->command(['ping' => 1]);
//     } catch (\Exception $e) {
//         $msg =  'MongoDB is not accessible. Error: ' . $e->getMessage();
//     }

//     return ['msg' => $msg];
// });