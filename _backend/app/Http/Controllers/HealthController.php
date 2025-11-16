<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HealthController extends Controller
{
    /**
     * Health check endpoint for monitoring.
     */
    public function check()
    {
        $health = [
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'service' => 'invoice-api',
            'checks' => []
        ];

        // Database check
        try {
            DB::connection()->getPdo();
            $health['checks']['database'] = [
                'status' => 'up',
                'message' => 'Database connection successful'
            ];
        } catch (\Exception $e) {
            $health['status'] = 'unhealthy';
            $health['checks']['database'] = [
                'status' => 'down',
                'message' => 'Database connection failed',
                'error' => $e->getMessage()
            ];
            Log::error('Health check failed: Database connection', ['error' => $e->getMessage()]);
        }

        // Application check
        $health['checks']['application'] = [
            'status' => 'up',
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version()
        ];

        $statusCode = $health['status'] === 'healthy' ? 200 : 503;

        return response()->json($health, $statusCode);
    }
}


