<?php

use App\Models\Enrollment;
use App\Models\Grade;

// ── GET /courses (enrollment index) ──────────────────────────────────────────

test('student can view their enrollments list', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user, 'student' => $student] = makeStudent($fac, $dept);

    $term    = makeOpenTerm();
    $section = makeSection($term);
    Enrollment::create([
        'student_id'       => $student->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'registered',
        'registered_at'    => now(),
    ]);

    $this->actingAs($user)
         ->get(route('portal.enrollments.index'))
         ->assertOk();
});

test('non-student cannot access enrollments list', function () {
    ['department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user] = makeProfessor($dept);

    $this->actingAs($user)
         ->get(route('portal.enrollments.index'))
         ->assertNotFound();
});

test('guest is redirected from enrollments', function () {
    $this->get(route('portal.enrollments.index'))
         ->assertRedirect(route('portal.login'));
});

// ── GET /courses/enroll (create form) ────────────────────────────────────────

test('student can view the enroll page during open term', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user] = makeStudent($fac, $dept);

    makeOpenTerm();

    $this->actingAs($user)
         ->get(route('portal.enrollments.create'))
         ->assertOk();
});

// ── POST /courses ─────────────────────────────────────────────────────────────

test('student can enroll in a section during open term', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user, 'student' => $student] = makeStudent($fac, $dept);

    $term    = makeOpenTerm();
    $section = makeSection($term);

    $this->actingAs($user)
         ->post(route('portal.enrollments.store'), ['section_id' => $section->id])
         ->assertRedirect();

    $this->assertDatabaseHas('enrollments', [
        'student_id' => $student->id,
        'section_id' => $section->id,
        'status'     => 'registered',
    ]);
});

test('student cannot enroll when registration is closed', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user] = makeStudent($fac, $dept);

    $term    = makeClosedTerm();
    $section = makeSection($term);

    $this->actingAs($user)
         ->post(route('portal.enrollments.store'), ['section_id' => $section->id])
         ->assertSessionHasErrors('error');
});

test('student cannot enroll in the same section twice', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user, 'student' => $student] = makeStudent($fac, $dept);

    $term    = makeOpenTerm();
    $section = makeSection($term);
    Enrollment::create([
        'student_id'       => $student->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'registered',
        'registered_at'    => now(),
    ]);

    $this->actingAs($user)
         ->post(route('portal.enrollments.store'), ['section_id' => $section->id])
         ->assertSessionHasErrors('error');
});

test('student cannot enroll in a full section', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user, 'student' => $student] = makeStudent($fac, $dept);
    ['faculty' => $fac2, 'department' => $dept2] = makeFacultyDeptFixture();
    ['student' => $other] = makeStudent($fac2, $dept2);

    $term    = makeOpenTerm();
    $section = makeSection($term, capacity: 1);
    Enrollment::create([
        'student_id'       => $other->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'registered',
        'registered_at'    => now(),
    ]);

    $this->actingAs($user)
         ->post(route('portal.enrollments.store'), ['section_id' => $section->id])
         ->assertSessionHasErrors('error');
});

// ── DELETE /courses/{enrollment} ──────────────────────────────────────────────

test('student can drop their own enrollment', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user, 'student' => $student] = makeStudent($fac, $dept);

    $term    = makeOpenTerm();
    $section = makeSection($term);
    $enrollment = Enrollment::create([
        'student_id'       => $student->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'registered',
        'registered_at'    => now(),
    ]);

    $this->actingAs($user)
         ->delete(route('portal.enrollments.destroy', $enrollment))
         ->assertRedirect();

    $this->assertDatabaseHas('enrollments', [
        'id'     => $enrollment->id,
        'status' => 'dropped',
    ]);
});

test('student cannot drop another student\'s enrollment', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user] = makeStudent($fac, $dept);
    ['faculty' => $fac2, 'department' => $dept2] = makeFacultyDeptFixture();
    ['student' => $other] = makeStudent($fac2, $dept2);

    $term    = makeOpenTerm();
    $section = makeSection($term);
    $enrollment = Enrollment::create([
        'student_id'       => $other->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'registered',
        'registered_at'    => now(),
    ]);

    $this->actingAs($user)
         ->delete(route('portal.enrollments.destroy', $enrollment))
         ->assertNotFound();
});
