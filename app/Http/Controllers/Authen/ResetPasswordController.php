<?php

namespace App\Http\Controllers\Authen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use App\Models\User;

class ResetPasswordController extends Controller
{
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $code = rand(100000, 999999); // Generate a random 6-digit code
            $user->password_reset_code = $code;
            $user->save();

            // Send the code to the user via email or SMS
            // You can use a package like Laravel Mail or a third-party SMS service
            // Example code for sending an email using Laravel's Mail facade:
            Mail::to($user->email)->send(new ResetPasswordCodeMail($code));

            return response()->json(['message' => 'Reset password code sent to your email'], 200);
        } else {
            return response()->json(['error' => 'User not found'], 404);
        }
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required',
            'password' => 'required|min:6',
        ]);

        $user = User::where('email', $request->email)
                    ->where('password_reset_code', $request->code)
                    ->first();

        if ($user) {
            $user->password_reset_code = null;
            $user->password = Hash::make($request->password);
            $user->save();

            return response()->json(['message' => 'Password reset successful'], 200);
        } else {
            return response()->json(['error' => 'Invalid code or email'], 400);
        }
    }
}
