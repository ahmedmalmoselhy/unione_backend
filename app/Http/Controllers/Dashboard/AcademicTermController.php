<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreAcademicTermRequest;
use App\Http\Requests\Dashboard\UpdateAcademicTermRequest;
use App\Models\AcademicTerm;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AcademicTermController extends Controller
{
    public function index(): View
    {
        $terms = AcademicTerm::orderByDesc('academic_year')
            ->orderByRaw("FIELD(semester, 'summer', 'second', 'first')")
            ->paginate(15);

        return view('dashboard.academic-terms.index', compact('terms'));
    }

    public function show(AcademicTerm $academicTerm): View
    {
        $academicTerm->load(['sections.course', 'sections.professor.user']);

        return view('dashboard.academic-terms.show', compact('academicTerm'));
    }

    public function create(): View
    {
        return view('dashboard.academic-terms.create');
    }

    public function store(StoreAcademicTermRequest $request): RedirectResponse
    {
        AcademicTerm::create([
            'name'                     => $request->name,
            'name_ar'                  => $request->name_ar,
            'academic_year'            => $request->academic_year,
            'semester'                 => $request->semester,
            'starts_at'               => $request->starts_at,
            'ends_at'                 => $request->ends_at,
            'registration_starts_at'  => $request->registration_starts_at,
            'registration_ends_at'    => $request->registration_ends_at,
            'withdrawal_deadline'     => $request->withdrawal_deadline,
            'exam_starts_at'          => $request->exam_starts_at,
            'exam_ends_at'            => $request->exam_ends_at,
            'grade_submission_deadline' => $request->grade_submission_deadline,
        ]);

        return redirect()->route('dashboard.academic-terms.index')
            ->with('success', 'Academic term created successfully.');
    }

    public function edit(AcademicTerm $academicTerm): View
    {
        return view('dashboard.academic-terms.edit', compact('academicTerm'));
    }

    public function update(UpdateAcademicTermRequest $request, AcademicTerm $academicTerm): RedirectResponse
    {
        // If activating this term, deactivate all others
        if ($request->boolean('is_active') && !$academicTerm->is_active) {
            AcademicTerm::where('id', '!=', $academicTerm->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        $academicTerm->update([
            'name'                     => $request->name,
            'name_ar'                  => $request->name_ar,
            'academic_year'            => $request->academic_year,
            'semester'                 => $request->semester,
            'starts_at'               => $request->starts_at,
            'ends_at'                 => $request->ends_at,
            'registration_starts_at'  => $request->registration_starts_at,
            'registration_ends_at'    => $request->registration_ends_at,
            'withdrawal_deadline'     => $request->withdrawal_deadline,
            'exam_starts_at'          => $request->exam_starts_at,
            'exam_ends_at'            => $request->exam_ends_at,
            'grade_submission_deadline' => $request->grade_submission_deadline,
            'is_active'               => $request->boolean('is_active'),
        ]);

        return redirect()->route('dashboard.academic-terms.index')
            ->with('success', 'Academic term updated successfully.');
    }

    public function destroy(AcademicTerm $academicTerm): RedirectResponse
    {
        try {
            $academicTerm->delete();
        } catch (\Illuminate\Database\QueryException) {
            return back()->withErrors(['delete' => 'This academic term cannot be deleted because it has associated sections or enrollments.']);
        }

        return redirect()->route('dashboard.academic-terms.index')
            ->with('success', 'Academic term deleted successfully.');
    }
}
