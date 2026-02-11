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
            'email' => 'required|email|ends_with:@laverdad.edu.ph',
            'password' => 'required',
        ], [
            'email.ends_with' => 'Only @laverdad.edu.ph email addresses can access this system.'
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
            'email' => 'required|email|ends_with:@laverdad.edu.ph',
            'password' => 'required',
        ], [
            'email.ends_with' => 'Only @laverdad.edu.ph email addresses can access this system.'
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();
            
            if (is_null($user->email_verified_at)) {
                Auth::logout();
                return response()->json([
                    'message' => 'Please verify your email before logging in. Check your inbox for verification link.',
                    'requires_verification' => true
                ], 403);
            }
            
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json(['token' => $token, 'role' => $user->role, 'user' => $user]);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }


    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users|ends_with:@laverdad.edu.ph',
            'password' => 'required|string|min:8|confirmed|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/',
        ], [
            'email.ends_with' => 'Only @laverdad.edu.ph email addresses are allowed to register.'
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
            'email' => 'required|email|unique:users|ends_with:@laverdad.edu.ph',
            'password' => 'required|string|min:8|confirmed|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/',
        ], [
            'email.ends_with' => 'Only @laverdad.edu.ph email addresses are allowed to register.'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
        ]);

        $user->sendEmailVerificationNotification();

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

    public function verifyEmail(Request $request)
    {
        $user = User::find($request->route('id'));

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified'], 200);
        }

        if ($user->markEmailAsVerified()) {
            return response()->json(['message' => 'Email verified successfully! You can now log in.'], 200);
        }

        return response()->json(['message' => 'Invalid verification link'], 400);
    }

    public function resendVerificationEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        
        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified'], 200);
        }
        
        $user->sendEmailVerificationNotification();
        
        return response()->json(['message' => 'Verification email sent! Please check your inbox.'], 200);
    }
}
