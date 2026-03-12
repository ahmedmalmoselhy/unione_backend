<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\DispatchWebhooks;
use App\Models\Enrollment;
use App\Models\EnrollmentWaitlist;
use App\Models\Section;
use App\Notifications\EnrollmentConfirmed;
use App\Notifications\WaitlistPromoted;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $section = Section::with(['academicTerm', 'course.prerequisites'])->find($data['section_id']);

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

        // ── Prerequisite check ──────────────────────────────────────────────
        $prerequisites = $section->course->prerequisites;

        if ($prerequisites->isNotEmpty()) {
            $completedCourseIds = $student->enrollments()
                ->where('status', 'completed')
                ->with('section:id,course_id')
                ->get()
                ->pluck('section.course_id')
                ->filter()
                ->unique();

            $missing = $prerequisites->whereNotIn('id', $completedCourseIds)->values();

            if ($missing->isNotEmpty()) {
                return response()->json([
                    'message'               => 'Prerequisites not satisfied.',
                    'missing_prerequisites' => $missing->map(fn ($c) => [
                        'id'   => $c->id,
                        'code' => $c->code,
                        'name' => $c->name,
                    ]),
                ], 422);
            }
        }

        // ── Capacity check — join waitlist if full ──────────────────────────
        $filledSpots = Enrollment::where('section_id', $section->id)
            ->whereIn('status', ['registered', 'completed'])
            ->count();

        if ($filledSpots >= $section->capacity) {
            $alreadyWaiting = EnrollmentWaitlist::where('student_id', $student->id)
                ->where('section_id', $section->id)
                ->exists();

            if ($alreadyWaiting) {
                return response()->json(['message' => 'Already on the waitlist for this section.'], 422);
            }

            $position = EnrollmentWaitlist::where('section_id', $section->id)->max('position') + 1;

            $waitlist = EnrollmentWaitlist::create([
                'student_id'       => $student->id,
                'section_id'       => $section->id,
                'academic_term_id' => $term->id,
                'position'         => $position,
                'joined_at'        => now(),
            ]);

            return response()->json([
                'message'  => 'Section is full. You have been added to the waitlist.',
                'waitlist' => [
                    'position'  => $waitlist->position,
                    'joined_at' => $waitlist->joined_at->toDateTimeString(),
                ],
            ], 202);
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

        DispatchWebhooks::dispatch('enrollment.confirmed', [
            'event'       => 'enrollment.confirmed',
            'student'     => $student->student_number,
            'course_code' => $enrollment->section->course->code ?? null,
            'course_name' => $enrollment->section->course->name ?? null,
            'section_id'  => $enrollment->section_id,
            'term'        => $enrollment->academicTerm->name ?? null,
            'enrolled_at' => $enrollment->registered_at?->toDateTimeString(),
        ]);

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

        DB::transaction(function () use ($enrollment, $term) {
            $enrollment->update([
                'status'     => 'dropped',
                'dropped_at' => now(),
            ]);

            // ── Auto-promote first student from waitlist ────────────────────
            $next = EnrollmentWaitlist::where('section_id', $enrollment->section_id)
                ->orderBy('position')
                ->lockForUpdate()
                ->first();

            if ($next) {
                $nextStudentId = $next->student_id;
                $next->delete();

                // Re-number remaining entries
                EnrollmentWaitlist::where('section_id', $enrollment->section_id)
                    ->orderBy('position')
                    ->get()
                    ->each(fn ($entry, $i) => $entry->update(['position' => $i + 1]));

                $promoted = Enrollment::create([
                    'student_id'       => $nextStudentId,
                    'section_id'       => $enrollment->section_id,
                    'academic_term_id' => $term->id,
                    'status'           => 'registered',
                    'registered_at'    => now(),
                ]);

                $promoted->load('section.course', 'academicTerm');
                $next->student->user->notify(new WaitlistPromoted($promoted));
            }
        });

        return response()->json(['message' => 'Enrollment dropped successfully.']);
    }
}
