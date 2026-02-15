<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$userId = 8;
$hash = '866ce86d53a32968a43548be972d5398aaf02521';

echo "Testing verification for user ID: {$userId}\n";
echo "Hash: {$hash}\n\n";

try {
    $user = App\Models\User::findOrFail($userId);
    echo "User found: {$user->name} ({$user->email})\n";
    echo "Email verified: " . ($user->hasVerifiedEmail() ? 'YES' : 'NO') . "\n\n";
    
    $expectedHash = sha1($user->getEmailForVerification());
    echo "Expected hash: {$expectedHash}\n";
    echo "Provided hash: {$hash}\n";
    echo "Hashes match: " . (hash_equals($hash, $expectedHash) ? 'YES' : 'NO') . "\n\n";
    
    if (hash_equals($hash, $expectedHash)) {
        if ($user->hasVerifiedEmail()) {
            echo "User is already verified!\n";
        } else {
            echo "Marking user as verified...\n";
            $user->markEmailAsVerified();
            echo "✓ User verified successfully!\n";
            
            // Check again
            $user->refresh();
            echo "Verification status: " . ($user->hasVerifiedEmail() ? 'VERIFIED' : 'NOT VERIFIED') . "\n";
        }
    } else {
        echo "✗ Hash mismatch - verification failed!\n";
    }
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
