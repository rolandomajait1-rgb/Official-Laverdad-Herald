<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Find the most recent student user
$user = App\Models\User::where('email', 'LIKE', '%@student.laverdad.edu.ph')
    ->orderBy('created_at', 'desc')
    ->first();

if (!$user) {
    echo "No student user found.\n";
    exit;
}

echo "User: {$user->name} ({$user->email})\n";
echo "User ID: {$user->id}\n";
echo "Email for verification: {$user->getEmailForVerification()}\n\n";

// Generate the hash
$hash = sha1($user->getEmailForVerification());
echo "Generated Hash: {$hash}\n\n";

// Generate the verification URL
$verificationUrl = config('app.url') . '/api/email/verify/' . $user->id . '/' . $hash;
echo "Verification URL:\n{$verificationUrl}\n\n";

// Test if the hash would match
$testHash = sha1($user->email);
echo "Test Hash (from email): {$testHash}\n";
echo "Hashes match: " . ($hash === $testHash ? 'YES' : 'NO') . "\n\n";

// Show what the backend would receive
echo "Backend would check:\n";
echo "  User email: {$user->email}\n";
echo "  sha1(email): " . sha1($user->email) . "\n";
echo "  URL hash: {$hash}\n";
echo "  Match: " . (hash_equals($hash, sha1($user->email)) ? 'YES' : 'NO') . "\n";
