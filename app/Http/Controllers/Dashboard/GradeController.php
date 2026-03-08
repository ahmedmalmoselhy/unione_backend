<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreGradeRequest;
use App\Http\Requests\Dashboard\UpdateGradeRequest;
use App\Models\Enrollment;
use App\Models\Grade;

class GradeController extends Controller
{
    public function index()
    {
        $grades = Grade::with([
            'enrollment.student.user',
            'enrollment.section.course',
            'enrollment.academicTerm',
            'gradedBy',
        ])
            ->latest('graded_at')
            ->paginate(20);

        return view('dashboard.grades.index', compact('grades'));
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
        Grade::create(array_merge($request->validated(), [
            'graded_by' => auth()->id(),
            'graded_at' => now(),
        ]));

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

        return redirect()
            ->route('dashboard.grades.index')
            ->with('success', 'Grade updated successfully.');
    }

    public function destroy(Grade $grade)
    {
        $grade->delete();

        return redirect()
            ->route('dashboard.grades.index')
            ->with('success', 'Grade deleted successfully.');
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
