<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Forbidden\n");
}

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Find the most recent student user
$user = App\Models\User::where('email', 'LIKE', '%@student.laverdad.edu.ph')
    ->latest('created_at')
    ->first();

if (! $user) {
    fwrite(STDERR, "No student user found.\n");
    exit(1);
}

fwrite(STDOUT, "User: {$user->name} ({$user->email})\n");
fwrite(STDOUT, "User ID: {$user->id}\n");
fwrite(STDOUT, 'Email verified: '.($user->hasVerifiedEmail() ? 'YES' : 'NO')."\n\n");

// Use token-based verification flow (no manual hash handling)
$token = App\Models\VerificationToken::where('user_id', $user->id)
    ->latest('created_at')
    ->first();

if (! $token) {
    $token = app(App\Services\TokenService::class)->createVerificationToken($user);
}

$verificationUrl = config('app.url').'/api/email/verify-token?token='.$token->token;
$maskedToken = substr($token->token, 0, 8).'...'.substr($token->token, -4);

fwrite(STDOUT, "Token: {$maskedToken}\n");
fwrite(STDOUT, "Expires: {$token->expires_at}\n");
fwrite(STDOUT, "Verification URL:\n{$verificationUrl}\n");
