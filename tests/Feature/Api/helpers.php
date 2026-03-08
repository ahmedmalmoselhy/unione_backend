<?php

use App\Models\Department;
use App\Models\Faculty;
use App\Models\Professor;
use App\Models\Student;

/**
 * Build a minimal faculty + academic department fixture.
 * Returns ['faculty' => Faculty, 'department' => Department]
 */
function makeFacultyDeptFixture(string $suffix = ''): array
{
    static $counter = 0;
    $counter++;

    $faculty = Faculty::create([
        'name'            => "Faculty {$counter}{$suffix}",
        'name_ar'         => "كلية {$counter}",
        'code'            => "FAC{$counter}{$suffix}",
        'enrollment_type' => 'immediate',
        'is_active'       => true,
    ]);

    $department = Department::create([
        'faculty_id' => $faculty->id,
        'name'       => "Dept {$counter}{$suffix}",
        'name_ar'    => "قسم {$counter}",
        'code'       => "DEP{$counter}{$suffix}",
        'type'       => 'academic',
        'is_active'  => true,
    ]);

    return compact('faculty', 'department');
}

/**
 * Create a student user with the given faculty/department.
 */
function makeStudent(Faculty $faculty, Department $department): array
{
    static $sNum = 0;
    $sNum++;

    $user    = createUserWithRole('student');
    $student = Student::create([
        'user_id'           => $user->id,
        'student_number'    => "S{$sNum}",
        'faculty_id'        => $faculty->id,
        'department_id'     => $department->id,
        'academic_year'     => 1,
        'semester'          => 'first',
        'enrollment_status' => 'active',
        'enrolled_at'       => now()->toDateString(),
    ]);

    return compact('user', 'student');
}

/**
 * Create a professor user attached to a department.
 */
function makeProfessor(Department $department): array
{
    static $pNum = 0;
    $pNum++;

    $user      = createUserWithRole('professor');
    $professor = Professor::create([
        'user_id'        => $user->id,
        'staff_number'   => "P{$pNum}",
        'department_id'  => $department->id,
        'specialization' => 'Computer Science',
        'academic_rank'  => 'assistant_professor',
        'hired_at'       => now()->toDateString(),
    ]);

    return compact('user', 'professor');
}
