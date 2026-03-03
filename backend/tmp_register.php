<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$authService = app(App\Services\AuthService::class);

// Find existing?
$existing = \App\Models\User::where('email', 'rolandomajait1@gmail.com')->first();
if ($existing) {
    if (!$existing->hasVerifiedEmail()) {
         $authService->resendVerification($existing->email);
         echo "Resent verification to {$existing->email}\n";
    } else {
         echo "Already verified.\n";
    }
} else {
    $user = $authService->createUserWithVerification([
        'name' => 'Tester',
        'email' => 'rolandomajait1@gmail.com', 
        'password' => 'password123'
    ]);
    echo "Created user and sent verification.\n";
}
