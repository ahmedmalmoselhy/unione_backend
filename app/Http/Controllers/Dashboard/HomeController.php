<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Faculty;
use App\Models\Professor;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        if ($user->isSystemAdmin()) {
            return $this->systemAdminHome();
        }

        if ($user->isFacultyAdmin()) {
            return $this->facultyAdminHome($user);
        }

        return $this->departmentAdminHome($user);
    }

    private function systemAdminHome(): View
    {
        $globalStats = [
            'faculties'   => Faculty::count(),
            'departments' => Department::count(),
            'students'    => Student::count(),
            'professors'  => Professor::count(),
            'employees'   => Employee::count(),
            'courses'     => Course::count(),
            'sections'    => Section::count(),
        ];

        $professorsByFaculty = DB::table('professors')
            ->join('departments', 'professors.department_id', '=', 'departments.id')
            ->selectRaw('departments.faculty_id, COUNT(*) as count')
            ->groupBy('departments.faculty_id')
            ->pluck('count', 'faculty_id');

        $faculties = Faculty::withCount([
            'departments',
            'students',
            'students as active_students_count'    => fn ($q) => $q->where('enrollment_status', 'active'),
            'students as suspended_students_count' => fn ($q) => $q->where('enrollment_status', 'suspended'),
            'students as graduated_students_count' => fn ($q) => $q->where('enrollment_status', 'graduated'),
            'students as withdrawn_students_count' => fn ($q) => $q->where('enrollment_status', 'withdrawn'),
        ])->orderBy('name')->get();

        return view('dashboard.home', [
            'role'                => 'system_admin',
            'globalStats'         => $globalStats,
            'faculties'           => $faculties,
            'professorsByFaculty' => $professorsByFaculty,
        ]);
    }

    private function facultyAdminHome($user): View
    {
        $facultyId = $user->scopedFacultyId();
        $faculty   = Faculty::findOrFail($facultyId);

        $deptIds = Department::where('faculty_id', $facultyId)->pluck('id');

        $stats = [
            'departments' => $deptIds->count(),
            'professors'  => Professor::whereIn('department_id', $deptIds)->count(),
            'employees'   => Employee::whereIn('department_id', $deptIds)->count(),
            'students'    => Student::where('faculty_id', $facultyId)->count(),
            'courses'     => Course::whereHas('departments', fn ($q) => $q->where('departments.faculty_id', $facultyId))->count(),
        ];

        $departments = Department::where('faculty_id', $facultyId)
            ->withCount(['professors', 'employees', 'students', 'courses'])
            ->orderBy('name')
            ->get();

        return view('dashboard.home', [
            'role'        => 'faculty_admin',
            'faculty'     => $faculty,
            'stats'       => $stats,
            'departments' => $departments,
        ]);
    }

    private function departmentAdminHome($user): View
    {
        $departmentId = $user->scopedDepartmentId();
        $department   = Department::with('faculty')->findOrFail($departmentId);

        $stats = [
            'professors' => Professor::where('department_id', $departmentId)->count(),
            'employees'  => Employee::where('department_id', $departmentId)->count(),
            'students'   => Student::where('department_id', $departmentId)->count(),
            'courses'    => Course::whereHas('departments', fn ($q) => $q->where('departments.id', $departmentId))->count(),
            'sections'   => Section::whereHas('course.departments', fn ($q) => $q->where('departments.id', $departmentId))->count(),
        ];

        $studentBreakdown = [
            'active'    => Student::where('department_id', $departmentId)->where('enrollment_status', 'active')->count(),
            'graduated' => Student::where('department_id', $departmentId)->where('enrollment_status', 'graduated')->count(),
            'suspended' => Student::where('department_id', $departmentId)->where('enrollment_status', 'suspended')->count(),
            'withdrawn' => Student::where('department_id', $departmentId)->where('enrollment_status', 'withdrawn')->count(),
        ];

        return view('dashboard.home', [
            'role'             => 'department_admin',
            'department'       => $department,
            'stats'            => $stats,
            'studentBreakdown' => $studentBreakdown,
        ]);
    }
}
