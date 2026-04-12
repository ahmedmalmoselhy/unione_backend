<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class QueueMonitorService
{
    /**
     * Get queue job statistics.
     */
    public function getStats(): array
    {
        return [
            'pending' => $this->getPendingJobsCount(),
            'processing' => $this->getProcessingJobsCount(),
            'failed' => $this->getFailedJobsCount(),
            'queues' => $this->getQueueBreakdown(),
        ];
    }

    /**
     * Get count of pending jobs.
     */
    public function getPendingJobsCount(): int
    {
        return DB::table('jobs')->count();
    }

    /**
     * Get count of currently processing jobs.
     */
    public function getProcessingJobsCount(): int
    {
        return DB::table('jobs')
            ->where('reserved_at', '!=', null)
            ->count();
    }

    /**
     * Get count of failed jobs.
     */
    public function getFailedJobsCount(): int
    {
        return DB::table('failed_jobs')
            ->where('failed_at', '>=', now()->subHours(24))
            ->count();
    }

    /**
     * Get breakdown of jobs by queue name.
     */
    public function getQueueBreakdown(): array
    {
        return DB::table('jobs')
            ->select('queue', DB::raw('count(*) as count'))
            ->groupBy('queue')
            ->pluck('count', 'queue')
            ->toArray();
    }

    /**
     * Get oldest pending job age in seconds.
     */
    public function getOldestJobAge(): ?int
    {
        $oldest = DB::table('jobs')
            ->whereNull('reserved_at')
            ->orderBy('available_at', 'asc')
            ->first();

        if (!$oldest) {
            return null;
        }

        return now()->timestamp - $oldest->available_at;
    }

    /**
     * Check if queue health is acceptable.
     */
    public function isHealthy(): bool
    {
        $failedCount = $this->getFailedJobsCount();
        $pendingCount = $this->getPendingJobsCount();
        $oldestJobAge = $this->getOldestJobAge();

        // Unhealthy if more than 100 failed jobs in last 24 hours
        if ($failedCount > 100) {
            return false;
        }

        // Unhealthy if more than 10,000 pending jobs
        if ($pendingCount > 10000) {
            return false;
        }

        // Unhealthy if oldest job is older than 1 hour
        if ($oldestJobAge !== null && $oldestJobAge > 3600) {
            return false;
        }

        return true;
    }

    /**
     * Clear failed jobs older than specified hours.
     */
    public function clearOldFailedJobs(int $hours = 72): int
    {
        return DB::table('failed_jobs')
            ->where('failed_at', '<', now()->subHours($hours))
            ->delete();
    }

    /**
     * Get recent failed jobs with details.
     */
    public function getRecentFailedJobs(int $limit = 50): array
    {
        return DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($job) {
                return [
                    'id' => $job->id,
                    'uuid' => $job->uuid,
                    'queue' => $job->queue,
                    'exception' => substr($job->exception, 0, 500), // Truncate for safety
                    'failed_at' => $job->failed_at,
                ];
            })
            ->toArray();
    }
}
