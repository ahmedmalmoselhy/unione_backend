<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\QueueMonitorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QueueHealthController extends Controller
{
    protected QueueMonitorService $monitor;

    public function __construct(QueueMonitorService $monitor)
    {
        $this->monitor = $monitor;
    }

    /**
     * GET /api/v1/admin/queue/health
     * Get queue system health status.
     */
    public function health(Request $request): JsonResponse
    {
        $stats = $this->monitor->getStats();
        $isHealthy = $this->monitor->isHealthy();
        $oldestJobAge = $this->monitor->getOldestJobAge();

        return response()->json([
            'status' => $isHealthy ? 'healthy' : 'degraded',
            'queue_stats' => $stats,
            'oldest_job_age_seconds' => $oldestJobAge,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * GET /api/v1/admin/queue/failed
     * Get recent failed jobs.
     */
    public function failedJobs(Request $request): JsonResponse
    {
        $limit = $request->integer('limit', 50);
        $failedJobs = $this->monitor->getRecentFailedJobs($limit);

        return response()->json([
            'failed_jobs' => $failedJobs,
            'total' => count($failedJobs),
        ]);
    }

    /**
     * DELETE /api/v1/admin/queue/failed/clear
     * Clear old failed jobs.
     */
    public function clearFailedJobs(Request $request): JsonResponse
    {
        $hours = $request->integer('older_than_hours', 72);
        $deleted = $this->monitor->clearOldFailedJobs($hours);

        return response()->json([
            'message' => "Cleared {$deleted} failed jobs older than {$hours} hours.",
            'deleted_count' => $deleted,
        ]);
    }
}
