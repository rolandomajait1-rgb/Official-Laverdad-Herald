<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get email from command line argument or use default
$email = $argv[1] ?? 'rolandommajait.majait@student.laverdad.edu.ph';

$user = \App\Models\User::where('email', $email)->first();

if ($user) {
    if ($user->email_verified_at) {
        echo "✓ User '{$user->email}' is already verified.\n";
        echo "  Verified at: {$user->email_verified_at}\n";
    } else {
        $user->email_verified_at = now();
        $user->save();
        echo "✓ Email verified successfully for: {$user->email}\n";
        echo "  User can now login.\n";
    }
} else {
    echo "✗ User not found: {$email}\n";
    echo "\nAvailable users:\n";
    \App\Models\User::all()->each(function($u) {
        $verified = $u->email_verified_at ? '✓ Verified' : '✗ Not verified';
        echo "  - {$u->email} ({$verified})\n";
    });
}
