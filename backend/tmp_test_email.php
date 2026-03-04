<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Forbidden\n");
}

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$recipient = $argv[1] ?? getenv('TEST_EMAIL_RECIPIENT');

if (! $recipient) {
    fwrite(STDERR, "Usage: php tmp_test_email.php <recipient-email>\n");
    exit(1);
}

try {
    \Illuminate\Support\Facades\Mail::raw(
        'This is a test email to verify SMTP configuration.',
        function ($message) use ($recipient) {
            $message->to($recipient)->subject('Test Email Delivery');
        }
    );
    fwrite(STDOUT, "Email send request completed.\n");
} catch (\Throwable $e) {
    error_log('[tmp_test_email] '.$e->getMessage());
    fwrite(STDERR, "Email sending failed. Check server logs.\n");
    exit(1);
}
