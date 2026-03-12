<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreDepartmentRequest;
use App\Http\Requests\Dashboard\UpdateDepartmentRequest;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    use Concerns\DashboardScopeAware;

    public function index(Request $request): View
    {
        $departments = Department::with(['faculty', 'head'])
            ->when($this->scopedFacultyId(), fn ($q, $id) => $q->where('faculty_id', $id))
            ->when($this->scopedDepartmentId(), fn ($q, $id) => $q->where('id', $id))
            ->when($request->filled('search'), fn ($q) => $q->where(function ($q) use ($request) {
                $q->whereIlike('name', '%' . $request->search . '%')
                  ->orWhereIlike('name_ar', '%' . $request->search . '%')
                  ->orWhereIlike('code', '%' . $request->search . '%');
            }))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->when($request->filled('faculty_id'), fn ($q) => $q->where('faculty_id', $request->faculty_id))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->when($request->boolean('no_head'), fn ($q) => $q->whereNull('head_id'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $nameCol   = app()->getLocale() === 'ar' ? 'name_ar' : 'name';
        $faculties = Faculty::orderBy('name')->pluck($nameCol, 'id');

        return view('dashboard.departments.index', compact('departments', 'faculties'));
    }

    public function show(Department $department): View
    {
        $department->load([
            'faculty',
            'head',
            'professors.user',
            'employees.user',
            'students.user',
            'courses',
        ]);

        return view('dashboard.departments.show', compact('department'));
    }

    public function createAcademic(): View
    {
        $faculties = Faculty::orderBy('name')->get();
        $professors = $this->activeProfessors();

        return view('dashboard.departments.create-academic', compact('faculties', 'professors'));
    }

    public function createManagerial(): View
    {
        $faculties = Faculty::orderBy('name')->get();
        $employees = $this->activeEmployees();

        return view('dashboard.departments.create-managerial', compact('faculties', 'employees'));
    }

    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        $dept = Department::create([
            'faculty_id'     => $request->faculty_id,
            'name'           => $request->name,
            'name_ar'        => $request->name_ar,
            'code'           => strtoupper($request->code),
            'logo_path'      => $request->hasFile('logo')
                ? $request->file('logo')->store('logos/departments', 'public')
                : null,
            'type'           => $request->type,
            'is_preparatory' => $request->type === 'academic' && $request->boolean('is_preparatory'),
            'head_id'        => $request->head_id,
            'is_active'      => $request->boolean('is_active'),
        ]);

        AuditLog::record(
            action: 'created',
            auditableType: 'Department',
            auditableId: $dept->id,
            description: "Created department {$dept->name}",
            newValues: $dept->only(['name', 'code', 'type', 'faculty_id', 'is_active']),
        );

        return redirect()->route('dashboard.departments.index')
            ->with('success', 'Department created successfully.');
    }

    public function edit(Department $department): View
    {
        $faculties = Faculty::orderBy('name')->get();

        if ($department->type === 'academic') {
            $professors = $this->activeProfessors();

            return view('dashboard.departments.edit', compact('department', 'faculties', 'professors'));
        }

        $employees = $this->activeEmployees();

        return view('dashboard.departments.edit', compact('department', 'faculties', 'employees'));
    }

    public function update(UpdateDepartmentRequest $request, Department $department): RedirectResponse
    {
        $oldValues = $department->only(['name', 'code', 'faculty_id', 'is_active']);

        $logoPath = $department->logo_path;

        if ($request->boolean('remove_logo') && $logoPath) {
            Storage::disk('public')->delete($logoPath);
            $logoPath = null;
        }

        if ($request->hasFile('logo')) {
            if ($logoPath) {
                Storage::disk('public')->delete($logoPath);
            }
            $logoPath = $request->file('logo')->store('logos/departments', 'public');
        }

        $department->update([
            'faculty_id'            => $request->faculty_id,
            'name'                  => $request->name,
            'name_ar'               => $request->name_ar,
            'code'                  => strtoupper($request->code),
            'logo_path'             => $logoPath,
            'is_preparatory'        => $department->type === 'academic' && $request->boolean('is_preparatory'),
            'head_id'               => $request->head_id,
            'is_active'             => $request->boolean('is_active'),
            'required_credit_hours' => $request->filled('required_credit_hours') ? (int) $request->required_credit_hours : null,
        ]);

        AuditLog::record(
            action: 'updated',
            auditableType: 'Department',
            auditableId: $department->id,
            description: "Updated department {$department->name}",
            oldValues: $oldValues,
            newValues: $department->only(['name', 'code', 'faculty_id', 'is_active']),
        );

        return redirect()->route('dashboard.departments.index')
            ->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        if ($department->is_mandatory) {
            return back()->withErrors(['delete' => 'This department is mandatory and cannot be deleted.']);
        }

        $name = $department->name;
        $id   = $department->id;

        try {
            $department->delete();
        } catch (\Illuminate\Database\QueryException) {
            return back()->withErrors(['delete' => 'This department cannot be deleted because it has associated records (professors, employees, students, or courses).']);
        }

        AuditLog::record(
            action: 'deleted',
            auditableType: 'Department',
            auditableId: $id,
            description: "Deleted department {$name}",
        );

        return redirect()->route('dashboard.departments.index')
            ->with('success', 'Department deleted successfully.');
    }

    private function activeProfessors()
    {
        return User::where('is_active', true)
            ->whereHas('roles', fn ($q) => $q->where('name', 'professor')->whereNull('role_user.revoked_at'))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    private function activeEmployees()
    {
        return User::where('is_active', true)
            ->whereHas('roles', fn ($q) => $q->where('name', 'employee')->whereNull('role_user.revoked_at'))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }
}
