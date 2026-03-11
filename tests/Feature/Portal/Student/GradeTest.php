<?php

use App\Models\Enrollment;
use App\Models\Grade;

// ── GET /grades ───────────────────────────────────────────────────────────────

test('student can view their grades page', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user, 'student' => $student] = makeStudent($fac, $dept);

    $term    = makeOpenTerm();
    $section = makeSection($term);
    $enrollment = Enrollment::create([
        'student_id'       => $student->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'completed',
        'registered_at'    => now()->subMonth(),
    ]);
    Grade::create([
        'enrollment_id' => $enrollment->id,
        'midterm'       => 40,
        'final'         => 50,
        'total'         => 90,
        'letter_grade'  => 'A',
        'grade_points'  => 4.0,
        'graded_by'     => $user->id,
        'graded_at'     => now(),
    ]);

    $this->actingAs($user)
         ->get(route('portal.grades'))
         ->assertOk();
});

test('student with no grades sees empty grades page', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user] = makeStudent($fac, $dept);

    $this->actingAs($user)
         ->get(route('portal.grades'))
         ->assertOk();
});

test('guest is redirected from grades page', function () {
    $this->get(route('portal.grades'))
         ->assertRedirect(route('portal.login'));
});
