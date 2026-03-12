<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * GET /api/student/profile
     * Returns the authenticated student's profile with faculty and department.
     */
    public function profile(Request $request): JsonResponse
    {
        $student = $request->user()
            ->student()
            ->with(['faculty', 'department'])
            ->firstOrFail();

        return response()->json([
            'student' => [
                'id'                => $student->id,
                'student_number'    => $student->student_number,
                'academic_year'     => $student->academic_year,
                'semester'          => $student->semester,
                'enrollment_status' => $student->enrollment_status,
                'gpa'               => $student->gpa,
                'academic_standing' => $student->academic_standing,
                'enrolled_at'       => $student->enrolled_at?->toDateString(),
                'graduated_at'      => $student->graduated_at?->toDateString(),
                'faculty'           => $student->faculty ? [
                    'id'   => $student->faculty->id,
                    'name' => $student->faculty->name,
                    'code' => $student->faculty->code,
                ] : null,
                'department' => $student->department ? [
                    'id'   => $student->department->id,
                    'name' => $student->department->name,
                    'code' => $student->department->code,
                ] : null,
            ],
        ]);
    }

    /**
     * GET /api/student/enrollments
     * Returns the authenticated student's enrollments with section, course,
     * academic term, and grade (if graded).
     */
    public function enrollments(Request $request): JsonResponse
    {
        $student = $request->user()
            ->student()
            ->firstOrFail();

        $enrollments = $student->enrollments()
            ->with([
                'section.course',
                'section.academicTerm',
                'grade',
            ])
            ->latest()
            ->get()
            ->map(function ($enrollment) {
                $section = $enrollment->section;
                $course  = $section?->course;
                $term    = $section?->academicTerm;
                $grade   = $enrollment->grade;

                return [
                    'id'            => $enrollment->id,
                    'status'        => $enrollment->status,
                    'registered_at' => $enrollment->registered_at?->toDateTimeString(),
                    'dropped_at'    => $enrollment->dropped_at?->toDateTimeString(),
                    'course' => $course ? [
                        'id'           => $course->id,
                        'code'         => $course->code,
                        'name'         => $course->name,
                        'credit_hours' => $course->credit_hours,
                    ] : null,
                    'section' => $section ? [
                        'id'       => $section->id,
                        'room'     => $section->room,
                        'schedule' => $section->schedule,
                    ] : null,
                    'academic_term' => $term ? [
                        'id'            => $term->id,
                        'name'          => $term->name,
                        'academic_year' => $term->academic_year,
                        'semester'      => $term->semester,
                    ] : null,
                    'grade' => $grade ? [
                        'midterm'      => $grade->midterm,
                        'final'        => $grade->final,
                        'coursework'   => $grade->coursework,
                        'total'        => $grade->total,
                        'letter_grade' => $grade->letter_grade,
                        'grade_points' => $grade->grade_points,
                        'graded_at'    => $grade->graded_at?->toDateTimeString(),
                    ] : null,
                ];
            });

        return response()->json(['enrollments' => $enrollments]);
    }

    /**
     * GET /api/student/grades
     * Returns all graded enrollments for the authenticated student.
     */
    public function grades(Request $request): JsonResponse
    {
        $student = $request->user()
            ->student()
            ->firstOrFail();

        // Index semester GPAs keyed by academic_term_id for O(1) lookup
        $termGpas = $student->termGpas()->get()->keyBy('academic_term_id');

        $grades = $student->enrollments()
            ->with(['section.course', 'section.academicTerm', 'grade'])
            ->whereHas('grade')
            ->latest()
            ->get()
            ->map(function ($enrollment) use ($termGpas) {
                $section = $enrollment->section;
                $course  = $section?->course;
                $term    = $section?->academicTerm;
                $grade   = $enrollment->grade;
                $termGpa = $term ? $termGpas->get($term->id)?->gpa : null;

                return [
                    'enrollment_id' => $enrollment->id,
                    'status'        => $enrollment->status,
                    'course' => $course ? [
                        'id'           => $course->id,
                        'code'         => $course->code,
                        'name'         => $course->name,
                        'credit_hours' => $course->credit_hours,
                    ] : null,
                    'academic_term' => $term ? [
                        'id'            => $term->id,
                        'name'          => $term->name,
                        'academic_year' => $term->academic_year,
                        'semester'      => $term->semester,
                        'semester_gpa'  => $termGpa !== null ? (float) $termGpa : null,
                    ] : null,
                    'grade' => [
                        'midterm'      => $grade->midterm,
                        'final'        => $grade->final,
                        'coursework'   => $grade->coursework,
                        'total'        => $grade->total,
                        'letter_grade' => $grade->letter_grade,
                        'grade_points' => $grade->grade_points,
                        'graded_at'    => $grade->graded_at?->toDateTimeString(),
                    ],
                ];
            });

        return response()->json(['grades' => $grades]);
    }

    /**
     * GET /api/student/schedule
     * Returns the authenticated student's schedule for the active academic term.
     */
    public function schedule(Request $request): JsonResponse
    {
        $student = $request->user()
            ->student()
            ->firstOrFail();

        $currentTerm = AcademicTerm::where('is_active', true)->latest('academic_year')->first();

        $enrollments = $student->enrollments()
            ->with(['section.course', 'section.professor.user', 'section.academicTerm'])
            ->when($currentTerm, fn ($q) => $q->where('academic_term_id', $currentTerm->id))
            ->whereIn('status', ['registered', 'completed'])
            ->get();

        if ($enrollments->isEmpty() && $currentTerm) {
            $enrollments = $student->enrollments()
                ->with(['section.course', 'section.professor.user', 'section.academicTerm'])
                ->whereIn('status', ['registered', 'completed'])
                ->get();
        }

        $scheduleEntries = $enrollments->flatMap(function ($enrollment) {
            $section  = $enrollment->section;
            $schedule = $section?->schedule ?? [];

            $courseData = [
                'id'   => $section?->course?->id,
                'code' => $section?->course?->code ?? '',
                'name' => $section?->course?->name ?? '',
            ];
            $professorName = $section?->professor?->user
                ? $section->professor->user->first_name . ' ' . $section->professor->user->last_name
                : null;

            if (empty($schedule)) {
                return collect([[
                    'day'        => 'Unscheduled',
                    'start_time' => null,
                    'end_time'   => null,
                    'room'       => $section?->room,
                    'type'       => 'lecture',
                    'course'     => $courseData,
                    'professor'  => $professorName,
                ]]);
            }

            return collect($schedule)->map(fn ($slot) => [
                'day'        => ucfirst(strtolower($slot['day'] ?? '')),
                'start_time' => $slot['start_time'] ?? null,
                'end_time'   => $slot['end_time'] ?? null,
                'room'       => $section?->room,
                'type'       => $slot['type'] ?? 'lecture',
                'course'     => $courseData,
                'professor'  => $professorName,
            ]);
        })->values();

        return response()->json([
            'academic_term'   => $currentTerm ? [
                'id'            => $currentTerm->id,
                'name'          => $currentTerm->name,
                'academic_year' => $currentTerm->academic_year,
                'semester'      => $currentTerm->semester,
            ] : null,
            'schedule' => $scheduleEntries,
        ]);
    }

    /**
     * GET /api/student/transcript
     * Returns the student's full academic transcript grouped by term.
     */
    public function transcript(Request $request): JsonResponse
    {
        $student = $request->user()
            ->student()
            ->with(['user', 'faculty', 'department'])
            ->firstOrFail();

        $termGpas = $student->termGpas()->get()->keyBy('academic_term_id');

        $enrollments = $student->enrollments()
            ->with(['section.course', 'section.academicTerm', 'grade'])
            ->whereHas('grade')
            ->whereIn('status', ['completed'])
            ->get();

        $terms = $enrollments->groupBy(fn ($e) => $e->section->academicTerm->id)
            ->map(function ($termEnrollments) use ($termGpas) {
                $term        = $termEnrollments->first()->section->academicTerm;
                $termGpa     = $termGpas->get($term->id);
                $totalCredits = $termEnrollments->sum(fn ($e) => $e->section->course->credit_hours ?? 0);

                $courses = $termEnrollments->map(fn ($e) => [
                    'course' => [
                        'id'           => $e->section->course->id,
                        'code'         => $e->section->course->code,
                        'name'         => $e->section->course->name,
                        'credit_hours' => $e->section->course->credit_hours,
                    ],
                    'grade' => [
                        'midterm'      => $e->grade->midterm,
                        'final'        => $e->grade->final,
                        'coursework'   => $e->grade->coursework,
                        'total'        => $e->grade->total,
                        'letter_grade' => $e->grade->letter_grade,
                        'grade_points' => $e->grade->grade_points,
                    ],
                ])->values();

                return [
                    'academic_term' => [
                        'id'            => $term->id,
                        'name'          => $term->name,
                        'academic_year' => $term->academic_year,
                        'semester'      => $term->semester,
                    ],
                    'term_gpa'     => $termGpa ? (float) $termGpa->gpa : null,
                    'term_credits' => $totalCredits,
                    'courses'      => $courses,
                ];
            })
            ->sortBy(fn ($t) => $t['academic_term']['id'])
            ->values();

        return response()->json([
            'student' => [
                'student_number'    => $student->student_number,
                'name'              => $student->user->first_name . ' ' . $student->user->last_name,
                'faculty'           => $student->faculty?->name,
                'department'        => $student->department?->name,
                'gpa'               => $student->gpa,
                'academic_standing' => $student->academic_standing,
            ],
            'terms' => $terms,
        ]);
    }
}
