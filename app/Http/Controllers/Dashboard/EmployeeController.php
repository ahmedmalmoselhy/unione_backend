<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreEmployeeRequest;
use App\Http\Requests\Dashboard\UpdateEmployeeRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(): View
    {
        $employees = Employee::with(['user', 'department.faculty'])
            ->join('users', 'employees.user_id', '=', 'users.id')
            ->orderBy('users.first_name')
            ->orderBy('users.last_name')
            ->select('employees.*')
            ->paginate(15);

        return view('dashboard.employees.index', compact('employees'));
    }

    public function show(Employee $employee): View
    {
        $employee->load(['user', 'department.faculty']);

        return view('dashboard.employees.show', compact('employee'));
    }

    public function create(): View
    {
        $departments = $this->managerialDepartments();

        return view('dashboard.employees.create', compact('departments'));
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $user = User::create([
                'national_id'   => $request->national_id,
                'first_name'    => $request->first_name,
                'last_name'     => $request->last_name,
                'email'         => $request->email,
                'password'      => $request->password,
                'phone'         => $request->phone,
                'gender'        => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'is_active'     => true,
            ]);

            Employee::create([
                'user_id'         => $user->id,
                'staff_number'    => $request->staff_number,
                'department_id'   => $request->department_id,
                'job_title'       => $request->job_title,
                'employment_type' => $request->employment_type,
                'salary'          => $request->salary,
                'hired_at'        => $request->hired_at,
            ]);

            $roleId = DB::table('roles')->where('name', 'employee')->value('id');

            DB::table('role_user')->insert([
                'user_id'    => $user->id,
                'role_id'    => $roleId,
                'granted_at' => now(),
            ]);
        });

        return redirect()->route('dashboard.employees.index')
            ->with('success', 'Employee created successfully.');
    }

    public function edit(Employee $employee): View
    {
        $employee->load('user');
        $departments = $this->managerialDepartments();

        return view('dashboard.employees.edit', compact('employee', 'departments'));
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        DB::transaction(function () use ($request, $employee) {
            $userData = [
                'national_id'   => $request->national_id,
                'first_name'    => $request->first_name,
                'last_name'     => $request->last_name,
                'email'         => $request->email,
                'phone'         => $request->phone,
                'gender'        => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'is_active'     => $request->boolean('is_active'),
            ];

            if ($request->filled('password')) {
                $userData['password'] = $request->password;
            }

            $employee->user->update($userData);

            $employee->update([
                'staff_number'    => $request->staff_number,
                'department_id'   => $request->department_id,
                'job_title'       => $request->job_title,
                'employment_type' => $request->employment_type,
                'salary'          => $request->salary,
                'hired_at'        => $request->hired_at,
                'terminated_at'   => $request->terminated_at,
            ]);
        });

        return redirect()->route('dashboard.employees.index')
            ->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        try {
            $employee->user->delete();
        } catch (\Illuminate\Database\QueryException) {
            return back()->withErrors(['delete' => 'This employee cannot be deleted because they have associated records (department head assignment).']);
        }

        return redirect()->route('dashboard.employees.index')
            ->with('success', 'Employee deleted successfully.');
    }

    private function managerialDepartments()
    {
        return Department::where('type', 'managerial')
            ->with('faculty')
            ->orderBy('name')
            ->get();
    }
}
