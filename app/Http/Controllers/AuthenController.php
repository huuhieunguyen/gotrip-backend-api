<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Utils\TimezoneConverter;
use App\Models\User;

class AuthenController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            // The regex rule enforces a pattern that requires at least one uppercase letter,
            // one lowercase letter, one number, one special character, and a minimum length of 8 characters.
            // Remember to include the password_confirmation field in the request payload structure alongside the password field.
            // Example request:
            /*
            {
                "name": "John Doe",
                "email": "john@example.com",
                "password": "mypassword",
                "password_confirmation": "mypassword",
                "phone_number": "1234567890"
            }
            */
            // 'password' => 'required|min:6|confirmed|regex:
            //                 /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/',
            'password' => 'required|min:6|confirmed'
        ]);

        $data = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),            
            // 'tag_name' => $this->generateValidTagName($request->input('name')),
            // 'phone_number' => "",
            // 'avatar_url' => "",
            // 'cover_image_url' => "",
            // 'portfolio_url' => "",
            // 'intro' => "",
            // 'is_active' => true,
            // 'last_active_time' => "",
            // 'inactice_duration' => 0,
            // 'count_followees' => 0,
            // 'count_followers' => 0,
            
            // 'receiver_id' => "",
            // 'sender_id' => "",
            
            // 'created_at' => TimezoneConverter::convertToTimezone(now()),
            // 'updated_at' => TimezoneConverter::convertToTimezone(now()),
            // 'created_at' => now(),
            // 'updated_at' => now(),
        ];
    
        $user = User::create($data);

        // $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful!',
            'user' => $user,
            // 'token' => $token
        ], 201);
    }

    private function generateValidTagName($name)
    {
        $tagName = preg_replace('/[^a-zA-Z0-9]+/', '', $name); // Remove non-alphanumeric characters
        $tagName = strtolower($tagName); // Convert to lowercase

        if (strpos($tagName, '@') !== 0) {
            $tagName = '@' . $tagName; // Add '@' at the beginning if not already present
        }
        return $tagName;
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            // Authentication successful, generate an API token for the user

            $user = Auth::user();
            /** @var \App\Models\User $user **/
            $token = $user->createToken('auth_token')->plainTextToken;

            // return response(['token' => $accessToken])->withCookie($cookie);
            return response()->json(
                [
                    'message'=>'Logged in successfully!',
                    'data'=> [
                        'user'=> $request->user(),
                        'token'=> $token
                    ]
                ]
            );

        } else {
            // Authentication failed
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json([
            'message' => 'Logged out successfully!',
            'data'=> [
                'user'=> $request->user()
            ],
        ], 201);
    }

//   public function forgetPassword(Request $request)
//   {
//       // Validate the request data
//       $validator = Validator::make($request->all(), [
//           'mail' => 'required|string|email',
//       ]);

//       if ($validator->fails()) {
//           return response()->json(['error' => $validator->errors()], 400);
//       }

//       // Attempt to find the user by email
//       $user = User::where('mail', $request->input('mail'))->first();

//       if (!$user) {
//           // User not found, return an error
//           return response()->json(['error' => 'User not found'], 404);
//       }

//       // Generate a new password reset token
//       $token = Str::random(60);

//       // Update the user's password reset token
//       $user->update(['password_reset_token' => $token]);

//       // Send the password reset email to the user
//       $link = env('APP_URL') . '/reset-password/' . $token;
//      Mail::to($user->mail)->send(new PasswordReset($link));

//       // Return a success message
//       return response()->json(['message' => 'Password reset email sent'], 200);
//   }
}