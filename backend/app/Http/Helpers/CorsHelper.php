<?php

namespace App\Http\Helpers;

class CorsHelper
{
    public static function addHeaders($response)
    {
        $allowedOrigins = [
            'https://laverdad.edu.ph',
            'http://localhost:5173'
        ];
        
        $origin = request()->header('Origin');
        $allowOrigin = in_array($origin, $allowedOrigins) ? $origin : 'https://laverdad.edu.ph';
        
        return $response
            ->header('Access-Control-Allow-Origin', $allowOrigin)
            ->header('Access-Control-Allow-Credentials', 'true')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
    }
}
