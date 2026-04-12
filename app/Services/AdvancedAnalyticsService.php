<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Student;
use App\Models\AttendanceRecord;
use App\Models\Professor;
use Illuminate\Support\Facades\DB;

class AdvancedAnalyticsService
{
    /**
     * Get enrollment trends over time.
     */
    public function getEnrollmentTrends(int $months = 12): array
    {
        $trends = Enrollment::selectRaw(
            "DATE_TRUNC('month', registered_at) as month, 
             COUNT(*) as total_enrollments,
             COUNT(CASE WHEN status = 'registered' THEN 1 END) as active,
             COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
             COUNT(CASE WHEN status = 'dropped' THEN 1 END) as dropped"
        )
            ->where('registered_at', '>=', now()->subMonths($months))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return [
            'period' => "{$months} months",
            'data' => $trends->map(fn ($row) => [
                'month' => $row->month,
                'total_enrollments' => (int) $row->total_enrollments,
                'active' => (int) $row->active,
                'completed' => (int) $row->completed,
                'dropped' => (int) $row->dropped,
                'drop_rate' => $row->total_enrollments > 0
                    ? round(($row->dropped / $row->total_enrollments) * 100, 2)
                    : 0,
            ]),
        ];
    }

    /**
     * Predict student performance based on historical data.
     */
    public function predictStudentPerformance(int $studentId): array
    {
        $student = Student::findOrFail($studentId);

        $grades = Grade::whereIn('enrollment_id', function ($query) use ($studentId) {
            $query->select('id')
                ->from('enrollments')
                ->where('student_id', $studentId)
                ->where('status', 'completed');
        })->get();

        if ($grades->isEmpty()) {
            return [
                'prediction' => 'insufficient_data',
                'confidence' => 0,
                'current_gpa' => $student->gpa,
            ];
        }

        $averageGrade = $grades->avg('total');
        $trend = $this->calculateGradeTrend($grades);

        $predictedGrade = match (true) {
            $averageGrade >= 90 => 'A',
            $averageGrade >= 80 => 'B',
            $averageGrade >= 70 => 'C',
            $averageGrade >= 60 => 'D',
            default => 'F',
        };

        $confidence = min(count($grades) * 10, 95); // More grades = higher confidence

        return [
            'prediction' => $predictedGrade,
            'confidence' => $confidence,
            'current_gpa' => $student->gpa,
            'average_score' => round($averageGrade, 2),
            'trend' => $trend,
            'risk_level' => $averageGrade < 60 ? 'high' : ($averageGrade < 70 ? 'medium' : 'low'),
        ];
    }

    /**
     * Get course demand analysis.
     */
    public function getCourseDemandAnalysis(): array
    {
        $courseDemand = DB::table('enrollments')
            ->join('sections', 'enrollments.section_id', '=', 'sections.id')
            ->join('courses', 'sections.course_id', '=', 'courses.id')
            ->selectRaw(
                "courses.id as course_id,
                 courses.code as course_code,
                 courses.name as course_name,
                 COUNT(enrollments.id) as total_enrollments,
                 COUNT(CASE WHEN enrollments.status = 'registered' THEN 1 END) as current_enrollments,
                 sections.capacity,
                 ROUND(COUNT(enrollments.id) * 100.0 / NULLIF(SUM(sections.capacity), 0), 2) as fill_rate"
            )
            ->groupBy('courses.id', 'courses.code', 'courses.name', 'sections.capacity')
            ->orderByDesc('current_enrollments')
            ->limit(20)
            ->get();

        return [
            'top_courses' => $courseDemand->map(fn ($course) => [
                'course_id' => $course->course_id,
                'code' => $course->course_code,
                'name' => $course->course_name,
                'current_enrollments' => (int) $course->current_enrollments,
                'capacity' => (int) $course->capacity,
                'fill_rate' => (float) $course->fill_rate,
                'demand_level' => $course->fill_rate > 90 ? 'high' : ($course->fill_rate > 70 ? 'medium' : 'low'),
            ]),
        ];
    }

    /**
     * Get professor workload analysis.
     */
    public function getProfessorWorkload(): array
    {
        $workload = DB::table('sections')
            ->join('professors', 'sections.professor_id', '=', 'professors.id')
            ->join('users', 'professors.user_id', '=', 'users.id')
            ->selectRaw(
                "professors.id as professor_id,
                 users.first_name || ' ' || users.last_name as professor_name,
                 professors.academic_rank,
                 COUNT(sections.id) as total_sections,
                 COUNT(CASE WHEN sections.is_active = true THEN 1 END) as active_sections,
                 COALESCE(SUM(jsonb_array_length(sections.schedule)), 0) as total_hours_per_week"
            )
            ->groupBy(
                'professors.id',
                'users.first_name',
                'users.last_name',
                'professors.academic_rank'
            )
            ->orderByDesc('active_sections')
            ->get();

        return [
            'professors' => $workload->map(fn ($w) => [
                'professor_id' => $w->professor_id,
                'name' => $w->professor_name,
                'rank' => $w->academic_rank,
                'total_sections' => (int) $w->total_sections,
                'active_sections' => (int) $w->active_sections,
                'hours_per_week' => (int) $w->total_hours_per_week,
                'workload_level' => $w->active_sections > 5 ? 'heavy' : ($w->active_sections > 3 ? 'moderate' : 'light'),
            ]),
            'summary' => [
                'average_sections' => round($workload->avg('active_sections'), 2),
                'average_hours' => round($workload->avg('total_hours_per_week'), 2),
                'total_professors' => $workload->count(),
            ],
        ];
    }

    /**
     * Calculate grade trend (improving, stable, declining).
     */
    protected function calculateGradeTrend($grades): string
    {
        if ($grades->count() < 3) {
            return 'insufficient_data';
        }

        $sorted = $grades->sortBy('graded_at')->values();
        $recent = $sorted->slice(-3)->avg('total');
        $older = $sorted->slice(0, 3)->avg('total');

        $diff = $recent - $older;

        return match (true) {
            $diff > 5 => 'improving',
            $diff < -5 => 'declining',
            default => 'stable',
        };
    }

    /**
     * Get attendance analytics.
     */
    public function getAttendanceAnalytics(): array
    {
        $stats = AttendanceRecord::selectRaw(
            "status,
             COUNT(*) as count,
             ROUND(COUNT(*) * 100.0 / SUM(COUNT(*)) OVER (), 2) as percentage"
        )
            ->groupBy('status')
            ->get();

        return [
            'by_status' => $stats->map(fn ($s) => [
                'status' => $s->status,
                'count' => (int) $s->count,
                'percentage' => (float) $s->percentage,
            ]),
            'overall_attendance_rate' => AttendanceRecord::where('status', 'present')->count()
                / max(AttendanceRecord::count(), 1) * 100,
        ];
    }
}
