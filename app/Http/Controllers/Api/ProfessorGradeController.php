<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Section;
use App\Notifications\GradePosted;
use App\Services\GpaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfessorGradeController extends Controller
{
    /**
     * GET /api/professor/sections/{section}/grades
     * Returns all grades posted in the professor's section.
     */
    public function index(Request $request, Section $section): JsonResponse
    {
        $professor = $request->user()->professor;

        if (! $professor) {
            return response()->json(['message' => 'Professor record not found.'], 404);
        }

        if ((int) $section->professor_id !== $professor->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $grades = $section->enrollments()
            ->with(['student.user', 'grade'])
            ->whereHas('grade')
            ->get()
            ->map(function ($enrollment) {
                $student = $enrollment->student;
                $user    = $student?->user;
                $grade   = $enrollment->grade;

                return [
                    'enrollment_id'  => $enrollment->id,
                    'student' => $student ? [
                        'id'             => $student->id,
                        'student_number' => $student->student_number,
                        'name'           => $user ? $user->first_name . ' ' . $user->last_name : null,
                    ] : null,
                    'midterm'      => $grade->midterm,
                    'final'        => $grade->final,
                    'coursework'   => $grade->coursework,
                    'total'        => $grade->total,
                    'letter_grade' => $grade->letter_grade,
                    'grade_points' => $grade->grade_points,
                    'graded_at'    => $grade->graded_at?->toDateTimeString(),
                ];
            });

        return response()->json(['grades' => $grades]);
    }

    /**
     * Submit or update a grade for an enrollment in the professor's section.
     * POST /api/professor/sections/{section}/grades
     */
    public function store(Request $request, Section $section): JsonResponse
    {
        $professor = $request->user()->professor;

        if (! $professor) {
            return response()->json(['message' => 'Professor record not found.'], 404);
        }

        if ((int) $section->professor_id !== $professor->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $data = $request->validate([
            'enrollment_id' => ['required', 'integer', 'exists:enrollments,id'],
            'midterm'       => ['nullable', 'numeric', 'min:0', 'max:100'],
            'final'         => ['nullable', 'numeric', 'min:0', 'max:100'],
            'coursework'    => ['nullable', 'numeric', 'min:0', 'max:100'],
            'total'         => ['nullable', 'numeric', 'min:0', 'max:100'],
            'letter_grade'  => ['nullable', 'string', 'max:5'],
            'grade_points'  => ['nullable', 'numeric', 'min:0', 'max:4'],
        ]);

        $enrollment = Enrollment::find($data['enrollment_id']);

        if ((int) $enrollment->section_id !== $section->id) {
            return response()->json(['message' => 'Enrollment does not belong to this section.'], 422);
        }

        $isNew = ! $enrollment->grade()->exists();

        $grade = Grade::updateOrCreate(
            ['enrollment_id' => $data['enrollment_id']],
            array_merge($data, [
                'graded_by' => $request->user()->id,
                'graded_at' => now(),
            ])
        );

        // Notify the student their grade has been posted/updated
        $enrollment->load('section.course');
        $studentUser = $enrollment->student?->user;
        if ($studentUser) {
            $studentUser->notify(new GradePosted(
                enrollment:  $enrollment,
                letterGrade: $grade->letter_grade,
                total:       $grade->total,
            ));
        }

        // Recalculate cumulative GPA and academic standing
        if ($enrollment->student_id) {
            GpaService::recalculate($enrollment->student_id);
        }

        return response()->json(['grade' => $grade], $isNew ? 201 : 200);
    }
}
