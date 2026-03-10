<?php

namespace App\Http\Controllers\Portal\Professor;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Section;
use App\Notifications\GradePosted;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SectionController extends Controller
{
    public function index(Request $request): View
    {
        $professor = $request->user()->professor()->firstOrFail();

        $sections = $professor->sections()
            ->with(['course', 'academicTerm'])
            ->withCount('enrollments')
            ->orderByDesc('created_at')
            ->get();

        return view('portal.professor.sections.index', compact('sections'));
    }

    public function show(Request $request, Section $section): View
    {
        $professor = $request->user()->professor()->firstOrFail();

        if ((int) $section->professor_id !== $professor->id) {
            abort(403);
        }

        $section->load(['course', 'academicTerm']);

        $enrollments = $section->enrollments()
            ->with(['student.user', 'grade'])
            ->whereIn('status', ['registered', 'completed'])
            ->get();

        return view('portal.professor.sections.show', compact('section', 'enrollments'));
    }

    public function postGrade(Request $request, Section $section): RedirectResponse
    {
        $professor = $request->user()->professor;

        if (! $professor || (int) $section->professor_id !== $professor->id) {
            abort(403);
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

        $enrollment = Enrollment::findOrFail($data['enrollment_id']);

        if ((int) $enrollment->section_id !== $section->id) {
            abort(422);
        }

        $grade = Grade::updateOrCreate(
            ['enrollment_id' => $data['enrollment_id']],
            array_merge($data, [
                'graded_by' => $request->user()->id,
                'graded_at' => now(),
            ])
        );

        $enrollment->load('section.course');
        $studentUser = $enrollment->student?->user;
        if ($studentUser) {
            $studentUser->notify(new GradePosted(
                enrollment:  $enrollment,
                letterGrade: $grade->letter_grade,
                total:       $grade->total,
            ));
        }

        return back()->with('success', 'Grade saved successfully.');
    }
}
