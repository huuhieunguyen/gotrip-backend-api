<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Utils\TimezoneConverter;
use App\Models\User;

class UserController extends Controller
{
    // GET all users in the databse
    public function index(Request $request)
    {
        $nameQuery = $request->query('name');

        if ($nameQuery) {
            $users = User::where('name', 'like', "%$nameQuery%")->get();
        } else {
            $users = User::all();
        }

        return response()->json($users);
    }

    //  GET a specific user
    public function show($userId)
    {
        try {
            $user = User::findOrFail($userId);
        } catch (ModelNotFoundException $exception) {
            return response()->json(['message' => 'UserID not found'], 404);
        }
        
        return response()->json($user);
    }

    // Delete a specific user
    public function destroy($userId)
    {
        try {
            $user = User::findOrFail($userId);
        } catch (ModelNotFoundException $exception) {
            return response()->json(['message' => 'UserID not found'], 404);
        }

        $user->delete();

      return response()->json([
        'message' => 'User deleted successfully',
        'Deleted user' => $user
        ]);
    }

    /**
    * Update the specified resource in storage.
    *
    * @param \Illuminate\Http\Request $request
    * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        /** @var \App\Models\User $user **/
        $user = Auth::user();

        $personalInfoFields = ['name', 'email', 'phone_number', 'intro', 'avatar_url', 'cover_image_url', 'portfolio_url'];

        // Validate the request data
        $validator = Validator::make($request->only($personalInfoFields), [
            'name' => 'string|max:255',
            // $user->id is used to exclude the current user's email from the uniqueness check.
            'email' => 'email|unique:users,email,' . $user->id, 
            'phone_number' => 'string|regex:/^[0-9]{9,11}$/',
            'intro' => 'string',
            'avatar_url' => 'url',
            'cover_image_url' => 'url',
            'portfolio_url' => 'url'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // update the user model
        // $user->fill($request->only($personalInfoFields));
        $user->update($request->only($personalInfoFields));

        // Check if any personal info fields are being updated
        if ($user->isDirty($personalInfoFields)) {
            $user->updated_at = TimezoneConverter::convertToTimezone(now());
        }

        // Save the user model
        $user->save();

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user,
        ], 200);
    }
}
