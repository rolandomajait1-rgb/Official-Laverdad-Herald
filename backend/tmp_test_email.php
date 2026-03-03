<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    \Illuminate\Support\Facades\Mail::raw('This is a test email to verify Brevo SMTP configuration.', function ($message) {
        $message->to('rolandomajait1@gmail.com')
                ->subject('Test Email Delivery');
    });
    echo "Email sent successfully.\n";
} catch (\Exception $e) {
    echo "Email sending failed: " . $e->getMessage() . "\n";
}
