<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreAcademicTermRequest;
use App\Http\Requests\Dashboard\UpdateAcademicTermRequest;
use App\Models\AcademicTerm;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademicTermController extends Controller
{
    public function index(Request $request): View
    {
        $terms = AcademicTerm::query()
            ->when($request->filled('search'), fn ($q) => $q->where(function ($q) use ($request) {
                $q->whereIlike('name', '%' . $request->search . '%')
                  ->orWhereIlike('name_ar', '%' . $request->search . '%')
                  ->orWhereIlike('academic_year', '%' . $request->search . '%');
            }))
            ->when($request->filled('semester'), fn ($q) => $q->where('semester', $request->semester))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->orderByDesc('academic_year')
            ->orderByRaw("CASE semester WHEN 'summer' THEN 1 WHEN 'second' THEN 2 WHEN 'first' THEN 3 ELSE 4 END")
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
        $term = AcademicTerm::create([
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

        AuditLog::record(
            action: 'created',
            auditableType: 'AcademicTerm',
            auditableId: $term->id,
            description: "Created academic term {$term->name}",
            newValues: $term->only(['name', 'academic_year', 'semester', 'is_active']),
        );

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

        $oldValues = $academicTerm->only(['name', 'academic_year', 'semester', 'is_active']);

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

        AuditLog::record(
            action: 'updated',
            auditableType: 'AcademicTerm',
            auditableId: $academicTerm->id,
            description: "Updated academic term {$academicTerm->name}",
            oldValues: $oldValues,
            newValues: $academicTerm->only(['name', 'academic_year', 'semester', 'is_active']),
        );

        return redirect()->route('dashboard.academic-terms.index')
            ->with('success', 'Academic term updated successfully.');
    }

    public function destroy(AcademicTerm $academicTerm): RedirectResponse
    {
        $name = $academicTerm->name;
        $id   = $academicTerm->id;

        try {
            $academicTerm->delete();
        } catch (\Illuminate\Database\QueryException) {
            return back()->withErrors(['delete' => 'This academic term cannot be deleted because it has associated sections or enrollments.']);
        }

        AuditLog::record(
            action: 'deleted',
            auditableType: 'AcademicTerm',
            auditableId: $id,
            description: "Deleted academic term {$name}",
        );

        return redirect()->route('dashboard.academic-terms.index')
            ->with('success', 'Academic term deleted successfully.');
    }
}
