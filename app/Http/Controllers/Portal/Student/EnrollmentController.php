<?php

namespace App\Http\Controllers\Portal\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\Enrollment;
use App\Models\Section;
use App\Notifications\EnrollmentConfirmed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    public function index(Request $request): View
    {
        $student = $request->user()->student()->firstOrFail();

        $enrollments = $student->enrollments()
            ->with([
                'section.course',
                'section.professor.user',
                'section.academicTerm',
                'grade',
            ])
            ->orderByDesc('registered_at')
            ->get();

        $byTerm = $enrollments->groupBy(fn ($e) => $e->section?->academicTerm?->name ?? 'Unknown Term');

        $currentTerm    = AcademicTerm::where('is_active', true)->latest('academic_year')->first();
        $inRegPeriod    = $currentTerm
            && today()->gte($currentTerm->registration_starts_at)
            && today()->lte($currentTerm->registration_ends_at);

        return view('portal.student.enrollments.index', compact('byTerm', 'currentTerm', 'inRegPeriod'));
    }

    public function create(Request $request): View
    {
        $student     = $request->user()->student()->firstOrFail();
        $currentTerm = AcademicTerm::where('is_active', true)->latest('academic_year')->firstOrFail();

        // Already enrolled section IDs in this term
        $enrolledSectionIds = $student->enrollments()
            ->where('academic_term_id', $currentTerm->id)
            ->whereIn('status', ['registered', 'completed'])
            ->pluck('section_id');

        $sections = Section::with(['course', 'professor.user'])
            ->where('academic_term_id', $currentTerm->id)
            ->where('is_active', true)
            ->whereNotIn('id', $enrolledSectionIds)
            ->withCount(['enrollments' => fn ($q) => $q->whereIn('status', ['registered', 'completed'])])
            ->get();

        return view('portal.student.enrollments.create', compact('sections', 'currentTerm'));
    }

    public function store(Request $request): RedirectResponse
    {
        $student = $request->user()->student;
        if (! $student) {
            return back()->withErrors(['error' => 'Student record not found.']);
        }

        $data    = $request->validate(['section_id' => ['required', 'integer', 'exists:sections,id']]);
        $section = Section::with('academicTerm')->findOrFail($data['section_id']);

        if (! $section->is_active) {
            return back()->withErrors(['error' => 'Section is not active.']);
        }

        $term  = $section->academicTerm;
        $today = today();

        if ($today->lt($term->registration_starts_at) || $today->gt($term->registration_ends_at)) {
            return back()->withErrors(['error' => 'Registration period is not open.']);
        }

        $alreadyEnrolled = Enrollment::where('student_id', $student->id)
            ->where('section_id', $section->id)
            ->whereIn('status', ['registered', 'completed'])
            ->exists();

        if ($alreadyEnrolled) {
            return back()->withErrors(['error' => 'Already enrolled in this section.']);
        }

        $filledSpots = Enrollment::where('section_id', $section->id)
            ->whereIn('status', ['registered', 'completed'])
            ->count();

        if ($filledSpots >= $section->capacity) {
            return back()->withErrors(['error' => 'Section is at full capacity.']);
        }

        $enrollment = Enrollment::create([
            'student_id'       => $student->id,
            'section_id'       => $section->id,
            'academic_term_id' => $term->id,
            'status'           => 'registered',
            'registered_at'    => now(),
        ]);

        $request->user()->notify(new EnrollmentConfirmed($enrollment));

        return redirect()->route('portal.enrollments.index')
            ->with('success', 'Successfully enrolled in section.');
    }

    public function destroy(Request $request, Enrollment $enrollment): RedirectResponse
    {
        $student = $request->user()->student;

        if (! $student || $enrollment->student_id !== $student->id) {
            abort(404);
        }

        if ($enrollment->status === 'dropped') {
            return back()->withErrors(['error' => 'Enrollment is already dropped.']);
        }

        $enrollment->load('section.academicTerm');
        $term  = $enrollment->section->academicTerm;
        $today = today();

        if ($today->lt($term->registration_starts_at) || $today->gt($term->registration_ends_at)) {
            return back()->withErrors(['error' => 'Drop period is not open.']);
        }

        $enrollment->update(['status' => 'dropped', 'dropped_at' => now()]);

        return back()->with('success', 'Enrollment dropped successfully.');
    }
}
