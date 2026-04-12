<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdvancedAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdvancedAnalyticsController extends Controller
{
    protected AdvancedAnalyticsService $analyticsService;

    public function __construct(AdvancedAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * GET /api/v1/admin/analytics/enrollment-trends
     * Get enrollment trends over time.
     */
    public function enrollmentTrends(Request $request): JsonResponse
    {
        $months = $request->integer('months', 12);
        $trends = $this->analyticsService->getEnrollmentTrends($months);

        return response()->json($trends);
    }

    /**
     * GET /api/v1/admin/analytics/student-performance/{studentId}
     * Predict student performance.
     */
    public function predictStudentPerformance(Request $request, int $studentId): JsonResponse
    {
        $prediction = $this->analyticsService->predictStudentPerformance($studentId);

        return response()->json($prediction);
    }

    /**
     * GET /api/v1/admin/analytics/course-demand
     * Get course demand analysis.
     */
    public function courseDemand(): JsonResponse
    {
        $demand = $this->analyticsService->getCourseDemandAnalysis();

        return response()->json($demand);
    }

    /**
     * GET /api/v1/admin/analytics/professor-workload
     * Get professor workload analysis.
     */
    public function professorWorkload(): JsonResponse
    {
        $workload = $this->analyticsService->getProfessorWorkload();

        return response()->json($workload);
    }

    /**
     * GET /api/v1/admin/analytics/attendance
     * Get attendance analytics.
     */
    public function attendanceAnalytics(): JsonResponse
    {
        $analytics = $this->analyticsService->getAttendanceAnalytics();

        return response()->json($analytics);
    }
}
