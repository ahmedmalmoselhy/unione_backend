<?php

namespace App\Http\Controllers\Portal\Student;

use App\Http\Controllers\Controller;
use App\Models\CourseRating;
use App\Models\Enrollment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RatingController extends Controller
{
    public function index(Request $request): View
    {
        $student = $request->user()->student()->firstOrFail();

        // Already-rated enrollments
        $rated = CourseRating::whereHas('enrollment', fn ($q) => $q->where('student_id', $student->id))
            ->with('enrollment.section.course', 'enrollment.section.academicTerm', 'enrollment.section.professor.user')
            ->latest('rated_at')
            ->get();

        // Completed enrollments where the term has ended — eligible to rate
        $pending = Enrollment::where('student_id', $student->id)
            ->where('status', 'completed')
            ->whereDoesntHave('rating')
            ->with('section.course', 'section.academicTerm', 'section.professor.user')
            ->get()
            ->filter(fn ($e) => $e->section?->academicTerm?->ends_at && now()->gte($e->section->academicTerm->ends_at));

        return view('portal.student.ratings', compact('rated', 'pending'));
    }

    public function store(Request $request): RedirectResponse
    {
        $student = $request->user()->student()->firstOrFail();

        $data = $request->validate([
            'enrollment_id' => ['required', 'integer', 'exists:enrollments,id'],
            'rating'        => ['required', 'integer', 'min:1', 'max:5'],
            'comment'       => ['nullable', 'string', 'max:2000'],
        ]);

        $enrollment = Enrollment::with('section.academicTerm')->findOrFail($data['enrollment_id']);

        if ((int) $enrollment->student_id !== $student->id) {
            abort(403);
        }

        if ($enrollment->status !== 'completed') {
            return back()->withErrors(['enrollment_id' => 'You can only rate completed courses.']);
        }

        $termEnd = $enrollment->section?->academicTerm?->ends_at;
        if (! $termEnd || now()->lt($termEnd)) {
            return back()->withErrors(['enrollment_id' => 'The term has not ended yet.']);
        }

        CourseRating::updateOrCreate(
            ['enrollment_id' => $enrollment->id],
            ['rating' => $data['rating'], 'comment' => $data['comment'] ?? null, 'rated_at' => now()]
        );

        return back()->with('success', 'Thank you for your feedback!');
    }
}
