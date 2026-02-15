<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== All Users ===\n\n";

$users = App\Models\User::all(['id', 'name', 'email', 'email_verified_at']);

foreach ($users as $user) {
    $verified = $user->email_verified_at ? '✓ Verified' : '✗ Not Verified';
    echo "ID: {$user->id} | {$user->name} | {$user->email} | {$verified}\n";
}

echo "\n=== Student Users ===\n\n";

$studentUsers = App\Models\User::where('email', 'LIKE', '%@student.laverdad.edu.ph')->get(['id', 'name', 'email']);

if ($studentUsers->isEmpty()) {
    echo "No student users found.\n";
} else {
    foreach ($studentUsers as $user) {
        echo "ID: {$user->id} | {$user->name} | {$user->email}\n";
    }
}
