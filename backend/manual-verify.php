<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Forbidden\n");
}

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$identifier = $argv[1] ?? null;

if (! $identifier) {
    fwrite(STDERR, "Usage: php manual-verify.php <user-id|email>\n");
    exit(1);
}

try {
    $query = App\Models\User::query();
    $user = ctype_digit((string) $identifier)
        ? $query->find((int) $identifier)
        : $query->where('email', $identifier)->first();

    if (! $user) {
        fwrite(STDERR, "User not found.\n");
        exit(1);
    }

    fwrite(STDOUT, "User: {$user->name} ({$user->email})\n");

    if ($user->hasVerifiedEmail()) {
        fwrite(STDOUT, "Status: already verified.\n");
        exit(0);
    }

    $user->markEmailAsVerified();
    $user->refresh();

    fwrite(STDOUT, 'Status: '.($user->hasVerifiedEmail() ? 'verified' : 'verification failed')."\n");
} catch (\Throwable $e) {
    error_log('[manual-verify] '.$e->getMessage());
    fwrite(STDERR, "Verification failed. Check server logs.\n");
    exit(1);
}
