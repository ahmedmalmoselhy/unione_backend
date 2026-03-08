<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\Concerns\DashboardScopeAware;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Faculty;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminAssignmentController extends Controller
{
    use DashboardScopeAware;

    // ── Faculty admin assignment (system admin only) ──────────────────────────

    public function editFacultyAdmin(Faculty $faculty): View
    {
        // Employees who belong to this faculty's departments
        $employees = Employee::with('user')
            ->whereHas('department', fn ($q) => $q->where('faculty_id', $faculty->id))
            ->join('users', 'employees.user_id', '=', 'users.id')
            ->orderBy('users.first_name')
            ->select('employees.*')
            ->get();

        // Current faculty admin for this faculty
        $currentAdmin = User::whereHas('roles', fn ($q) =>
            $q->where('roles.name', 'faculty_admin')
              ->wherePivotNull('revoked_at')
              ->where('role_user.faculty_id', $faculty->id)
        )->first();

        return view('dashboard.admin-assignment.faculty', compact('faculty', 'employees', 'currentAdmin'));
    }

    public function assignFacultyAdmin(Request $request, Faculty $faculty): RedirectResponse
    {
        $request->validate([
            'employee_user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $roleId = DB::table('roles')->where('name', 'faculty_admin')->value('id');

        DB::transaction(function () use ($request, $faculty, $roleId) {
            // Revoke existing faculty_admin for this faculty
            DB::table('role_user')
                ->where('role_id', $roleId)
                ->where('faculty_id', $faculty->id)
                ->whereNull('revoked_at')
                ->update(['revoked_at' => now()]);

            // Assign new admin
            DB::table('role_user')->insert([
                'user_id'    => $request->employee_user_id,
                'role_id'    => $roleId,
                'faculty_id' => $faculty->id,
                'granted_at' => now(),
            ]);

            // Force password reset on next login
            User::where('id', $request->employee_user_id)->update(['must_change_password' => true]);
        });

        return redirect()->route('dashboard.faculties.assign-admin', $faculty)
            ->with('success', 'Faculty administrator assigned successfully. They will be required to set a new password on next login.');
    }

    public function revokeFacultyAdmin(Faculty $faculty): RedirectResponse
    {
        $roleId = DB::table('roles')->where('name', 'faculty_admin')->value('id');

        DB::table('role_user')
            ->where('role_id', $roleId)
            ->where('faculty_id', $faculty->id)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);

        return redirect()->route('dashboard.faculties.assign-admin', $faculty)
            ->with('success', 'Faculty administrator role revoked.');
    }

    // ── Department admin assignment (system admin + faculty admin) ────────────

    public function editDepartmentAdmin(Department $department): View
    {
        // Faculty admin can only manage departments in their faculty
        if ($this->isFacultyAdmin()) {
            $this->authorizeDepartment($department->id);
        }

        // Employees in this department
        $employees = Employee::with('user')
            ->where('department_id', $department->id)
            ->join('users', 'employees.user_id', '=', 'users.id')
            ->orderBy('users.first_name')
            ->select('employees.*')
            ->get();

        // Current department admin for this department
        $currentAdmin = User::whereHas('roles', fn ($q) =>
            $q->where('roles.name', 'department_admin')
              ->wherePivotNull('revoked_at')
              ->where('role_user.department_id', $department->id)
        )->first();

        $department->load('faculty');

        return view('dashboard.admin-assignment.department', compact('department', 'employees', 'currentAdmin'));
    }

    public function assignDepartmentAdmin(Request $request, Department $department): RedirectResponse
    {
        if ($this->isFacultyAdmin()) {
            $this->authorizeDepartment($department->id);
        }

        $request->validate([
            'employee_user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $roleId = DB::table('roles')->where('name', 'department_admin')->value('id');

        DB::transaction(function () use ($request, $department, $roleId) {
            // Revoke existing department_admin for this department
            DB::table('role_user')
                ->where('role_id', $roleId)
                ->where('department_id', $department->id)
                ->whereNull('revoked_at')
                ->update(['revoked_at' => now()]);

            // Assign new admin
            DB::table('role_user')->insert([
                'user_id'       => $request->employee_user_id,
                'role_id'       => $roleId,
                'department_id' => $department->id,
                'granted_at'    => now(),
            ]);

            // Force password reset on next login
            User::where('id', $request->employee_user_id)->update(['must_change_password' => true]);
        });

        return redirect()->route('dashboard.departments.assign-admin', $department)
            ->with('success', 'Department administrator assigned successfully. They will be required to set a new password on next login.');
    }

    public function revokeDepartmentAdmin(Department $department): RedirectResponse
    {
        if ($this->isFacultyAdmin()) {
            $this->authorizeDepartment($department->id);
        }

        $roleId = DB::table('roles')->where('name', 'department_admin')->value('id');

        DB::table('role_user')
            ->where('role_id', $roleId)
            ->where('department_id', $department->id)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);

        return redirect()->route('dashboard.departments.assign-admin', $department)
            ->with('success', 'Department administrator role revoked.');
    }
}
