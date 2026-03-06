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

        return view('dashboard.home', compact('globalStats', 'faculties', 'professorsByFaculty'));
    }
}
