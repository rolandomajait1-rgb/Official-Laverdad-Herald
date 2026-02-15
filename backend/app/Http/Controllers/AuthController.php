<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Staff;
use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email|ends_with:@student.laverdad.edu.ph',
            'password' => 'required',
        ], [
            'email.ends_with' => 'Use a laverdad email addresses to access this system.'
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function loginApi(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            Hash::check($request->password, '$2y$12$dummyhashtopreventtimingattack');
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if (is_null($user->email_verified_at)) {
            return response()->json([
                'message' => 'Please verify your email before logging in. Check your inbox for verification link.',
                'requires_verification' => true
            ], 403);
        }
        
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['token' => $token, 'role' => $user->role, 'user' => $user]);
    }


    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users|ends_with:@student.laverdad.edu.ph',
            'password' => 'required|string|min:8|confirmed|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/',
        ], [
            'email.ends_with' => 'Only @student.laverdad.edu.ph email addresses are allowed to register.'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
        ]);


        Auth::login($user);

        return redirect('/dashboard')->with('success', 'Registration successful. Welcome!');
    }

    public function registerApi(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
        ]);

        $token = bin2hex(random_bytes(32));
        \DB::table('verification_tokens')->insert([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => now()->addHours(24),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $verificationUrl = config('app.url') . '/api/email/verify-token?token=' . $token;
        \Mail::send('emails.verify-email', ['user' => $user, 'verificationUrl' => $verificationUrl], function ($message) use ($user) {
            $message->to($user->email)->subject('Verify Your Email Address');
        });

        return response()->json([
            'message' => 'Registration successful! Please check your email to verify your account before logging in.',
            'user_id' => $user->id
        ], 201);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function logoutApi(Request $request)
    {
        $request->user()->tokens()->where('id', $request->user()->currentAccessToken()->id)->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function changePasswordApi(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 400);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(['message' => 'Password changed successfully']);
    }

    public function deleteAccountApi(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Password is incorrect'], 400);
        }

        $user->tokens()->delete();
        \App\Models\ArticleInteraction::where('user_id', $user->id)->delete();

        $author = \App\Models\Author::where('user_id', $user->id)->first();
        if ($author) {
            \App\Models\Article::where('author_id', $author->id)->delete();
        }

        $user->delete();

        return response()->json(['message' => 'Account deleted successfully']);
    }

    public function verifyEmailToken(Request $request)
    {
        $token = $request->query('token');
        
        if (!$token) {
            return redirect(config('app.frontend_url') . '/login?error=invalid_token');
        }

        $verification = \DB::table('verification_tokens')
            ->where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (!$verification) {
            return redirect(config('app.frontend_url') . '/login?error=invalid_or_expired_token');
        }

        $user = User::find($verification->user_id);
        
        if (!$user) {
            return redirect(config('app.frontend_url') . '/login?error=user_not_found');
        }

        if ($user->hasVerifiedEmail()) {
            \DB::table('verification_tokens')->where('token', $token)->delete();
            return redirect(config('app.frontend_url') . '/login?verified=1&message=already_verified');
        }

        $user->markEmailAsVerified();
        \DB::table('verification_tokens')->where('user_id', $user->id)->delete();

        return redirect(config('app.frontend_url') . '/login?verified=1');
    }

    public function resendVerificationEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        
        $user = User::where('email', $request->email)->first();

        if ($user && !$user->hasVerifiedEmail()) {
            \DB::table('verification_tokens')->where('user_id', $user->id)->delete();
            
            $token = bin2hex(random_bytes(32));
            \DB::table('verification_tokens')->insert([
                'user_id' => $user->id,
                'token' => $token,
                'expires_at' => now()->addHours(24),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $verificationUrl = config('app.url') . '/api/email/verify-token?token=' . $token;
            \Mail::send('emails.verify-email', ['user' => $user, 'verificationUrl' => $verificationUrl], function ($message) use ($user) {
                $message->to($user->email)->subject('Verify Your Email Address');
            });
        }

        return response()->json([
            'message' => 'If an unverified account exists for that email, a verification email has been sent.'
        ], 200);
    }

    public function forgotPasswordApi(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'If an account exists for that email, a password reset link has been sent.'
            ], 200);
        }

        $token = \Illuminate\Support\Str::random(64);

        \DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'email' => $user->email,
                'token' => Hash::make($token),
                'created_at' => now()
            ]
        );

        $resetUrl = config('app.frontend_url') . '/reset-password?token=' . $token . '&email=' . urlencode($user->email);

        try {
            \Mail::send('emails.reset-password', ['user' => $user, 'resetUrl' => $resetUrl], function ($message) use ($user) {
                $message->to($user->email)->subject('Reset Your Password');
            });
        } catch (\Exception $e) {
            \Log::error('Password reset email failed: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'If an account exists for that email, a password reset link has been sent.'
        ], 200);
    }

    public function resetPasswordApi(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/',
        ]);

        $resetRecord = \DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord) {
            return response()->json(['message' => 'Invalid or expired reset token'], 400);
        }

        if (!Hash::check($request->token, $resetRecord->token)) {
            return response()->json(['message' => 'Invalid or expired reset token'], 400);
        }

        if (now()->diffInHours($resetRecord->created_at) > 24) {
            \DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json(['message' => 'Reset token has expired'], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        \DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        $user->tokens()->delete();

        return response()->json(['message' => 'Password reset successfully! You can now log in with your new password.'], 200);
    }

}