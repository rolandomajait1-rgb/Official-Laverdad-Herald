<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $cloudinaryUrl = config('cloudinary.cloud_url');
    echo "Cloudinary Config URL: " . ($cloudinaryUrl ? 'Set' : 'Missing') . "\n";
    
    // Quick ping to cloudinary sdk
    $cloudinary = new \Cloudinary\Cloudinary(env('CLOUDINARY_URL'));
    $api = $cloudinary->adminApi();
    $result = $api->ping();
    echo "Cloudinary Ping: " . json_encode($result) . "\n";
} catch (\Exception $e) {
    echo "Cloudinary verification failed: " . $e->getMessage() . "\n";
}
