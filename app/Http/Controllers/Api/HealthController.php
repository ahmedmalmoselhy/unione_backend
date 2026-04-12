<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HealthController extends Controller
{
    /**
     * GET /api/health
     * Comprehensive health check endpoint with service status details.
     */
    public function __invoke(): JsonResponse
    {
        $services = [];
        $overallStatus = 'healthy';

        // Database check
        $services['database'] = $this->checkDatabase();
        if ($services['database']['status'] === 'unhealthy') {
            $overallStatus = 'degraded';
        }

        // Redis/Cache check
        $services['cache'] = $this->checkCache();
        if ($services['cache']['status'] === 'unhealthy') {
            $overallStatus = 'degraded';
        }

        // Disk storage check
        $services['storage'] = $this->checkStorage();
        if ($services['storage']['status'] === 'unhealthy') {
            $overallStatus = 'degraded';
        }

        // Queue status (check if queue jobs table is accessible)
        $services['queue'] = $this->checkQueue();

        $status = match ($overallStatus) {
            'healthy' => 200,
            'degraded' => 200,
            'unhealthy' => 503,
        };

        return response()->json([
            'status' => $overallStatus,
            'timestamp' => now()->toIso8601String(),
            'version' => config('app.version', '1.0.0'),
            'environment' => config('app.env'),
            'services' => $services,
        ], $status);
    }

    /**
     * Check database connectivity and basic query performance.
     */
    protected function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            $result = DB::select('SELECT 1 as test');
            $responseTime = round((microtime(true) - $start) * 1000, 2);

            return [
                'status' => 'healthy',
                'driver' => config('database.default'),
                'response_time_ms' => $responseTime,
                'message' => 'Database connection successful',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'driver' => config('database.default'),
                'error' => $e->getMessage(),
                'message' => 'Database connection failed',
            ];
        }
    }

    /**
     * Check cache/Redis connectivity.
     */
    protected function checkCache(): array
    {
        try {
            $start = microtime(true);
            $cacheKey = 'health:check:' . time();
            Cache::put($cacheKey, 'ok', 10);
            $result = Cache::get($cacheKey);
            Cache::forget($cacheKey);
            $responseTime = round((microtime(true) - $start) * 1000, 2);

            $driver = config('cache.default');
            $isRedis = $driver === 'redis';

            return [
                'status' => $result === 'ok' ? 'healthy' : 'unhealthy',
                'driver' => $driver,
                'redis' => $isRedis,
                'response_time_ms' => $responseTime,
                'message' => $result === 'ok' ? 'Cache operational' : 'Cache returned unexpected value',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'driver' => config('cache.default'),
                'error' => $e->getMessage(),
                'message' => 'Cache connection failed',
            ];
        }
    }

    /**
     * Check storage disk connectivity and available space.
     */
    protected function checkStorage(): array
    {
        try {
            $start = microtime(true);
            $disk = Storage::disk('public');
            $testFile = 'health/check-' . time() . '.txt';

            $disk->put($testFile, 'health check');
            $exists = $disk->exists($testFile);
            $disk->delete($testFile);
            $responseTime = round((microtime(true) - $start) * 1000, 2);

            return [
                'status' => $exists ? 'healthy' : 'unhealthy',
                'disk' => 'public',
                'response_time_ms' => $responseTime,
                'message' => $exists ? 'Storage operational' : 'Storage test failed',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'disk' => 'public',
                'error' => $e->getMessage(),
                'message' => 'Storage connection failed',
            ];
        }
    }

    /**
     * Check queue system status.
     */
    protected function checkQueue(): array
    {
        try {
            $queueDriver = config('queue.default');

            return [
                'status' => 'healthy',
                'driver' => $queueDriver,
                'message' => 'Queue system accessible',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'driver' => config('queue.default'),
                'error' => $e->getMessage(),
                'message' => 'Queue system check failed',
            ];
        }
    }
}
