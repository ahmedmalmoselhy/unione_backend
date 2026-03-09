<?php

use App\Models\AcademicTerm;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Section;

function makeSectionFixture(): array
{
    static $n = 0;
    $n++;

    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture("SCT{$n}");
    ['professor' => $prof] = makeProfessor($dept);

    $course = Course::create([
        'code'          => "SCRS{$n}",
        'name'          => "Section Course {$n}",
        'name_ar'       => 'مادة',
        'credit_hours'  => 3,
        'lecture_hours' => 2,
        'level'         => 1,
        'is_elective'   => false,
        'is_active'     => true,
    ]);

    $term = AcademicTerm::create([
        'name'                   => "SecTerm{$n}",
        'name_ar'                => 'فصل',
        'academic_year'          => 7000 + $n,
        'semester'               => 'first',
        'starts_at'              => '2025-09-01',
        'ends_at'                => '2026-01-31',
        'registration_starts_at' => '2025-08-01',
        'registration_ends_at'   => '2025-08-31',
    ]);

    return compact('course', 'term', 'prof', 'fac', 'dept');
}

// ── GET /dashboard/sections ───────────────────────────────────────────────────

test('admin can list sections', function () {
    $admin = createUserWithRole('admin');

    $this->actingAs($admin)
         ->get(route('dashboard.sections.index'))
         ->assertOk();
});

// ── POST /dashboard/sections ──────────────────────────────────────────────────

test('admin can create a section', function () {
    $admin = createUserWithRole('admin');
    ['course' => $course, 'term' => $term, 'prof' => $prof] = makeSectionFixture();

    $this->actingAs($admin)
         ->post(route('dashboard.sections.store'), [
             'course_id'        => $course->id,
             'professor_id'     => $prof->id,
             'academic_term_id' => $term->id,
             'capacity'         => 30,
         ])
         ->assertRedirect(route('dashboard.sections.index'))
         ->assertSessionHas('success');

    $this->assertDatabaseHas('sections', [
        'course_id'    => $course->id,
        'professor_id' => $prof->id,
        'capacity'     => 30,
    ]);
});

test('section creation validates required fields', function () {
    $admin = createUserWithRole('admin');

    $this->actingAs($admin)
         ->post(route('dashboard.sections.store'), [])
         ->assertSessionHasErrors(['course_id', 'professor_id', 'academic_term_id', 'capacity']);
});

// ── PUT /dashboard/sections/{section} ─────────────────────────────────────────

test('admin can update a section', function () {
    $admin = createUserWithRole('admin');
    ['course' => $course, 'term' => $term, 'prof' => $prof] = makeSectionFixture();

    $section = Section::create([
        'course_id'        => $course->id,
        'professor_id'     => $prof->id,
        'academic_term_id' => $term->id,
        'capacity'         => 20,
        'is_active'        => true,
    ]);

    $this->actingAs($admin)
         ->put(route('dashboard.sections.update', $section), [
             'course_id'        => $course->id,
             'professor_id'     => $prof->id,
             'academic_term_id' => $term->id,
             'capacity'         => 40,
             'is_active'        => '1',
         ])
         ->assertRedirect(route('dashboard.sections.index'))
         ->assertSessionHas('success');

    $this->assertDatabaseHas('sections', ['id' => $section->id, 'capacity' => 40]);
});

// ── DELETE /dashboard/sections/{section} ──────────────────────────────────────

test('admin can delete a section with no enrollments', function () {
    $admin = createUserWithRole('admin');
    ['course' => $course, 'term' => $term, 'prof' => $prof] = makeSectionFixture();

    $section = Section::create([
        'course_id'        => $course->id,
        'professor_id'     => $prof->id,
        'academic_term_id' => $term->id,
        'capacity'         => 10,
        'is_active'        => true,
    ]);

    $this->actingAs($admin)
         ->delete(route('dashboard.sections.destroy', $section))
         ->assertRedirect(route('dashboard.sections.index'))
         ->assertSessionHas('success');

    $this->assertDatabaseMissing('sections', ['id' => $section->id]);
});

test('section with enrollments cannot be deleted', function () {
    $admin = createUserWithRole('admin');
    ['course' => $course, 'term' => $term, 'prof' => $prof, 'fac' => $fac, 'dept' => $dept] = makeSectionFixture();

    $section = Section::create([
        'course_id'        => $course->id,
        'professor_id'     => $prof->id,
        'academic_term_id' => $term->id,
        'capacity'         => 30,
        'is_active'        => true,
    ]);

    ['student' => $student] = makeStudent($fac, $dept);
    Enrollment::create([
        'student_id'       => $student->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'registered',
        'registered_at'    => now(),
    ]);

    $this->actingAs($admin)
         ->delete(route('dashboard.sections.destroy', $section))
         ->assertSessionHasErrors('delete');
});

test('employee cannot create sections', function () {
    $emp = createUserWithRole('employee');
    ['course' => $course, 'term' => $term, 'prof' => $prof] = makeSectionFixture();

    $this->actingAs($emp)
         ->post(route('dashboard.sections.store'), [
             'course_id'        => $course->id,
             'professor_id'     => $prof->id,
             'academic_term_id' => $term->id,
             'capacity'         => 30,
         ])
         ->assertForbidden();
});
