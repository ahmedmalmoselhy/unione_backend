<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Support\Facades\DB;

class GpaService
{
    /**
     * GPA thresholds for academic standing.
     * Adjust these constants to change institution policy.
     */
    public const PROBATION_THRESHOLD = 2.0;   // below this → probation
    public const DISMISSAL_THRESHOLD = 1.0;   // below this → dismissal

    /**
     * Recompute a student's cumulative GPA (weighted by credit hours) and
     * derive their academic standing, then persist both to the database.
     */
    public static function recalculate(int $studentId): void
    {
        $result = DB::table('grades')
            ->join('enrollments', 'grades.enrollment_id', '=', 'enrollments.id')
            ->join('sections',    'enrollments.section_id', '=', 'sections.id')
            ->join('courses',     'sections.course_id', '=', 'courses.id')
            ->where('enrollments.student_id', $studentId)
            ->whereNotNull('grades.grade_points')
            ->where('courses.credit_hours', '>', 0)
            ->selectRaw('
                SUM(grades.grade_points * courses.credit_hours) as weighted_sum,
                SUM(courses.credit_hours) as total_credits
            ')
            ->first();

        $gpa = ($result && $result->total_credits > 0)
            ? round($result->weighted_sum / $result->total_credits, 2)
            : null;

        Student::where('id', $studentId)->update([
            'gpa'               => $gpa,
            'academic_standing' => self::deriveStanding($gpa),
        ]);
    }

    /**
     * Derive the academic standing label from a GPA value.
     *
     * Returns null when no GPA has been computed yet (no graded courses).
     */
    public static function deriveStanding(?float $gpa): ?string
    {
        if ($gpa === null) {
            return null;
        }

        return match (true) {
            $gpa >= self::PROBATION_THRESHOLD => 'good_standing',
            $gpa >= self::DISMISSAL_THRESHOLD => 'probation',
            default                           => 'dismissal',
        };
    }
}
