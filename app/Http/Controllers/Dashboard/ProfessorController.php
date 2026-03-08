<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreProfessorRequest;
use App\Http\Requests\Dashboard\UpdateProfessorRequest;
use App\Models\Department;
use App\Models\Professor;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProfessorController extends Controller
{
    use Concerns\DashboardScopeAware;

    public function index(Request $request): View
    {
        $professors = Professor::with(['user', 'department.faculty'])
            ->join('users', 'professors.user_id', '=', 'users.id')
            ->when($this->scopedFacultyId(), fn ($q, $id) => $q->whereHas('department', fn ($d) => $d->where('faculty_id', $id)))
            ->when($this->scopedDepartmentId(), fn ($q, $id) => $q->where('professors.department_id', $id))
            ->when($request->filled('search'), fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('users.first_name', 'ilike', '%' . $request->search . '%')
                  ->orWhere('users.last_name', 'ilike', '%' . $request->search . '%')
                  ->orWhere('users.email', 'ilike', '%' . $request->search . '%')
                  ->orWhere('professors.staff_number', 'ilike', '%' . $request->search . '%');
            }))
            ->when($request->filled('department_id'), fn ($q) => $q->where('professors.department_id', $request->department_id))
            ->when($request->filled('rank'), fn ($q) => $q->where('professors.academic_rank', $request->rank))
            ->when($request->filled('status'), fn ($q) => $q->where('users.is_active', $request->status === 'active'))
            ->orderBy('users.first_name')
            ->orderBy('users.last_name')
            ->select('professors.*')
            ->paginate(15)
            ->withQueryString();

        $departments = Department::where('type', 'academic')->orderBy('name')->pluck('name', 'id');

        return view('dashboard.professors.index', compact('professors', 'departments'));
    }

    public function show(Professor $professor): View
    {
        $professor->load(['user', 'department.faculty', 'sections.course']);

        return view('dashboard.professors.show', compact('professor'));
    }

    public function create(): View
    {
        $departments = $this->academicDepartments();

        return view('dashboard.professors.create', compact('departments'));
    }

    public function store(StoreProfessorRequest $request): RedirectResponse
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

            Professor::create([
                'user_id'         => $user->id,
                'staff_number'    => $request->staff_number,
                'department_id'   => $request->department_id,
                'specialization'  => $request->specialization,
                'academic_rank'   => $request->academic_rank,
                'office_location' => $request->office_location,
                'hired_at'        => $request->hired_at,
            ]);

            $roleId = DB::table('roles')->where('name', 'professor')->value('id');

            DB::table('role_user')->insert([
                'user_id'    => $user->id,
                'role_id'    => $roleId,
                'granted_at' => now(),
            ]);
        });

        return redirect()->route('dashboard.professors.index')
            ->with('success', 'Professor created successfully.');
    }

    public function edit(Professor $professor): View
    {
        $professor->load('user');
        $departments = $this->academicDepartments();

        return view('dashboard.professors.edit', compact('professor', 'departments'));
    }

    public function update(UpdateProfessorRequest $request, Professor $professor): RedirectResponse
    {
        DB::transaction(function () use ($request, $professor) {
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

            $professor->user->update($userData);

            $professor->update([
                'staff_number'    => $request->staff_number,
                'department_id'   => $request->department_id,
                'specialization'  => $request->specialization,
                'academic_rank'   => $request->academic_rank,
                'office_location' => $request->office_location,
                'hired_at'        => $request->hired_at,
            ]);
        });

        return redirect()->route('dashboard.professors.index')
            ->with('success', 'Professor updated successfully.');
    }

    public function destroy(Professor $professor): RedirectResponse
    {
        try {
            $professor->user->delete();
        } catch (\Illuminate\Database\QueryException) {
            return back()->withErrors(['delete' => 'This professor cannot be deleted because they have associated records (sections, dean assignment, or department head).']);
        }

        return redirect()->route('dashboard.professors.index')
            ->with('success', 'Professor deleted successfully.');
    }

    private function academicDepartments()
    {
        return Department::where('type', 'academic')
            ->with('faculty')
            ->orderBy('name')
            ->get();
    }
}
