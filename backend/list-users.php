<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "═══════════════════════════════════════════════════════════════\n";
echo "  ALL USERS IN DATABASE\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$users = \App\Models\User::all();

if ($users->isEmpty()) {
    echo "No users found in database.\n";
} else {
    foreach ($users as $user) {
        $verified = $user->email_verified_at ? '✓ VERIFIED' : '✗ NOT VERIFIED';
        $verifiedDate = $user->email_verified_at ? " (on {$user->email_verified_at})" : '';

        echo "Email: {$user->email}\n";
        echo "Name:  {$user->name}\n";
        echo "Role:  {$user->role}\n";
        echo "Status: {$verified}{$verifiedDate}\n";
        echo "───────────────────────────────────────────────────────────────\n";
    }

    echo "\nTotal users: ".$users->count()."\n";
    echo 'Verified: '.$users->whereNotNull('email_verified_at')->count()."\n";
    echo 'Unverified: '.$users->whereNull('email_verified_at')->count()."\n";
}

echo "\n═══════════════════════════════════════════════════════════════\n";
