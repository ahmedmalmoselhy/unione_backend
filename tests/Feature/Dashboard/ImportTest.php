<?php

use App\Imports\GradesImport;
use App\Imports\ProfessorsImport;
use App\Imports\StudentsImport;
use App\Models\AcademicTerm;
use App\Models\Course;
use App\Models\Department;
use App\Models\Enrollment;
use App\Models\Faculty;
use App\Models\Section;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;

// ── helper: write a temp CSV and return an UploadedFile ─────────────────────

function csvUpload(string $content, string $name = 'import.csv'): UploadedFile
{
    $tmp = tempnam(sys_get_temp_dir(), 'csv');
    file_put_contents($tmp, $content);

    return new UploadedFile($tmp, $name, 'text/csv', null, true);
}

// ── GradesImport ─────────────────────────────────────────────────────────────

test('grades import: valid CSV imports all rows', function () {
    $admin = createUserWithRole('admin');

    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture('GI1');
    ['student' => $studentA] = makeStudent($fac, $dept);
    ['student' => $studentB] = makeStudent($fac, $dept);
    ['professor' => $prof]   = makeProfessor($dept);

    $course = Course::create([
        'code'          => 'GIMP1',
        'name'          => 'Import Test Course',
        'name_ar'       => 'مادة',
        'credit_hours'  => 3,
        'lecture_hours' => 2,
        'level'         => 1,
        'is_elective'   => false,
        'is_active'     => true,
    ]);

    $term = AcademicTerm::create([
        'name'                   => 'Import Term',
        'name_ar'                => 'فصل',
        'academic_year'          => 9001,
        'semester'               => 'first',
        'starts_at'              => today()->subMonth()->toDateString(),
        'ends_at'                => today()->addMonths(3)->toDateString(),
        'registration_starts_at' => today()->subMonths(2)->toDateString(),
        'registration_ends_at'   => today()->subMonth()->toDateString(),
    ]);

    $section = Section::create([
        'course_id'        => $course->id,
        'professor_id'     => $prof->id,
        'academic_term_id' => $term->id,
        'capacity'         => 30,
        'is_active'        => true,
    ]);

    $enrollA = Enrollment::create([
        'student_id'       => $studentA->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'registered',
        'registered_at'    => now(),
    ]);
    $enrollB = Enrollment::create([
        'student_id'       => $studentB->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'registered',
        'registered_at'    => now(),
    ]);

    $csv = "enrollment_id,midterm,coursework,final,total,letter_grade,grade_points\n"
         . "{$enrollA->id},40,10,45,85,B,3.00\n"
         . "{$enrollB->id},35,10,40,78,C+,2.30\n";

    $this->actingAs($admin)
         ->post(route('dashboard.grades.import'), ['file' => csvUpload($csv)])
         ->assertRedirect(route('dashboard.grades.index'))
         ->assertSessionHas('success');

    $this->assertDatabaseHas('grades', ['enrollment_id' => $enrollA->id, 'letter_grade' => 'B']);
    $this->assertDatabaseHas('grades', ['enrollment_id' => $enrollB->id, 'letter_grade' => 'C+']);
});

test('grades import: valid rows are imported even when some rows have errors', function () {
    $admin = createUserWithRole('admin');

    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture('GI2');
    ['student' => $student] = makeStudent($fac, $dept);
    ['professor' => $prof]  = makeProfessor($dept);

    $course = Course::create([
        'code'          => 'GIMP2',
        'name'          => 'Partial Import Course',
        'name_ar'       => 'مادة',
        'credit_hours'  => 3,
        'lecture_hours' => 2,
        'level'         => 1,
        'is_elective'   => false,
        'is_active'     => true,
    ]);

    $term = AcademicTerm::create([
        'name'                   => 'Partial Import Term',
        'name_ar'                => 'فصل',
        'academic_year'          => 9002,
        'semester'               => 'first',
        'starts_at'              => today()->subMonth()->toDateString(),
        'ends_at'                => today()->addMonths(3)->toDateString(),
        'registration_starts_at' => today()->subMonths(2)->toDateString(),
        'registration_ends_at'   => today()->subMonth()->toDateString(),
    ]);

    $section = Section::create([
        'course_id'        => $course->id,
        'professor_id'     => $prof->id,
        'academic_term_id' => $term->id,
        'capacity'         => 30,
        'is_active'        => true,
    ]);

    $validEnrollment = Enrollment::create([
        'student_id'       => $student->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'registered',
        'registered_at'    => now(),
    ]);

    $invalidEnrollmentId = 99999; // does not exist

    $csv = "enrollment_id,midterm,coursework,final,total,letter_grade,grade_points\n"
         . "{$validEnrollment->id},40,10,45,85,B,3.00\n"     // valid
         . "{$invalidEnrollmentId},30,10,35,70,C,2.00\n";    // invalid (enrollment not found)

    $this->actingAs($admin)
         ->post(route('dashboard.grades.import'), ['file' => csvUpload($csv)])
         ->assertRedirect(route('dashboard.grades.index'))
         ->assertSessionHas('success')          // partial success message shown
         ->assertSessionHas('import_errors');   // errors reported too

    // Valid row was still imported
    $this->assertDatabaseHas('grades', [
        'enrollment_id' => $validEnrollment->id,
        'letter_grade'  => 'B',
    ]);
});

test('grades import: entirely invalid CSV goes back with errors only', function () {
    $admin = createUserWithRole('admin');

    $csv = "enrollment_id,midterm,final\n"
         . "99998,40,50\n"   // non-existent enrollment
         . "99997,30,40\n";  // non-existent enrollment

    $this->actingAs($admin)
         ->post(route('dashboard.grades.import'), ['file' => csvUpload($csv)])
         ->assertSessionHas('import_errors')
         ->assertSessionMissing('success');
});

// ── StudentsImport ────────────────────────────────────────────────────────────

test('students import: valid CSV imports all rows', function () {
    $admin = createUserWithRole('admin');

    $faculty = Faculty::create([
        'name'            => 'Import Faculty',
        'name_ar'         => 'كلية',
        'code'            => 'IMPF',
        'enrollment_type' => 'immediate',
        'is_active'       => true,
    ]);

    $dept = Department::create([
        'faculty_id' => $faculty->id,
        'name'       => 'Import Dept',
        'name_ar'    => 'قسم',
        'code'       => 'IMPD',
        'type'       => 'academic',
        'is_active'  => true,
    ]);

    $csv = "national_id,first_name,last_name,email,gender,student_number,faculty,department,academic_year,semester,enrollment_status\n"
         . "NID10001,John,Doe,john.doe.imp@example.com,male,SNUM0001,Import Faculty,Import Dept,1,first,active\n";

    $this->actingAs($admin)
         ->post(route('dashboard.students.import'), ['file' => csvUpload($csv, 'students.csv')])
         ->assertRedirect(route('dashboard.students.index'))
         ->assertSessionHas('success');

    $this->assertDatabaseHas('students', ['student_number' => 'SNUM0001']);
});

test('students import: duplicate email in file is reported as error, valid row still imported', function () {
    $admin = createUserWithRole('admin');

    $faculty = Faculty::create([
        'name'            => 'Dup Faculty',
        'name_ar'         => 'كلية',
        'code'            => 'DUPF',
        'enrollment_type' => 'immediate',
        'is_active'       => true,
    ]);

    Department::create([
        'faculty_id' => $faculty->id,
        'name'       => 'Dup Dept',
        'name_ar'    => 'قسم',
        'code'       => 'DUPD',
        'type'       => 'academic',
        'is_active'  => true,
    ]);

    $csv = "national_id,first_name,last_name,email,gender,student_number,faculty,department,academic_year,semester,enrollment_status\n"
         . "NID20001,Alice,Smith,alice.smith@example.com,female,SNUM0010,Dup Faculty,Dup Dept,1,first,active\n"
         . "NID20002,Bob,Jones,alice.smith@example.com,male,SNUM0011,Dup Faculty,Dup Dept,1,first,active\n"; // same email

    $this->actingAs($admin)
         ->post(route('dashboard.students.import'), ['file' => csvUpload($csv, 'students.csv')])
         ->assertSessionHas('import_errors');

    // First valid row should still be imported
    $this->assertDatabaseHas('students', ['student_number' => 'SNUM0010']);
    $this->assertDatabaseMissing('students', ['student_number' => 'SNUM0011']);
});

// ── ProfessorsImport ──────────────────────────────────────────────────────────

test('professors import: valid CSV imports all rows', function () {
    $admin = createUserWithRole('admin');

    $faculty = Faculty::create([
        'name'            => 'Prof Import Faculty',
        'name_ar'         => 'كلية',
        'code'            => 'PIMPF',
        'enrollment_type' => 'immediate',
        'is_active'       => true,
    ]);

    Department::create([
        'faculty_id' => $faculty->id,
        'name'       => 'Prof Import Dept',
        'name_ar'    => 'قسم',
        'code'       => 'PIMPD',
        'type'       => 'academic',
        'is_active'  => true,
    ]);

    $csv = "national_id,first_name,last_name,email,gender,staff_number,department,academic_rank,specialization\n"
         . "PNID10001,Jane,Prof,jane.prof@example.com,female,PSTF0001,Prof Import Dept,lecturer,Computer Science\n";

    $this->actingAs($admin)
         ->post(route('dashboard.professors.import'), ['file' => csvUpload($csv, 'professors.csv')])
         ->assertRedirect(route('dashboard.professors.index'))
         ->assertSessionHas('success');

    $this->assertDatabaseHas('professors', ['staff_number' => 'PSTF0001']);
});
