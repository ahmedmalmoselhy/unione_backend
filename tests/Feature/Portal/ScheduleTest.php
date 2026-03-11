<?php

use App\Models\Enrollment;

// ── GET /schedule ─────────────────────────────────────────────────────────────

test('student can view schedule page', function () {
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
         ->get(route('portal.schedule'))
         ->assertOk();
});

test('professor can view schedule page', function () {
    ['department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user] = makeProfessor($dept);

    $this->actingAs($user)
         ->get(route('portal.schedule'))
         ->assertOk();
});

test('student with no enrollments sees empty schedule', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user] = makeStudent($fac, $dept);

    $this->actingAs($user)
         ->get(route('portal.schedule'))
         ->assertOk();
});

test('guest is redirected from schedule page', function () {
    $this->get(route('portal.schedule'))
         ->assertRedirect(route('portal.login'));
});
