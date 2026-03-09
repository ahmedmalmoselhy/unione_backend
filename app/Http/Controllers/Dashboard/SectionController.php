<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreSectionRequest;
use App\Http\Requests\Dashboard\UpdateSectionRequest;
use App\Models\AcademicTerm;
use App\Models\AuditLog;
use App\Models\Course;
use App\Models\Professor;
use App\Models\Section;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SectionController extends Controller
{
    use Concerns\DashboardScopeAware;

    public function index(Request $request): View
    {
        $sections = Section::with(['course', 'professor.user', 'academicTerm'])
            ->when($this->scopedFacultyId(), fn ($q, $id) => $q->whereHas('course.departments', fn ($d) => $d->where('faculty_id', $id)))
            ->when($this->scopedDepartmentId(), fn ($q, $id) => $q->whereHas('professor', fn ($p) => $p->where('department_id', $id)))
            ->when($request->filled('search'), fn ($q) => $q->whereHas('course', function ($q) use ($request) {
                $q->whereIlike('code', '%' . $request->search . '%')
                  ->orWhereIlike('name', '%' . $request->search . '%');
            }))
            ->when($request->filled('course_id'), fn ($q) => $q->where('course_id', $request->course_id))
            ->when($request->filled('term_id'), fn ($q) => $q->where('academic_term_id', $request->term_id))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $courses = Course::where('is_active', true)->orderBy('code')->get()->pluck(null, 'id')->map(fn ($c) => $c->code . ' — ' . $c->name);
        $terms = AcademicTerm::orderByDesc('academic_year')->pluck('name', 'id');

        return view('dashboard.sections.index', compact('sections', 'courses', 'terms'));
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
        $section = Section::create([
            'course_id'        => $request->course_id,
            'professor_id'     => $request->professor_id,
            'academic_term_id' => $request->academic_term_id,
            'capacity'         => $request->capacity,
            'room'             => $request->room,
            'schedule'         => $request->schedule,
        ]);

        AuditLog::record(
            action: 'created',
            auditableType: 'Section',
            auditableId: $section->id,
            description: "Created section #{$section->id} for course #{$section->course_id}",
            newValues: ['course_id' => $section->course_id, 'professor_id' => $section->professor_id, 'academic_term_id' => $section->academic_term_id, 'capacity' => $section->capacity],
        );

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
        $oldValues = [
            'course_id'        => $section->course_id,
            'professor_id'     => $section->professor_id,
            'academic_term_id' => $section->academic_term_id,
            'capacity'         => $section->capacity,
            'room'             => $section->room,
            'is_active'        => $section->is_active,
        ];

        $section->update([
            'course_id'        => $request->course_id,
            'professor_id'     => $request->professor_id,
            'academic_term_id' => $request->academic_term_id,
            'capacity'         => $request->capacity,
            'room'             => $request->room,
            'schedule'         => $request->schedule,
            'is_active'        => $request->boolean('is_active'),
        ]);

        AuditLog::record(
            action: 'updated',
            auditableType: 'Section',
            auditableId: $section->id,
            description: "Updated section #{$section->id}",
            oldValues: $oldValues,
            newValues: ['course_id' => $request->course_id, 'professor_id' => $request->professor_id, 'academic_term_id' => $request->academic_term_id, 'capacity' => $request->capacity, 'room' => $request->room, 'is_active' => $request->boolean('is_active')],
        );

        return redirect()->route('dashboard.sections.index')
            ->with('success', 'Section updated successfully.');
    }

    public function destroy(Section $section): RedirectResponse
    {
        if ($section->enrollments()->exists()) {
            return back()->withErrors(['delete' => 'This section cannot be deleted because it has associated enrollments.']);
        }

        $id       = $section->id;
        $courseId = $section->course_id;

        $section->delete();

        AuditLog::record(
            action: 'deleted',
            auditableType: 'Section',
            auditableId: $id,
            description: "Deleted section #{$id} for course #{$courseId}",
        );

        return redirect()->route('dashboard.sections.index')
            ->with('success', 'Section deleted successfully.');
    }

    private function formData(): array
    {
        return [
            'courses'       => Course::where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']),
            'professors'    => Professor::with('user')->get()->sortBy(fn ($p) => $p->user->first_name),
            'academicTerms' => AcademicTerm::orderByDesc('academic_year')
                ->orderByRaw("array_position(ARRAY['summer','second','first'], semester)")
                ->get(['id', 'name', 'is_active']),
        ];
    }
}
