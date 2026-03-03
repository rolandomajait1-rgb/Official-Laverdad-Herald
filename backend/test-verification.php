<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Services\AuthService;
use App\Services\TokenService;
use App\Services\MailService;

echo "=== Email Verification Test ===\n\n";

// Check if user exists
$email = 'rolandomajait1@gmail.com';
$user = User::where('email', $email)->first();

if (!$user) {
    echo "❌ User not found: {$email}\n";
    echo "Creating test user...\n";
    
    $tokenService = new TokenService();
    $mailService = new MailService();
    $authService = new AuthService($tokenService, $mailService);
    
    try {
        $user = $authService->createUserWithVerification([
            'name' => 'Test User',
            'email' => $email,
            'password' => 'password123',
        ]);
        echo "✅ User created successfully\n";
    } catch (\Exception $e) {
        echo "❌ Failed to create user: " . $e->getMessage() . "\n";
        exit(1);
    }
} else {
    echo "✅ User found: {$user->email}\n";
    echo "   Name: {$user->name}\n";
    echo "   Email verified: " . ($user->email_verified_at ? 'Yes (' . $user->email_verified_at . ')' : 'No') . "\n";
    echo "   Role: {$user->role}\n\n";
}

// Test resending verification email
if (!$user->hasVerifiedEmail()) {
    echo "Sending verification email...\n";
    
    $tokenService = new TokenService();
    $mailService = new MailService();
    $authService = new AuthService($tokenService, $mailService);
    
    $result = $authService->resendVerification($email);
    echo "Result: {$result['message']}\n\n";
    
    // Get the token
    $token = \App\Models\VerificationToken::where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->first();
    
    if ($token) {
        echo "✅ Verification token created\n";
        echo "   Token: {$token->token}\n";
        echo "   Expires: {$token->expires_at}\n";
        
        $verificationUrl = config('app.url') . '/api/email/verify-token?token=' . $token->token;
        echo "\n📧 Verification URL:\n{$verificationUrl}\n\n";
        
        echo "Frontend redirect URL:\n";
        echo config('app.frontend_url') . '/login?verified=1' . "\n";
    } else {
        echo "❌ No verification token found\n";
    }
} else {
    echo "✅ Email already verified\n";
}
