<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourseRating;
use App\Models\Enrollment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseRatingController extends Controller
{
    /**
     * POST /api/student/ratings
     * Student submits a rating for a completed enrollment (term must be closed).
     */
    public function store(Request $request): JsonResponse
    {
        $student = $request->user()->student()->firstOrFail();

        $data = $request->validate([
            'enrollment_id' => ['required', 'integer', 'exists:enrollments,id'],
            'rating'        => ['required', 'integer', 'min:1', 'max:5'],
            'comment'       => ['nullable', 'string', 'max:2000'],
        ]);

        $enrollment = Enrollment::with('section.academicTerm')->findOrFail($data['enrollment_id']);

        // Must belong to this student
        if ((int) $enrollment->student_id !== $student->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        // Must be completed
        if ($enrollment->status !== 'completed') {
            return response()->json(['message' => 'You can only rate completed courses.'], 422);
        }

        // Term must have ended
        $termEnd = $enrollment->section?->academicTerm?->ends_at;
        if (! $termEnd || now()->lt(\Carbon\Carbon::parse($termEnd))) {
            return response()->json(['message' => 'The term has not ended yet.'], 422);
        }

        // One rating per enrollment (upsert)
        $rating = CourseRating::updateOrCreate(
            ['enrollment_id' => $enrollment->id],
            [
                'rating'   => $data['rating'],
                'comment'  => $data['comment'] ?? null,
                'rated_at' => now(),
            ]
        );

        return response()->json(['rating' => [
            'id'            => $rating->id,
            'enrollment_id' => $rating->enrollment_id,
            'rating'        => $rating->rating,
            'comment'       => $rating->comment,
            'rated_at'      => $rating->rated_at->toDateTimeString(),
        ]], 201);
    }

    /**
     * GET /api/student/ratings
     * Student lists their own ratings.
     */
    public function index(Request $request): JsonResponse
    {
        $student = $request->user()->student()->firstOrFail();

        $ratings = CourseRating::whereHas('enrollment', fn ($q) => $q->where('student_id', $student->id))
            ->with('enrollment.section.course')
            ->latest('rated_at')
            ->get()
            ->map(fn ($r) => [
                'id'            => $r->id,
                'enrollment_id' => $r->enrollment_id,
                'rating'        => $r->rating,
                'comment'       => $r->comment,
                'rated_at'      => $r->rated_at->toDateTimeString(),
                'course'        => $r->enrollment?->section?->course ? [
                    'code' => $r->enrollment->section->course->code,
                    'name' => $r->enrollment->section->course->name,
                ] : null,
            ]);

        return response()->json(['ratings' => $ratings]);
    }
}
