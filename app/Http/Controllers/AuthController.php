<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password as FacadesPassword;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function authenticate(Request $request) {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 422);
        }

        $request->session()->regenerate();

        return response()->json([
            'message' => 'Logged in successfully',
            'user' => Auth::user(),
        ]);
    }

    public function register(Request $request) {
        $userInfo = $request->validate([
            'name' => ['required', 'unique:users,name'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $userInfo['name'],
            'email' => $userInfo['email'],
            'password' => Hash::make($userInfo['password']),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Registered successfully',
            'user' => Auth::user(),
        ]);
    }

    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    public function resetPasswordLink(Request $request) {
        $validated = $request->validate(['email' => ['email', 'required']]);

        $token = Str::random(32);
        $hashedToken = Hash::make($token);

        $user = User::where('email', $validated['email'])->first();

        if ($user) {
            DB::table('password_resets')->updateOrInsert(
                ['email' => $user->email],
                [
                    'token' => $hashedToken,
                    'created_at' => now()
                ]
            );
            Mail::to($user->email)->send(new ResetPasswordMail($user->email, $token));
        }

        return response()->json([
            'message' => 'Reset email sent.'
        ]);
    }

    public function resetPassword(Request $request) {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required'],
            'password' => ['required', Password::defaults()]
        ]);

        $user = User::where('email', $validated['email'])->first();

        $row = DB::table('password_resets')
            ->select('token', 'created_at')
            ->where('email', $validated['email'])
            ->first();

        if (!$row || !$user)
            return response()->json([
                'message' => 'Invalid token'
            ], 422);

        //To do: change 60 to an env val
        if (now()->diffInMinutes($row->created_at) > 60) {
            DB::table('password_resets')->where('email', $validated['email'])->delete();
            return response()->json([
                'message' => 'Invalid token'
            ], 422);
        }

        if (!Hash::check($validated['token'], $row->token))
            return response()->json([
                'message' => 'Invalid token'
            ], 422);

        $user->password = Hash::make($validated['password']);
        $user->save();

        DB::table('password_resets')->where('email', $validated['email'])->delete();

        return response()->json([
            'message' => 'Password reset! Please log in again.'
        ], 200);
    }
}
