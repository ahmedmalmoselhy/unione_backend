<?php

use App\Models\AcademicTerm;
use App\Models\Course;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Professor;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

/**
 * Create an AcademicTerm whose registration window is currently open.
 */
function makeOpenTerm(string $suffix = ''): AcademicTerm
{
    static $t = 0;
    $t++;

    return AcademicTerm::create([
        'name'                   => "Open Term {$t}{$suffix}",
        'name_ar'                => "فصل مفتوح {$t}",
        'academic_year'          => 2026,
        'semester'               => 'first',
        'starts_at'              => today()->subMonth()->toDateString(),
        'ends_at'                => today()->addMonths(4)->toDateString(),
        'registration_starts_at' => today()->subDays(10)->toDateString(),
        'registration_ends_at'   => today()->addDays(10)->toDateString(),
        'is_active'              => true,
    ]);
}

/**
 * Create an AcademicTerm whose registration window has closed.
 */
function makeClosedTerm(string $suffix = ''): AcademicTerm
{
    static $c = 0;
    $c++;

    return AcademicTerm::create([
        'name'                   => "Closed Term {$c}{$suffix}",
        'name_ar'                => "فصل مغلق {$c}",
        'academic_year'          => 2025,
        'semester'               => 'second',
        'starts_at'              => today()->subMonths(6)->toDateString(),
        'ends_at'                => today()->subMonths(2)->toDateString(),
        'registration_starts_at' => today()->subMonths(7)->toDateString(),
        'registration_ends_at'   => today()->subMonths(6)->toDateString(),
        'is_active'              => false,
    ]);
}

/**
 * Create an active Section for the given term, with its own course and professor.
 */
function makeSection(AcademicTerm $term, int $capacity = 30): Section
{
    static $sc = 0;
    $sc++;

    $course = Course::create([
        'code'          => "CRS{$sc}",
        'name'          => "Course {$sc}",
        'name_ar'       => "مادة {$sc}",
        'credit_hours'  => 3,
        'lecture_hours' => 2,
        'level'         => 1,
        'is_elective'   => false,
        'is_active'     => true,
    ]);

    ['department' => $dept] = makeFacultyDeptFixture("SC{$sc}");
    ['professor' => $prof]  = makeProfessor($dept);

    return Section::create([
        'course_id'        => $course->id,
        'professor_id'     => $prof->id,
        'academic_term_id' => $term->id,
        'capacity'         => $capacity,
        'is_active'        => true,
    ]);
}

/**
 * Insert a database notification for a user and return its UUID.
 */
function makeDbNotification(\App\Models\User $user): string
{
    $id = (string) Str::uuid();

    DB::table('notifications')->insert([
        'id'              => $id,
        'type'            => 'App\\Notifications\\TestNotification',
        'notifiable_type' => 'App\\Models\\User',
        'notifiable_id'   => $user->id,
        'data'            => json_encode(['title' => 'Test', 'body' => 'Test notification']),
        'read_at'         => null,
        'created_at'      => now(),
        'updated_at'      => now(),
    ]);

    return $id;
}
