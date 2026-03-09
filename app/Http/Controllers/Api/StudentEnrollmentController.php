<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Section;
use App\Notifications\EnrollmentConfirmed;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentEnrollmentController extends Controller
{
    /**
     * Enroll the authenticated student in a section.
     * POST /api/student/enrollments
     */
    public function store(Request $request): JsonResponse
    {
        $student = $request->user()->student;

        if (! $student) {
            return response()->json(['message' => 'Student record not found.'], 404);
        }

        $data = $request->validate([
            'section_id' => ['required', 'integer', 'exists:sections,id'],
        ]);

        $section = Section::with('academicTerm')->find($data['section_id']);

        if (! $section->is_active) {
            return response()->json(['message' => 'Section is not active.'], 422);
        }

        $term  = $section->academicTerm;
        $today = today();

        if ($today->lt($term->registration_starts_at) || $today->gt($term->registration_ends_at)) {
            return response()->json(['message' => 'Registration period is not open.'], 422);
        }

        $alreadyEnrolled = Enrollment::where('student_id', $student->id)
            ->where('section_id', $section->id)
            ->whereIn('status', ['registered', 'completed'])
            ->exists();

        if ($alreadyEnrolled) {
            return response()->json(['message' => 'Already enrolled in this section.'], 422);
        }

        $filledSpots = Enrollment::where('section_id', $section->id)
            ->whereIn('status', ['registered', 'completed'])
            ->count();

        if ($filledSpots >= $section->capacity) {
            return response()->json(['message' => 'Section is at full capacity.'], 422);
        }

        $enrollment = Enrollment::create([
            'student_id'       => $student->id,
            'section_id'       => $section->id,
            'academic_term_id' => $term->id,
            'status'           => 'registered',
            'registered_at'    => now(),
        ]);

        $enrollment->load('section.course', 'academicTerm');

        $request->user()->notify(new EnrollmentConfirmed($enrollment));

        return response()->json(['enrollment' => $enrollment], 201);
    }

    /**
     * Drop an enrollment during the registration period.
     * DELETE /api/student/enrollments/{enrollment}
     */
    public function destroy(Request $request, Enrollment $enrollment): JsonResponse
    {
        $student = $request->user()->student;

        if (! $student || $enrollment->student_id !== $student->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        if ($enrollment->status === 'dropped') {
            return response()->json(['message' => 'Already dropped.'], 422);
        }

        $enrollment->load('section.academicTerm');
        $term  = $enrollment->section->academicTerm;
        $today = today();

        if ($today->lt($term->registration_starts_at) || $today->gt($term->registration_ends_at)) {
            return response()->json(['message' => 'Drop period is not open.'], 422);
        }

        $enrollment->update([
            'status'     => 'dropped',
            'dropped_at' => now(),
        ]);

        return response()->json(['message' => 'Enrollment dropped successfully.']);
    }
}
