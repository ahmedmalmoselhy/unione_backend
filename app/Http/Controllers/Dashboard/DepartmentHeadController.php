<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\Concerns\DashboardScopeAware;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentHeadController extends Controller
{
    use DashboardScopeAware;

    public function edit(Department $department): View
    {
        if ($this->isFacultyAdmin()) {
            $this->authorizeFaculty($department->faculty_id);
        }

        $department->load(['faculty', 'head']);

        // Professors and employees in this department are eligible
        $professors = \App\Models\Professor::with('user')
            ->where('department_id', $department->id)
            ->join('users', 'professors.user_id', '=', 'users.id')
            ->orderBy('users.first_name')
            ->select('professors.*')
            ->get();

        $employees = Employee::with('user')
            ->where('department_id', $department->id)
            ->join('users', 'employees.user_id', '=', 'users.id')
            ->orderBy('users.first_name')
            ->select('employees.*')
            ->get();

        return view('dashboard.departments.assign-head', compact('department', 'professors', 'employees'));
    }

    public function assign(Request $request, Department $department): RedirectResponse
    {
        if ($this->isFacultyAdmin()) {
            $this->authorizeFaculty($department->faculty_id);
        }

        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $oldHead = $department->head_id
            ? User::find($department->head_id)
            : null;

        $newHead = User::findOrFail($request->user_id);

        $department->update(['head_id' => $newHead->id]);

        AuditLog::record(
            action: 'assigned',
            auditableType: 'DepartmentHead',
            auditableId: $department->id,
            description: "Assigned {$newHead->first_name} {$newHead->last_name} as department head of {$department->name}",
            oldValues: $oldHead ? ['user_id' => $oldHead->id, 'name' => "{$oldHead->first_name} {$oldHead->last_name}"] : null,
            newValues: ['user_id' => $newHead->id, 'name' => "{$newHead->first_name} {$newHead->last_name}"],
        );

        return redirect()
            ->route('dashboard.departments.assign-head', $department)
            ->with('success', "{$newHead->first_name} {$newHead->last_name} has been assigned as department head.");
    }

    public function revoke(Department $department): RedirectResponse
    {
        if ($this->isFacultyAdmin()) {
            $this->authorizeFaculty($department->faculty_id);
        }

        $currentHead = $department->head_id ? User::find($department->head_id) : null;

        if (!$currentHead) {
            return redirect()
                ->route('dashboard.departments.assign-head', $department)
                ->with('success', 'No department head was assigned.');
        }

        $department->update(['head_id' => null]);

        AuditLog::record(
            action: 'revoked',
            auditableType: 'DepartmentHead',
            auditableId: $department->id,
            description: "Removed {$currentHead->first_name} {$currentHead->last_name} as department head of {$department->name}",
            oldValues: ['user_id' => $currentHead->id, 'name' => "{$currentHead->first_name} {$currentHead->last_name}"],
        );

        return redirect()
            ->route('dashboard.departments.assign-head', $department)
            ->with('success', "{$currentHead->first_name} {$currentHead->last_name} has been removed as department head.");
    }
}
