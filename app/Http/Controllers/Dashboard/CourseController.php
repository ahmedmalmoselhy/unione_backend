<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreCourseRequest;
use App\Http\Requests\Dashboard\UpdateCourseRequest;
use App\Models\Course;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CourseController extends Controller
{
    use Concerns\DashboardScopeAware;

    public function index(Request $request): View
    {
        $courses = Course::with('departments.faculty')
            ->when($this->scopedFacultyId(), fn ($q, $id) => $q->whereHas('departments', fn ($d) => $d->where('faculty_id', $id)))
            ->when($this->scopedDepartmentId(), fn ($q, $id) => $q->whereHas('departments', fn ($d) => $d->where('departments.id', $id)))
            ->when($request->filled('search'), fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('code', 'ilike', '%' . $request->search . '%')
                  ->orWhere('name', 'ilike', '%' . $request->search . '%')
                  ->orWhere('name_ar', 'ilike', '%' . $request->search . '%');
            }))
            ->when($request->filled('level'), fn ($q) => $q->where('level', $request->level))
            ->when($request->filled('is_elective'), fn ($q) => $q->where('is_elective', $request->is_elective === '1'))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->orderBy('code')
            ->paginate(15)
            ->withQueryString();

        return view('dashboard.courses.index', compact('courses'));
    }

    public function show(Course $course): View
    {
        $course->load(['departments.faculty', 'prerequisites', 'dependents', 'sections']);

        return view('dashboard.courses.show', compact('course'));
    }

    public function create(): View
    {
        $departments = $this->academicDepartments();
        $courses = Course::orderBy('code')->get(['id', 'code', 'name']);

        return view('dashboard.courses.create', compact('departments', 'courses'));
    }

    public function store(StoreCourseRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $course = Course::create([
                'code'          => $request->code,
                'name'          => $request->name,
                'name_ar'       => $request->name_ar,
                'description'   => $request->description,
                'credit_hours'  => $request->credit_hours,
                'lecture_hours' => $request->lecture_hours,
                'lab_hours'     => $request->lab_hours,
                'level'         => $request->level,
                'is_elective'   => $request->boolean('is_elective'),
            ]);

            $this->syncDepartments($course, $request->departments);

            if ($request->prerequisites) {
                $course->prerequisites()->sync($request->prerequisites);
            }
        });

        return redirect()->route('dashboard.courses.index')
            ->with('success', 'Course created successfully.');
    }

    public function edit(Course $course): View
    {
        $course->load(['departments', 'prerequisites']);
        $departments = $this->academicDepartments();
        $courses = Course::where('id', '!=', $course->id)->orderBy('code')->get(['id', 'code', 'name']);

        return view('dashboard.courses.edit', compact('course', 'departments', 'courses'));
    }

    public function update(UpdateCourseRequest $request, Course $course): RedirectResponse
    {
        DB::transaction(function () use ($request, $course) {
            $course->update([
                'code'          => $request->code,
                'name'          => $request->name,
                'name_ar'       => $request->name_ar,
                'description'   => $request->description,
                'credit_hours'  => $request->credit_hours,
                'lecture_hours' => $request->lecture_hours,
                'lab_hours'     => $request->lab_hours,
                'level'         => $request->level,
                'is_elective'   => $request->boolean('is_elective'),
                'is_active'     => $request->boolean('is_active'),
            ]);

            $this->syncDepartments($course, $request->departments);

            $prerequisites = $request->prerequisites
                ? array_filter($request->prerequisites, fn ($id) => $id != $course->id)
                : [];
            $course->prerequisites()->sync($prerequisites);
        });

        return redirect()->route('dashboard.courses.index')
            ->with('success', 'Course updated successfully.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        try {
            $course->delete();
        } catch (\Illuminate\Database\QueryException) {
            return back()->withErrors(['delete' => 'This course cannot be deleted because it has associated sections or is a prerequisite for other courses.']);
        }

        return redirect()->route('dashboard.courses.index')
            ->with('success', 'Course deleted successfully.');
    }

    private function academicDepartments()
    {
        return Department::where('type', 'academic')
            ->with('faculty')
            ->orderBy('name')
            ->get();
    }

    private function syncDepartments(Course $course, array $departments): void
    {
        $pivotData = [];
        foreach ($departments as $dept) {
            $pivotData[$dept['id']] = ['is_owner' => !empty($dept['is_owner'])];
        }
        $course->departments()->sync($pivotData);
    }
}
