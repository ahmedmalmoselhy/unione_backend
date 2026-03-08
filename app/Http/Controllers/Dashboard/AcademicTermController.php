<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreAcademicTermRequest;
use App\Http\Requests\Dashboard\UpdateAcademicTermRequest;
use App\Models\AcademicTerm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademicTermController extends Controller
{
    public function index(Request $request): View
    {
        $terms = AcademicTerm::query()
            ->when($request->filled('search'), fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'ilike', '%' . $request->search . '%')
                  ->orWhere('name_ar', 'ilike', '%' . $request->search . '%')
                  ->orWhere('academic_year', 'ilike', '%' . $request->search . '%');
            }))
            ->when($request->filled('semester'), fn ($q) => $q->where('semester', $request->semester))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->orderByDesc('academic_year')
            ->orderByRaw("array_position(ARRAY['summer','second','first'], semester)")
            ->paginate(15)
            ->withQueryString();

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
