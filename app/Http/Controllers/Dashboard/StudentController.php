<?php

namespace App\Http\Controllers\Dashboard;

use App\Exports\StudentsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreStudentRequest;
use App\Imports\StudentsImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\Dashboard\UpdateStudentRequest;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Student;
use App\Models\StudentDepartmentHistory;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class StudentController extends Controller
{
    use Concerns\DashboardScopeAware;

    public function index(Request $request): View
    {
        $students = Student::with(['user', 'faculty', 'department'])
            ->join('users', 'students.user_id', '=', 'users.id')
            ->when($this->scopedFacultyId(), fn ($q, $id) => $q->where('students.faculty_id', $id))
            ->when($this->scopedDepartmentId(), fn ($q, $id) => $q->where('students.department_id', $id))
            ->when($request->filled('search'), fn ($q) => $q->where(function ($q) use ($request) {
                $q->whereIlike('users.first_name', '%' . $request->search . '%')
                  ->orWhereIlike('users.last_name', '%' . $request->search . '%')
                  ->orWhereIlike('users.email', '%' . $request->search . '%')
                  ->orWhereIlike('students.student_number', '%' . $request->search . '%');
            }))
            ->when($request->filled('faculty_id'), fn ($q) => $q->where('students.faculty_id', $request->faculty_id))
            ->when($request->filled('enrollment_status'), fn ($q) => $q->where('students.enrollment_status', $request->enrollment_status))
            ->when($request->filled('status'), fn ($q) => $q->where('users.is_active', $request->status === 'active'))
            ->when($request->boolean('no_dept'), fn ($q) => $q->whereNull('students.department_id'))
            ->orderBy('users.first_name')
            ->orderBy('users.last_name')
            ->select('students.*')
            ->paginate(15)
            ->withQueryString();

        $faculties = Faculty::where('is_active', true)->orderBy('name')->pluck('name', 'id');

        return view('dashboard.students.index', compact('students', 'faculties'));
    }

    public function show(Student $student): View
    {
        $student->load([
            'user',
            'faculty',
            'department',
            'enrollments.section.course',
            'enrollments.section.academicTerm',
            'departmentHistory.fromDepartment',
            'departmentHistory.toDepartment',
        ]);

        $departments = Department::where('type', 'academic')
            ->where('is_active', true)
            ->where('id', '!=', $student->department_id)
            ->orderBy('name')
            ->get();

        return view('dashboard.students.show', compact('student', 'departments'));
    }

    public function transfer(Request $request, Student $student): RedirectResponse
    {
        $request->validate([
            'to_department_id' => ['required', 'integer', 'exists:departments,id'],
            'note'             => ['nullable', 'string', 'max:500'],
        ]);

        $fromDepartmentId = $student->department_id;

        StudentDepartmentHistory::create([
            'student_id'         => $student->id,
            'from_department_id' => $student->department_id,
            'to_department_id'   => $request->to_department_id,
            'switched_at'        => now(),
            'switched_by'        => auth()->id(),
            'note'               => $request->note,
        ]);

        $student->update(['department_id' => $request->to_department_id]);

        AuditLog::record(
            action: 'transferred',
            auditableType: 'Student',
            auditableId: $student->id,
            description: "Transferred student #{$student->id} between departments",
            oldValues: ['department_id' => $fromDepartmentId],
            newValues: ['department_id' => $request->to_department_id, 'note' => $request->note],
        );

        return redirect()->route('dashboard.students.show', $student)
            ->with('success', 'Student transferred to new department successfully.');
    }

    public function create(): View
    {
        return view('dashboard.students.create', $this->formData());
    }

    public function store(StoreStudentRequest $request): RedirectResponse
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
                'avatar_path'   => $request->hasFile('avatar')
                    ? $request->file('avatar')->store('avatars/students', 'public')
                    : null,
            ]);

            $student = Student::create([
                'user_id'           => $user->id,
                'student_number'    => $request->student_number,
                'faculty_id'        => $request->faculty_id,
                'department_id'     => $request->department_id,
                'academic_year'     => $request->academic_year,
                'semester'          => $request->semester,
                'enrollment_status' => $request->enrollment_status,
                'gpa'               => $request->gpa,
                'enrolled_at'       => $request->enrolled_at,
                'graduated_at'      => $request->graduated_at,
            ]);

            $roleId = DB::table('roles')->where('name', 'student')->value('id');

            DB::table('role_user')->insert([
                'user_id'    => $user->id,
                'role_id'    => $roleId,
                'granted_at' => now(),
            ]);

            AuditLog::record(
                action: 'created',
                auditableType: 'Student',
                auditableId: $student->id,
                description: "Created student {$user->first_name} {$user->last_name}",
                newValues: ['student_number' => $student->student_number, 'faculty_id' => $student->faculty_id, 'department_id' => $student->department_id, 'enrollment_status' => $student->enrollment_status],
            );
        });

        return redirect()->route('dashboard.students.index')
            ->with('success', 'Student created successfully.');
    }

    public function edit(Student $student): View
    {
        $student->load('user');

        return view('dashboard.students.edit', array_merge(
            compact('student'),
            $this->formData()
        ));
    }

    public function update(UpdateStudentRequest $request, Student $student): RedirectResponse
    {
        $student->load('user');
        $oldValues = [
            'name'              => $student->user->first_name . ' ' . $student->user->last_name,
            'email'             => $student->user->email,
            'student_number'    => $student->student_number,
            'faculty_id'        => $student->faculty_id,
            'department_id'     => $student->department_id,
            'enrollment_status' => $student->enrollment_status,
        ];

        DB::transaction(function () use ($request, $student) {
            $avatarPath = $student->user->avatar_path;

            if ($request->boolean('remove_avatar') && $avatarPath) {
                Storage::disk('public')->delete($avatarPath);
                $avatarPath = null;
            }

            if ($request->hasFile('avatar')) {
                if ($avatarPath) {
                    Storage::disk('public')->delete($avatarPath);
                }
                $avatarPath = $request->file('avatar')->store('avatars/students', 'public');
            }

            $userData = [
                'national_id'   => $request->national_id,
                'first_name'    => $request->first_name,
                'last_name'     => $request->last_name,
                'email'         => $request->email,
                'phone'         => $request->phone,
                'gender'        => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'is_active'     => $request->boolean('is_active'),
                'avatar_path'   => $avatarPath,
            ];

            if ($request->filled('password')) {
                $userData['password'] = $request->password;
            }

            $student->user->update($userData);

            $student->update([
                'student_number'    => $request->student_number,
                'faculty_id'        => $request->faculty_id,
                'department_id'     => $request->department_id,
                'academic_year'     => $request->academic_year,
                'semester'          => $request->semester,
                'enrollment_status' => $request->enrollment_status,
                'gpa'               => $request->gpa,
                'enrolled_at'       => $request->enrolled_at,
                'graduated_at'      => $request->graduated_at,
            ]);
        });

        AuditLog::record(
            action: 'updated',
            auditableType: 'Student',
            auditableId: $student->id,
            description: "Updated student {$request->first_name} {$request->last_name}",
            oldValues: $oldValues,
            newValues: ['name' => $request->first_name . ' ' . $request->last_name, 'email' => $request->email, 'student_number' => $request->student_number, 'faculty_id' => $request->faculty_id, 'department_id' => $request->department_id, 'enrollment_status' => $request->enrollment_status],
        );

        return redirect()->route('dashboard.students.index')
            ->with('success', 'Student updated successfully.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $name = $student->user->first_name . ' ' . $student->user->last_name;
        $id   = $student->id;

        try {
            $student->user->delete();
        } catch (\Illuminate\Database\QueryException) {
            return back()->withErrors(['delete' => 'This student cannot be deleted because they have associated records (enrollments, grades, etc.).']);
        }

        AuditLog::record(
            action: 'deleted',
            auditableType: 'Student',
            auditableId: $id,
            description: "Deleted student {$name}",
        );

        return redirect()->route('dashboard.students.index')
            ->with('success', 'Student deleted successfully.');
    }

    public function export(Request $request)
    {
        return Excel::download(
            new StudentsExport(
                facultyId:    $this->scopedFacultyId(),
                departmentId: $this->scopedDepartmentId(),
                filters:      $request->only(['faculty_id', 'enrollment_status']),
            ),
            'students_' . now()->format('Y-m-d') . '.xlsx',
        );
    }

    public function importTemplate()
    {
        $headers = [
            'national_id', 'first_name', 'last_name', 'email', 'phone',
            'gender', 'date_of_birth', 'student_number', 'faculty', 'department',
            'academic_year', 'semester', 'enrollment_status',
        ];
        $example = [
            '1234567890', 'John', 'Doe', 'john.doe@example.com', '+1234567890',
            'male', '1990-01-01', 'STU-001', 'Faculty of Engineering', 'Computer Science',
            '2', '1', 'active',
        ];

        return response()->streamDownload(function () use ($headers, $example) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            fputcsv($handle, $example);
            fclose($handle);
        }, 'students_import_template.csv', ['Content-Type' => 'text/csv']);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:5120'],
        ]);

        $import = new StudentsImport(
            scopedFacultyId:    $this->scopedFacultyId(),
            scopedDepartmentId: $this->scopedDepartmentId(),
        );

        Excel::import($import, $request->file('file'));

        $redirect = $import->importedCount > 0
            ? redirect()->route('dashboard.students.index')
                  ->with('success', "{$import->importedCount} students imported successfully.")
            : back();

        if (! empty($import->importErrors)) {
            $redirect = $redirect->with('import_errors', $import->importErrors);
        }

        return $redirect;
    }

    private function formData(): array
    {
        return [
            'faculties'   => Faculty::where('is_active', true)->orderBy('name')->get(),
            'departments' => Department::where('type', 'academic')
                ->where('is_active', true)
                ->with('faculty')
                ->orderBy('name')
                ->get(),
        ];
    }
}
