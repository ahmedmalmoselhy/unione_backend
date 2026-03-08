<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreGradeRequest;
use App\Http\Requests\Dashboard\UpdateGradeRequest;
use App\Models\AcademicTerm;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
                $e->whereHas('student.user', fn ($u) => $u->where('first_name', 'ilike', '%' . $request->search . '%')
                      ->orWhere('last_name', 'ilike', '%' . $request->search . '%'))
                  ->orWhereHas('section.course', fn ($c) => $c->where('code', 'ilike', '%' . $request->search . '%')
                      ->orWhere('name', 'ilike', '%' . $request->search . '%'));
            }))
            ->when($request->filled('term_id'), fn ($q) => $q->whereHas('enrollment', fn ($e) => $e->where('academic_term_id', $request->term_id)))
            ->when($request->filled('letter_grade'), fn ($q) => $q->where('letter_grade', $request->letter_grade))
            ->latest('graded_at')
            ->paginate(20)
            ->withQueryString();

        $terms = AcademicTerm::orderByDesc('academic_year')->pluck('name', 'id');

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
        $grade->update(array_merge($request->validated(), [
            'graded_by' => auth()->id(),
            'graded_at' => now(),
        ]));

        $this->recalculateGpa($grade->enrollment->student_id);

        return redirect()
            ->route('dashboard.grades.index')
            ->with('success', 'Grade updated successfully.');
    }

    public function destroy(Grade $grade)
    {
        $studentId = $grade->enrollment->student_id;
        $grade->delete();

        $this->recalculateGpa($studentId);

        return redirect()
            ->route('dashboard.grades.index')
            ->with('success', 'Grade deleted successfully.');
    }

    private function recalculateGpa(int $studentId): void
    {
        $result = DB::table('grades')
            ->join('enrollments', 'grades.enrollment_id', '=', 'enrollments.id')
            ->join('sections', 'enrollments.section_id', '=', 'sections.id')
            ->join('courses', 'sections.course_id', '=', 'courses.id')
            ->where('enrollments.student_id', $studentId)
            ->whereNotNull('grades.grade_points')
            ->where('courses.credit_hours', '>', 0)
            ->selectRaw('SUM(grades.grade_points * courses.credit_hours) as weighted_sum, SUM(courses.credit_hours) as total_credits')
            ->first();

        $gpa = ($result && $result->total_credits > 0)
            ? round($result->weighted_sum / $result->total_credits, 2)
            : null;

        Student::where('id', $studentId)->update(['gpa' => $gpa]);
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
