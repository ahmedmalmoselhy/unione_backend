<?php

use App\Models\AcademicTerm;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Section;

function makeEnrollmentTestFixture(): array
{
    static $n = 0;
    $n++;

    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture("ENR{$n}");
    ['student' => $student] = makeStudent($fac, $dept);
    ['professor' => $prof]  = makeProfessor($dept);

    $course = Course::create([
        'code'          => "ENRC{$n}",
        'name'          => "Enrollment Course {$n}",
        'name_ar'       => "مادة {$n}",
        'credit_hours'  => 3,
        'lecture_hours' => 2,
        'level'         => 1,
        'is_elective'   => false,
        'is_active'     => true,
    ]);

    $term = AcademicTerm::create([
        'name'                   => "EnrollTerm {$n}",
        'name_ar'                => "فصل {$n}",
        'academic_year'          => 3000 + $n,
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

    return compact('student', 'section', 'term');
}

// ── store ────────────────────────────────────────────────────────────────────

test('admin can create an enrollment', function () {
    $admin = createUserWithRole('admin');
    ['student' => $student, 'section' => $section, 'term' => $term] = makeEnrollmentTestFixture();

    $this->actingAs($admin)
         ->post(route('dashboard.enrollments.store'), [
             'student_id'       => $student->id,
             'section_id'       => $section->id,
             'academic_term_id' => $term->id,
             'status'           => 'registered',
             'registered_at'    => now()->toDateString(),
         ])
         ->assertRedirect(route('dashboard.enrollments.index'));

    $this->assertDatabaseHas('enrollments', [
        'student_id' => $student->id,
        'section_id' => $section->id,
        'status'     => 'registered',
    ]);
});

test('enrolling a student twice in the same section is rejected', function () {
    $admin = createUserWithRole('admin');
    ['student' => $student, 'section' => $section, 'term' => $term] = makeEnrollmentTestFixture();

    Enrollment::create([
        'student_id'       => $student->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'registered',
        'registered_at'    => now(),
    ]);

    $this->actingAs($admin)
         ->post(route('dashboard.enrollments.store'), [
             'student_id'       => $student->id,
             'section_id'       => $section->id,
             'academic_term_id' => $term->id,
             'status'           => 'registered',
             'registered_at'    => now()->toDateString(),
         ])
         ->assertSessionHasErrors('section_id');
});

test('employee cannot create enrollments', function () {
    $emp = createUserWithRole('employee');
    ['student' => $student, 'section' => $section, 'term' => $term] = makeEnrollmentTestFixture();

    $this->actingAs($emp)
         ->post(route('dashboard.enrollments.store'), [
             'student_id'       => $student->id,
             'section_id'       => $section->id,
             'academic_term_id' => $term->id,
             'status'           => 'registered',
             'registered_at'    => now()->toDateString(),
         ])
         ->assertForbidden();
});

// ── destroy ──────────────────────────────────────────────────────────────────

test('admin can hard-delete an enrollment', function () {
    $admin = createUserWithRole('admin');
    ['student' => $student, 'section' => $section, 'term' => $term] = makeEnrollmentTestFixture();

    $enrollment = Enrollment::create([
        'student_id'       => $student->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'registered',
        'registered_at'    => now(),
    ]);

    $this->actingAs($admin)
         ->delete(route('dashboard.enrollments.destroy', $enrollment))
         ->assertRedirect(route('dashboard.enrollments.index'));

    $this->assertDatabaseMissing('enrollments', ['id' => $enrollment->id]);
});
