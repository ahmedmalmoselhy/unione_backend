<?php

use App\Models\AcademicTerm;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Section;
use App\Models\Student;

// ── fixture helper ───────────────────────────────────────────────────────────

function makeGradeTestFixture(): array
{
    static $n = 0;
    $n++;

    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture("GRC{$n}");
    ['student' => $student] = makeStudent($fac, $dept);
    ['professor' => $prof]  = makeProfessor($dept);

    $course = Course::create([
        'code'          => "GCO{$n}",
        'name'          => "Grade Course {$n}",
        'name_ar'       => "مادة {$n}",
        'credit_hours'  => 3,
        'lecture_hours' => 2,
        'level'         => 1,
        'is_elective'   => false,
        'is_active'     => true,
    ]);

    $term = AcademicTerm::create([
        'name'                   => "GradeTerm {$n}",
        'name_ar'                => "فصل {$n}",
        'academic_year'          => 2000 + $n,
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

    $enrollment = Enrollment::create([
        'student_id'       => $student->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'registered',
        'registered_at'    => now(),
    ]);

    return compact('student', 'enrollment', 'course', 'section', 'term');
}

// ── store ────────────────────────────────────────────────────────────────────

test('admin can store a grade record', function () {
    $admin = createUserWithRole('admin');
    ['enrollment' => $enrollment] = makeGradeTestFixture();

    $this->actingAs($admin)
         ->post(route('dashboard.grades.store'), [
             'enrollment_id' => $enrollment->id,
             'midterm'       => 40,
             'final'         => 50,
             'coursework'    => 10,
             'total'         => 85,
             'letter_grade'  => 'B',
             'grade_points'  => 3.00,
         ])
         ->assertRedirect(route('dashboard.grades.index'));

    $this->assertDatabaseHas('grades', [
        'enrollment_id' => $enrollment->id,
        'letter_grade'  => 'B',
    ]);
});

test('storing a grade recalculates student GPA', function () {
    $admin = createUserWithRole('admin');
    ['student' => $student, 'enrollment' => $enrollment] = makeGradeTestFixture();

    $this->actingAs($admin)
         ->post(route('dashboard.grades.store'), [
             'enrollment_id' => $enrollment->id,
             'total'         => 90,
             'letter_grade'  => 'A',
             'grade_points'  => 4.00,
         ]);

    // GPA = (4.00 * 3 credit_hours) / 3 = 4.00
    $fresh = Student::find($student->id);
    expect((float) $fresh->gpa)->toBe(4.0);
    expect($fresh->academic_standing)->toBe('good_standing');
});

test('employee cannot store grades', function () {
    $emp = createUserWithRole('employee');
    ['enrollment' => $enrollment] = makeGradeTestFixture();

    $this->actingAs($emp)
         ->post(route('dashboard.grades.store'), [
             'enrollment_id' => $enrollment->id,
             'grade_points'  => 3.0,
         ])
         ->assertForbidden();
});

// ── update ───────────────────────────────────────────────────────────────────

test('admin can update an existing grade', function () {
    $admin = createUserWithRole('admin');
    ['enrollment' => $enrollment] = makeGradeTestFixture();

    $grade = Grade::create([
        'enrollment_id' => $enrollment->id,
        'midterm'       => 30,
        'final'         => 40,
        'total'         => 60,
        'letter_grade'  => 'C',
        'grade_points'  => 2.00,
        'graded_by'     => $admin->id,
        'graded_at'     => now(),
    ]);

    $this->actingAs($admin)
         ->put(route('dashboard.grades.update', $grade), [
             'enrollment_id' => $enrollment->id,
             'total'         => 88,
             'letter_grade'  => 'B+',
             'grade_points'  => 3.30,
         ])
         ->assertRedirect(route('dashboard.grades.index'));

    $this->assertDatabaseHas('grades', ['id' => $grade->id, 'letter_grade' => 'B+']);
});

test('updating a grade recalculates student GPA', function () {
    $admin = createUserWithRole('admin');
    ['student' => $student, 'enrollment' => $enrollment] = makeGradeTestFixture();

    $grade = Grade::create([
        'enrollment_id' => $enrollment->id,
        'total'         => 60,
        'letter_grade'  => 'C',
        'grade_points'  => 2.00,
        'graded_by'     => $admin->id,
        'graded_at'     => now(),
    ]);

    $this->actingAs($admin)
         ->put(route('dashboard.grades.update', $grade), [
             'enrollment_id' => $enrollment->id,
             'total'         => 95,
             'letter_grade'  => 'A+',
             'grade_points'  => 4.00,
         ]);

    $fresh = Student::find($student->id);
    expect((float) $fresh->gpa)->toBe(4.0);
    expect($fresh->academic_standing)->toBe('good_standing');
});

// ── destroy ──────────────────────────────────────────────────────────────────

test('admin can delete a grade record', function () {
    $admin = createUserWithRole('admin');
    ['enrollment' => $enrollment] = makeGradeTestFixture();

    $grade = Grade::create([
        'enrollment_id' => $enrollment->id,
        'total'         => 70,
        'letter_grade'  => 'B',
        'grade_points'  => 3.00,
        'graded_by'     => $admin->id,
        'graded_at'     => now(),
    ]);

    $this->actingAs($admin)
         ->delete(route('dashboard.grades.destroy', $grade))
         ->assertRedirect(route('dashboard.grades.index'));

    $this->assertDatabaseMissing('grades', ['id' => $grade->id]);
});

test('deleting a grade sets student GPA to null when no grades remain', function () {
    $admin = createUserWithRole('admin');
    ['student' => $student, 'enrollment' => $enrollment] = makeGradeTestFixture();

    $grade = Grade::create([
        'enrollment_id' => $enrollment->id,
        'total'         => 80,
        'letter_grade'  => 'B+',
        'grade_points'  => 3.30,
        'graded_by'     => $admin->id,
        'graded_at'     => now(),
    ]);

    $this->actingAs($admin)
         ->delete(route('dashboard.grades.destroy', $grade));

    $fresh = Student::find($student->id);
    expect($fresh->gpa)->toBeNull();
    expect($fresh->academic_standing)->toBeNull();
});
