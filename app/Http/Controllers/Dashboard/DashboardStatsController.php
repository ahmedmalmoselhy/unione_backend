<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Professor;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardStatsController extends Controller
{
    use Concerns\DashboardScopeAware;

    /**
     * GET /dashboard/stats
     * Returns charts-ready stats scoped to the authenticated admin's role.
     */
    public function index(): JsonResponse
    {
        $fId = $this->scopedFacultyId();
        $dId = $this->scopedDepartmentId();

        return response()->json([
            'overview'           => $this->overview($fId, $dId),
            'enrollment_status'  => $this->enrollmentStatus($fId, $dId),
            'grade_distribution' => $this->gradeDistribution($fId, $dId),
            'gpa_distribution'   => $this->gpaDistribution($fId, $dId),
            'enrollment_rates'   => $this->enrollmentRates($fId, $dId),
        ]);
    }

    // ── Private query helpers ─────────────────────────────────────────────────

    private function studentQuery(?int $fId, ?int $dId)
    {
        return Student::query()
            ->when($fId, fn ($q) => $q->where('faculty_id', $fId))
            ->when($dId, fn ($q) => $q->where('department_id', $dId));
    }

    /**
     * Overview counts: students, professors, courses, sections.
     */
    private function overview(?int $fId, ?int $dId): array
    {
        $students = $this->studentQuery($fId, $dId)->count();

        $professors = Professor::query()
            ->when($fId, fn ($q) => $q->whereHas('department', fn ($d) => $d->where('faculty_id', $fId)))
            ->when($dId, fn ($q) => $q->where('department_id', $dId))
            ->count();

        $courses = Course::query()
            ->when($fId, fn ($q) => $q->whereHas('departments', fn ($d) => $d->where('faculty_id', $fId)))
            ->when($dId, fn ($q) => $q->whereHas('departments', fn ($d) => $d->where('departments.id', $dId)))
            ->count();

        $sections = Section::query()
            ->when($fId, fn ($q) => $q->whereHas('course.departments', fn ($d) => $d->where('faculty_id', $fId)))
            ->when($dId, fn ($q) => $q->whereHas('course.departments', fn ($d) => $d->where('departments.id', $dId)))
            ->count();

        return compact('students', 'professors', 'courses', 'sections');
    }

    /**
     * Student counts grouped by enrollment_status.
     * Returns e.g. { "active": 120, "graduated": 45, "suspended": 3, "withdrawn": 7 }
     */
    private function enrollmentStatus(?int $fId, ?int $dId): array
    {
        return $this->studentQuery($fId, $dId)
            ->selectRaw('enrollment_status, COUNT(*) as count')
            ->groupBy('enrollment_status')
            ->pluck('count', 'enrollment_status')
            ->map(fn ($v) => (int) $v)
            ->toArray();
    }

    /**
     * Grade counts grouped by letter_grade.
     * Returns e.g. { "A": 230, "B": 180, "C": 95, "D": 30, "F": 12 }
     */
    private function gradeDistribution(?int $fId, ?int $dId): array
    {
        return DB::table('grades')
            ->join('enrollments', 'grades.enrollment_id', '=', 'enrollments.id')
            ->join('students', 'enrollments.student_id', '=', 'students.id')
            ->when($fId, fn ($q) => $q->where('students.faculty_id', $fId))
            ->when($dId, fn ($q) => $q->where('students.department_id', $dId))
            ->whereNotNull('grades.letter_grade')
            ->selectRaw('grades.letter_grade, COUNT(*) as count')
            ->groupBy('grades.letter_grade')
            ->orderBy('grades.letter_grade')
            ->pluck('count', 'letter_grade')
            ->map(fn ($v) => (int) $v)
            ->toArray();
    }

    /**
     * Student GPA distribution in five brackets.
     * Returns e.g. { "0.0-1.99": 5, "2.0-2.49": 18, "2.5-2.99": 35, "3.0-3.49": 60, "3.5-4.0": 42 }
     */
    private function gpaDistribution(?int $fId, ?int $dId): array
    {
        $brackets = [
            '0.0-1.99' => 0,
            '2.0-2.49' => 0,
            '2.5-2.99' => 0,
            '3.0-3.49' => 0,
            '3.5-4.0'  => 0,
        ];

        $gpas = $this->studentQuery($fId, $dId)
            ->whereNotNull('gpa')
            ->pluck('gpa');

        foreach ($gpas as $gpa) {
            $gpa = (float) $gpa;
            if ($gpa < 2.0)      $brackets['0.0-1.99']++;
            elseif ($gpa < 2.5)  $brackets['2.0-2.49']++;
            elseif ($gpa < 3.0)  $brackets['2.5-2.99']++;
            elseif ($gpa < 3.5)  $brackets['3.0-3.49']++;
            else                 $brackets['3.5-4.0']++;
        }

        return $brackets;
    }

    /**
     * Fill rates for all sections in currently active academic terms.
     * Returns array of { section_id, course_code, course_name, capacity, filled, fill_pct }
     */
    private function enrollmentRates(?int $fId, ?int $dId): array
    {
        return Section::with(['course', 'academicTerm'])
            ->whereHas('academicTerm', fn ($q) => $q->where('is_active', true))
            ->when($fId, fn ($q) => $q->whereHas('course.departments', fn ($d) => $d->where('faculty_id', $fId)))
            ->when($dId, fn ($q) => $q->whereHas('course.departments', fn ($d) => $d->where('departments.id', $dId)))
            ->withCount([
                'enrollments as filled_spots' => fn ($q) => $q->whereIn('status', ['registered', 'completed']),
            ])
            ->get()
            ->map(fn ($s) => [
                'section_id'  => $s->id,
                'course_code' => $s->course?->code,
                'course_name' => $s->course?->name,
                'capacity'    => $s->capacity,
                'filled'      => $s->filled_spots,
                'fill_pct'    => $s->capacity > 0 ? round(($s->filled_spots / $s->capacity) * 100, 1) : 0.0,
            ])
            ->values()
            ->toArray();
    }
}
