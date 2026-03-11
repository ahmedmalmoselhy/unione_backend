<?php

namespace App\Http\Controllers\Dashboard;

use App\Exports\GradesExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreGradeRequest;
use App\Imports\GradesImport;
use Illuminate\Http\RedirectResponse;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\Dashboard\UpdateGradeRequest;
use App\Models\AcademicTerm;
use App\Models\AuditLog;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Student;
use App\Services\GpaService;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    use Concerns\DashboardScopeAware;

    public function index(Request $request)
    {
        $grades = Grade::with([
            'enrollment.student.user',
            'enrollment.section.course',
            'enrollment.academicTerm',
            'gradedBy',
        ])
            ->when($this->scopedFacultyId(), fn ($q, $id) => $q->whereHas('enrollment.student', fn ($s) => $s->where('faculty_id', $id)))
            ->when($this->scopedDepartmentId(), fn ($q, $id) => $q->whereHas('enrollment.student', fn ($s) => $s->where('department_id', $id)))
            ->when($request->filled('search'), fn ($q) => $q->whereHas('enrollment', function ($e) use ($request) {
                $e->whereHas('student.user', fn ($u) => $u->whereIlike('first_name', '%' . $request->search . '%')
                      ->orWhereIlike('last_name', '%' . $request->search . '%'))
                  ->orWhereHas('section.course', fn ($c) => $c->whereIlike('code', '%' . $request->search . '%')
                      ->orWhereIlike('name', '%' . $request->search . '%'));
            }))
            ->when($request->filled('term_id'), fn ($q) => $q->whereHas('enrollment', fn ($e) => $e->where('academic_term_id', $request->term_id)))
            ->when($request->filled('letter_grade'), fn ($q) => $q->where('letter_grade', $request->letter_grade))
            ->latest('graded_at')
            ->paginate(20)
            ->withQueryString();

        $nameCol = app()->getLocale() === 'ar' ? 'name_ar' : 'name';
        $terms   = AcademicTerm::orderByDesc('academic_year')->pluck($nameCol, 'id');

        return view('dashboard.grades.index', compact('grades', 'terms'));
    }

    public function show(Grade $grade)
    {
        $grade->load([
            'enrollment.student.user',
            'enrollment.section.course',
            'enrollment.section.professor.user',
            'enrollment.academicTerm',
            'gradedBy',
        ]);

        return view('dashboard.grades.show', compact('grade'));
    }

    public function create()
    {
        return view('dashboard.grades.create', $this->formData());
    }

    public function store(StoreGradeRequest $request)
    {
        $grade = Grade::create(array_merge($request->validated(), [
            'graded_by' => auth()->id(),
            'graded_at' => now(),
        ]));

        $grade->load('enrollment.student.user', 'enrollment.section.course');
        $studentName = $grade->enrollment->student->user->first_name . ' ' . $grade->enrollment->student->user->last_name;
        $courseName  = $grade->enrollment->section->course->name;

        AuditLog::record(
            action: 'created',
            auditableType: 'Grade',
            auditableId: $grade->id,
            description: "Recorded grade for {$studentName} in {$courseName}",
            newValues: $grade->only(['midterm', 'final', 'coursework', 'total', 'letter_grade', 'grade_points']),
        );

        $this->recalculateGpa($grade->enrollment->student_id);

        return redirect()
            ->route('dashboard.grades.index')
            ->with('success', 'Grade recorded successfully.');
    }

    public function edit(Grade $grade)
    {
        $grade->load(['enrollment.student.user', 'enrollment.section.course']);

        return view('dashboard.grades.edit', array_merge(
            ['grade' => $grade],
            $this->formData(),
        ));
    }

    public function update(UpdateGradeRequest $request, Grade $grade)
    {
        $grade->load('enrollment.student.user', 'enrollment.section.course');
        $oldValues   = $grade->only(['midterm', 'final', 'coursework', 'total', 'letter_grade', 'grade_points']);
        $studentName = $grade->enrollment->student->user->first_name . ' ' . $grade->enrollment->student->user->last_name;
        $courseName  = $grade->enrollment->section->course->name;

        $grade->update(array_merge($request->validated(), [
            'graded_by' => auth()->id(),
            'graded_at' => now(),
        ]));

        AuditLog::record(
            action: 'updated',
            auditableType: 'Grade',
            auditableId: $grade->id,
            description: "Updated grade for {$studentName} in {$courseName}",
            oldValues: $oldValues,
            newValues: $grade->only(['midterm', 'final', 'coursework', 'total', 'letter_grade', 'grade_points']),
        );

        $this->recalculateGpa($grade->enrollment->student_id);

        return redirect()
            ->route('dashboard.grades.index')
            ->with('success', 'Grade updated successfully.');
    }

    public function destroy(Grade $grade)
    {
        $grade->load('enrollment.student.user', 'enrollment.section.course');
        $studentId   = $grade->enrollment->student_id;
        $studentName = $grade->enrollment->student->user->first_name . ' ' . $grade->enrollment->student->user->last_name;
        $courseName  = $grade->enrollment->section->course->name;
        $oldValues   = $grade->only(['midterm', 'final', 'coursework', 'total', 'letter_grade', 'grade_points']);
        $gradeId     = $grade->id;

        $grade->delete();

        AuditLog::record(
            action: 'deleted',
            auditableType: 'Grade',
            auditableId: $gradeId,
            description: "Deleted grade for {$studentName} in {$courseName}",
            oldValues: $oldValues,
        );

        $this->recalculateGpa($studentId);

        return redirect()
            ->route('dashboard.grades.index')
            ->with('success', 'Grade deleted successfully.');
    }

    public function export(Request $request)
    {
        return Excel::download(
            new GradesExport(
                facultyId:    $this->scopedFacultyId(),
                departmentId: $this->scopedDepartmentId(),
                filters:      $request->only(['term_id', 'letter_grade']),
            ),
            'grades_' . now()->format('Y-m-d') . '.xlsx',
        );
    }

    public function importTemplate()
    {
        $headers = ['enrollment_id', 'midterm', 'coursework', 'final', 'total', 'letter_grade', 'grade_points'];
        $example = ['1', '85', '90', '88', '88', 'B+', '3.3'];

        return response()->streamDownload(function () use ($headers, $example) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            fputcsv($handle, $example);
            fclose($handle);
        }, 'grades_import_template.csv', ['Content-Type' => 'text/csv']);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:5120'],
        ]);

        $import = new GradesImport(
            scopedFacultyId:    $this->scopedFacultyId(),
            scopedDepartmentId: $this->scopedDepartmentId(),
        );

        Excel::import($import, $request->file('file'));

        $redirect = $import->importedCount > 0
            ? redirect()->route('dashboard.grades.index')
                  ->with('success', "{$import->importedCount} grades imported/updated successfully.")
            : back();

        if (! empty($import->importErrors)) {
            $redirect = $redirect->with('import_errors', $import->importErrors);
        }

        return $redirect;
    }

    private function recalculateGpa(int $studentId): void
    {
        GpaService::recalculate($studentId);
    }

    private function formData(): array
    {
        // Only enrollments that don't already have a grade
        $enrollments = Enrollment::with(['student.user', 'section.course', 'academicTerm'])
            ->whereDoesntHave('grade')
            ->whereIn('status', ['registered', 'completed', 'failed'])
            ->get()
            ->sortBy(fn ($e) => $e->student->user->first_name);

        return compact('enrollments');
    }
}
