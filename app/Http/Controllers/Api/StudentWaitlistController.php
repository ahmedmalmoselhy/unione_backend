<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentWaitlist;
use App\Models\Section;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentWaitlistController extends Controller
{
    /**
     * GET /api/student/waitlist
     * List the authenticated student's active waitlist positions.
     */
    public function index(Request $request): JsonResponse
    {
        $student = $request->user()->student;

        if (! $student) {
            return response()->json(['message' => 'Student record not found.'], 404);
        }

        $entries = EnrollmentWaitlist::where('student_id', $student->id)
            ->with(['section.course', 'academicTerm'])
            ->orderBy('position')
            ->get()
            ->map(fn ($entry) => [
                'position'  => $entry->position,
                'joined_at' => $entry->joined_at->toDateTimeString(),
                'section'   => [
                    'id'       => $entry->section->id,
                    'room'     => $entry->section->room,
                    'schedule' => $entry->section->schedule,
                ],
                'course' => [
                    'id'           => $entry->section->course->id,
                    'code'         => $entry->section->course->code,
                    'name'         => $entry->section->course->name,
                    'credit_hours' => $entry->section->course->credit_hours,
                ],
                'academic_term' => [
                    'id'            => $entry->academicTerm->id,
                    'name'          => $entry->academicTerm->name,
                    'academic_year' => $entry->academicTerm->academic_year,
                    'semester'      => $entry->academicTerm->semester,
                ],
            ]);

        return response()->json(['waitlist' => $entries]);
    }

    /**
     * DELETE /api/student/waitlist/{section}
     * Leave the waitlist for a section.
     */
    public function destroy(Request $request, Section $section): JsonResponse
    {
        $student = $request->user()->student;

        if (! $student) {
            return response()->json(['message' => 'Student record not found.'], 404);
        }

        $entry = EnrollmentWaitlist::where('student_id', $student->id)
            ->where('section_id', $section->id)
            ->first();

        if (! $entry) {
            return response()->json(['message' => 'Not on the waitlist for this section.'], 404);
        }

        $removedPosition = $entry->position;
        $entry->delete();

        // Re-number entries that were behind this one
        EnrollmentWaitlist::where('section_id', $section->id)
            ->where('position', '>', $removedPosition)
            ->orderBy('position')
            ->get()
            ->each(fn ($e, $i) => $e->update(['position' => $removedPosition + $i]));

        return response()->json(['message' => 'Removed from waitlist successfully.']);
    }
}
