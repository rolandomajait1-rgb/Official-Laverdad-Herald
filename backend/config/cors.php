<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines which domains are allowed to access your
    | application's resources from a different domain. You may also
    | configure which HTTP methods and headers are allowed.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_origins' => [
        'http://localhost:5173',
        'https://official-laverdad-herald.vercel.app',
        'https://official-laverdad-herald-j84kxacox-rolando-majaits-projects.vercel.app',
        env('FRONTEND_URL', 'http://localhost:5173'),
    ],

    // Allow preview/deployed Vercel subdomains via pattern
    'allowed_origins_patterns' => [
        '^https?:\/\/.*\.vercel\.app$',
    ],

    'allowed_methods' => ['*'],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];