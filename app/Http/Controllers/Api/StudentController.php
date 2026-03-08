<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
}
