<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Forbidden\n");
}

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\VerificationToken;
use App\Services\AuthService;
use App\Services\MailService;
use App\Services\TokenService;

$email = $argv[1] ?? getenv('TEST_VERIFY_EMAIL');

if (! $email) {
    fwrite(STDERR, "Usage: php test-verification.php <email>\n");
    exit(1);
}

fwrite(STDOUT, "=== Email Verification Test ===\n\n");
fwrite(STDOUT, "Target email: {$email}\n\n");

try {
    $user = User::where('email', $email)->first();

    if (! $user) {
        fwrite(STDERR, "User not found. Create the user first via register endpoint.\n");
        exit(1);
    }

    fwrite(STDOUT, "User found: {$user->email}\n");
    fwrite(STDOUT, "Name: {$user->name}\n");
    fwrite(STDOUT, 'Email verified: '.($user->email_verified_at ? 'Yes' : 'No')."\n");
    fwrite(STDOUT, "Role: {$user->role}\n\n");

    if ($user->hasVerifiedEmail()) {
        fwrite(STDOUT, "Email is already verified.\n");
        exit(0);
    }

    $tokenService = new TokenService();
    $mailService = new MailService();
    $authService = new AuthService($tokenService, $mailService);

    fwrite(STDOUT, "Sending verification email...\n");
    $result = $authService->resendVerification($email);
    fwrite(STDOUT, "Result: {$result['message']}\n");

    $token = VerificationToken::where('user_id', $user->id)
        ->latest('created_at')
        ->first();

    if (! $token) {
        fwrite(STDERR, "No verification token found.\n");
        exit(1);
    }

    $verificationUrl = config('app.url').'/api/email/verify-token?token='.$token->token;
    $maskedToken = substr($token->token, 0, 8).'...'.substr($token->token, -4);

    fwrite(STDOUT, "Verification token created: {$maskedToken}\n");
    fwrite(STDOUT, "Expires: {$token->expires_at}\n");
    fwrite(STDOUT, "Verification URL: {$verificationUrl}\n");
    fwrite(STDOUT, 'Frontend redirect (on success): '.config('app.frontend_url')."/login?verified=1\n");
} catch (\Throwable $e) {
    error_log('[test-verification] '.$e->getMessage());
    fwrite(STDERR, "Verification test failed. Check server logs.\n");
    exit(1);
}
