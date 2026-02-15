<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$email = $argv[1] ?? 'rolandommajait.majait@student.laverdad.edu.ph';

$user = App\Models\User::where('email', $email)->first();

if (!$user) {
    echo "✗ User not found: {$email}\n";
    exit(1);
}

echo "Deleting user: {$user->name} ({$user->email})\n";
$user->delete();
echo "✓ User deleted successfully!\n";
