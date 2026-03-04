<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Forbidden\n");
}

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $cloudinaryUrl = config('cloudinary.cloud_url');
    fwrite(STDOUT, 'Cloudinary config URL: '.($cloudinaryUrl ? 'set' : 'missing')."\n");

    $cloudinary = new \Cloudinary\Cloudinary(env('CLOUDINARY_URL'));
    $api = $cloudinary->adminApi();
    $result = $api->ping();

    fwrite(STDOUT, "Cloudinary ping successful.\n");
    fwrite(STDOUT, 'Response: '.json_encode($result)."\n");
} catch (\Throwable $e) {
    error_log('[tmp_test_cloudinary] '.$e->getMessage());
    fwrite(STDERR, "Cloudinary verification failed. Check server logs.\n");
    exit(1);
}
