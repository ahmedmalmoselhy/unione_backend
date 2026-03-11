<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\Section;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfessorController extends Controller
{
    /**
     * GET /api/professor/profile
     * Returns the authenticated professor's profile with department and faculty.
     */
    public function profile(Request $request): JsonResponse
    {
        $professor = $request->user()
            ->professor()
            ->with(['department.faculty'])
            ->firstOrFail();

        return response()->json([
            'professor' => [
                'id'              => $professor->id,
                'staff_number'    => $professor->staff_number,
                'specialization'  => $professor->specialization,
                'academic_rank'   => $professor->academic_rank,
                'office_location' => $professor->office_location,
                'hired_at'        => $professor->hired_at?->toDateString(),
                'department' => $professor->department ? [
                    'id'      => $professor->department->id,
                    'name'    => $professor->department->name,
                    'code'    => $professor->department->code,
                    'faculty' => $professor->department->faculty ? [
                        'id'   => $professor->department->faculty->id,
                        'name' => $professor->department->faculty->name,
                        'code' => $professor->department->faculty->code,
                    ] : null,
                ] : null,
            ],
        ]);
    }

    /**
     * GET /api/professor/sections
     * Returns the authenticated professor's sections with course, academic term,
     * and enrolled student count.
     */
    public function sections(Request $request): JsonResponse
    {
        $professor = $request->user()
            ->professor()
            ->firstOrFail();

        $sections = $professor->sections()
            ->with(['course', 'academicTerm'])
            ->withCount('enrollments')
            ->latest()
            ->get()
            ->map(function ($section) {
                return [
                    'id'                => $section->id,
                    'room'              => $section->room,
                    'schedule'          => $section->schedule,
                    'capacity'          => $section->capacity,
                    'enrollments_count' => $section->enrollments_count,
                    'is_active'         => $section->is_active,
                    'course' => $section->course ? [
                        'id'           => $section->course->id,
                        'code'         => $section->course->code,
                        'name'         => $section->course->name,
                        'credit_hours' => $section->course->credit_hours,
                    ] : null,
                    'academic_term' => $section->academicTerm ? [
                        'id'            => $section->academicTerm->id,
                        'name'          => $section->academicTerm->name,
                        'academic_year' => $section->academicTerm->academic_year,
                        'semester'      => $section->academicTerm->semester,
                    ] : null,
                ];
            });

        return response()->json(['sections' => $sections]);
    }

    /**
     * GET /api/professor/sections/{section}/students
     * Returns students enrolled in a section belonging to the authenticated professor.
     */
    public function sectionStudents(Request $request, Section $section): JsonResponse
    {
        $professor = $request->user()->professor;

        if (! $professor) {
            return response()->json(['message' => 'Professor record not found.'], 404);
        }

        if ((int) $section->professor_id !== $professor->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $students = $section->enrollments()
            ->with(['student.user', 'grade'])
            ->whereIn('status', ['registered', 'completed'])
            ->get()
            ->map(function ($enrollment) {
                $student = $enrollment->student;
                $user    = $student?->user;
                $grade   = $enrollment->grade;

                return [
                    'enrollment_id'  => $enrollment->id,
                    'status'         => $enrollment->status,
                    'registered_at'  => $enrollment->registered_at?->toDateTimeString(),
                    'student' => $student ? [
                        'id'             => $student->id,
                        'student_number' => $student->student_number,
                        'academic_year'  => $student->academic_year,
                        'semester'       => $student->semester,
                        'name'           => $user ? $user->first_name . ' ' . $user->last_name : null,
                        'email'          => $user?->email,
                    ] : null,
                    'grade' => $grade ? [
                        'midterm'      => $grade->midterm,
                        'final'        => $grade->final,
                        'coursework'   => $grade->coursework,
                        'total'        => $grade->total,
                        'letter_grade' => $grade->letter_grade,
                        'grade_points' => $grade->grade_points,
                    ] : null,
                ];
            });

        return response()->json([
            'section' => [
                'id'   => $section->id,
                'room' => $section->room,
            ],
            'students' => $students,
        ]);
    }

    /**
     * GET /api/professor/schedule
     * Returns the authenticated professor's schedule for the active academic term.
     */
    public function schedule(Request $request): JsonResponse
    {
        $professor = $request->user()
            ->professor()
            ->firstOrFail();

        $currentTerm = AcademicTerm::where('is_active', true)->latest('academic_year')->first();

        $sections = $professor->sections()
            ->with(['course', 'academicTerm'])
            ->when($currentTerm, fn ($q) => $q->where('academic_term_id', $currentTerm->id))
            ->where('is_active', true)
            ->get();

        if ($sections->isEmpty() && $currentTerm) {
            $sections = $professor->sections()
                ->with(['course', 'academicTerm'])
                ->where('is_active', true)
                ->get();
        }

        $scheduleEntries = $sections->flatMap(function ($section) {
            $schedule   = $section->schedule ?? [];
            $courseData = [
                'id'   => $section->course?->id,
                'code' => $section->course?->code ?? '',
                'name' => $section->course?->name ?? '',
            ];

            if (empty($schedule)) {
                return collect([[
                    'day'        => 'Unscheduled',
                    'start_time' => null,
                    'end_time'   => null,
                    'room'       => $section->room,
                    'type'       => 'teaching',
                    'course'     => $courseData,
                ]]);
            }

            return collect($schedule)->map(fn ($slot) => [
                'day'        => ucfirst(strtolower($slot['day'] ?? '')),
                'start_time' => $slot['start_time'] ?? null,
                'end_time'   => $slot['end_time'] ?? null,
                'room'       => $section->room,
                'type'       => $slot['type'] ?? 'teaching',
                'course'     => $courseData,
            ]);
        })->values();

        return response()->json([
            'academic_term' => $currentTerm ? [
                'id'            => $currentTerm->id,
                'name'          => $currentTerm->name,
                'academic_year' => $currentTerm->academic_year,
                'semester'      => $currentTerm->semester,
            ] : null,
            'schedule' => $scheduleEntries,
        ]);
    }
}
