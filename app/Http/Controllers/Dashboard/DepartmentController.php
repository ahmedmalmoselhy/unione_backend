<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreDepartmentRequest;
use App\Http\Requests\Dashboard\UpdateDepartmentRequest;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(): View
    {
        $departments = Department::with(['faculty', 'head'])->orderBy('name')->paginate(15);

        return view('dashboard.departments.index', compact('departments'));
    }

    public function createAcademic(): View
    {
        $faculties  = Faculty::orderBy('name')->get();
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
        Department::create([
            'faculty_id'     => $request->faculty_id,
            'name'           => $request->name,
            'name_ar'        => $request->name_ar,
            'code'           => strtoupper($request->code),
            'type'           => $request->type,
            'is_preparatory' => $request->type === 'academic' && $request->boolean('is_preparatory'),
            'head_id'        => $request->head_id,
            'is_active'      => $request->boolean('is_active'),
        ]);

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
        $department->update([
            'faculty_id'     => $request->faculty_id,
            'name'           => $request->name,
            'name_ar'        => $request->name_ar,
            'code'           => strtoupper($request->code),
            'is_preparatory' => $department->type === 'academic' && $request->boolean('is_preparatory'),
            'head_id'        => $request->head_id,
            'is_active'      => $request->boolean('is_active'),
        ]);

        return redirect()->route('dashboard.departments.index')
            ->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        if ($department->is_mandatory) {
            return back()->withErrors(['delete' => 'This department is mandatory and cannot be deleted.']);
        }

        try {
            $department->delete();
        } catch (\Illuminate\Database\QueryException) {
            return back()->withErrors(['delete' => 'This department cannot be deleted because it has associated records (professors, employees, students, or courses).']);
        }

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
