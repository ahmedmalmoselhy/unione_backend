<?php

namespace App\Http\Controllers\Dashboard;

use App\Exports\EnrollmentsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreEnrollmentRequest;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\Dashboard\UpdateEnrollmentRequest;
use App\Models\AcademicTerm;
use App\Models\Enrollment;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    use Concerns\DashboardScopeAware;

    public function index(Request $request)
    {
        $enrollments = Enrollment::with([
            'student.user',
            'section.course',
            'academicTerm',
        ])
            ->when($this->scopedFacultyId(), fn ($q, $id) => $q->whereHas('student', fn ($s) => $s->where('faculty_id', $id)))
            ->when($this->scopedDepartmentId(), fn ($q, $id) => $q->whereHas('student', fn ($s) => $s->where('department_id', $id)))
            ->when($request->filled('search'), fn ($q) => $q->where(function ($q) use ($request) {
                $q->whereHas('student.user', fn ($u) => $u->whereIlike('first_name', '%' . $request->search . '%')
                      ->orWhereIlike('last_name', '%' . $request->search . '%'))
                  ->orWhereHas('student', fn ($s) => $s->whereIlike('student_number', '%' . $request->search . '%'))
                  ->orWhereHas('section.course', fn ($c) => $c->whereIlike('code', '%' . $request->search . '%')
                      ->orWhereIlike('name', '%' . $request->search . '%'));
            }))
            ->when($request->filled('term_id'), fn ($q) => $q->where('academic_term_id', $request->term_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('registered_at')
            ->paginate(20)
            ->withQueryString();

        $terms = AcademicTerm::orderByDesc('academic_year')->pluck('name', 'id');

        return view('dashboard.enrollments.index', compact('enrollments', 'terms'));
    }

    public function show(Enrollment $enrollment)
    {
        $enrollment->load([
            'student.user',
            'section.course',
            'section.professor.user',
            'academicTerm',
            'grade',
        ]);

        return view('dashboard.enrollments.show', compact('enrollment'));
    }

    public function create()
    {
        return view('dashboard.enrollments.create', $this->formData());
    }

    public function store(StoreEnrollmentRequest $request)
    {
        Enrollment::create($request->validated());

        return redirect()
            ->route('dashboard.enrollments.index')
            ->with('success', 'Enrollment created successfully.');
    }

    public function edit(Enrollment $enrollment)
    {
        $enrollment->load(['student.user', 'section.course']);

        return view('dashboard.enrollments.edit', array_merge(
            ['enrollment' => $enrollment],
            $this->formData(),
        ));
    }

    public function update(UpdateEnrollmentRequest $request, Enrollment $enrollment)
    {
        $enrollment->update($request->validated());

        return redirect()
            ->route('dashboard.enrollments.index')
            ->with('success', 'Enrollment updated successfully.');
    }

    public function destroy(Enrollment $enrollment)
    {
        $enrollment->delete();

        return redirect()
            ->route('dashboard.enrollments.index')
            ->with('success', 'Enrollment deleted successfully.');
    }

    public function export(Request $request)
    {
        return Excel::download(
            new EnrollmentsExport(
                facultyId:    $this->scopedFacultyId(),
                departmentId: $this->scopedDepartmentId(),
                filters:      $request->only(['term_id', 'status']),
            ),
            'enrollments_' . now()->format('Y-m-d') . '.xlsx',
        );
    }

    private function formData(): array
    {
        $students = Student::with('user')
            ->whereHas('user', fn ($q) => $q->where('is_active', true))
            ->where('enrollment_status', 'active')
            ->get()
            ->sortBy(fn ($s) => $s->user->first_name . ' ' . $s->user->last_name);

        $sections = Section::with(['course', 'professor.user', 'academicTerm'])
            ->where('is_active', true)
            ->get()
            ->sortBy(fn ($s) => $s->course->code);

        $academicTerms = AcademicTerm::orderByDesc('start_date')->get();

        return compact('students', 'sections', 'academicTerms');
    }
}
