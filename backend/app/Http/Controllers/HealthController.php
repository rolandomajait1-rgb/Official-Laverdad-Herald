<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    /**
     * Health check endpoint for uptime monitoring
     * This prevents Render free tier from spinning down
     */
    public function check()
    {
        try {
            // Check database connection
            DB::connection()->getPdo();
            
            return response()->json([
                'status' => 'healthy',
                'timestamp' => now()->toIso8601String(),
                'service' => 'Official La Verdad Herald API',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'unhealthy',
                'error' => 'Database connection failed',
                'timestamp' => now()->toIso8601String(),
            ], 503);
        }
    }
}
