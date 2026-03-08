<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreSectionRequest;
use App\Http\Requests\Dashboard\UpdateSectionRequest;
use App\Models\AcademicTerm;
use App\Models\Course;
use App\Models\Professor;
use App\Models\Section;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SectionController extends Controller
{
    public function index(): View
    {
        $sections = Section::with(['course', 'professor.user', 'academicTerm'])
            ->orderByDesc('id')
            ->paginate(15);

        return view('dashboard.sections.index', compact('sections'));
    }

    public function show(Section $section): View
    {
        $section->load(['course', 'professor.user', 'academicTerm', 'enrollments.student.user']);

        return view('dashboard.sections.show', compact('section'));
    }

    public function create(): View
    {
        return view('dashboard.sections.create', $this->formData());
    }

    public function store(StoreSectionRequest $request): RedirectResponse
    {
        Section::create([
            'course_id'        => $request->course_id,
            'professor_id'     => $request->professor_id,
            'academic_term_id' => $request->academic_term_id,
            'capacity'         => $request->capacity,
            'room'             => $request->room,
            'schedule'         => $request->schedule,
        ]);

        return redirect()->route('dashboard.sections.index')
            ->with('success', 'Section created successfully.');
    }

    public function edit(Section $section): View
    {
        return view('dashboard.sections.edit', array_merge(
            ['section' => $section],
            $this->formData()
        ));
    }

    public function update(UpdateSectionRequest $request, Section $section): RedirectResponse
    {
        $section->update([
            'course_id'        => $request->course_id,
            'professor_id'     => $request->professor_id,
            'academic_term_id' => $request->academic_term_id,
            'capacity'         => $request->capacity,
            'room'             => $request->room,
            'schedule'         => $request->schedule,
            'is_active'        => $request->boolean('is_active'),
        ]);

        return redirect()->route('dashboard.sections.index')
            ->with('success', 'Section updated successfully.');
    }

    public function destroy(Section $section): RedirectResponse
    {
        try {
            $section->delete();
        } catch (\Illuminate\Database\QueryException) {
            return back()->withErrors(['delete' => 'This section cannot be deleted because it has associated enrollments.']);
        }

        return redirect()->route('dashboard.sections.index')
            ->with('success', 'Section deleted successfully.');
    }

    private function formData(): array
    {
        return [
            'courses'       => Course::where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']),
            'professors'    => Professor::with('user')->get()->sortBy(fn ($p) => $p->user->first_name),
            'academicTerms' => AcademicTerm::orderByDesc('academic_year')
                ->orderByRaw("FIELD(semester, 'summer', 'second', 'first')")
                ->get(['id', 'name', 'is_active']),
        ];
    }
}
