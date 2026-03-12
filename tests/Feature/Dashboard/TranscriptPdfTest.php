<?php

use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Student;

// ── GET /dashboard/students/{student}/transcript/pdf ─────────────────────────

test('admin can download a student transcript as PDF', function () {
    ['faculty' => $f, 'department' => $d] = makeFacultyDeptFixture();
    ['student' => $s] = makeStudent($f, $d);

    $term    = makeOpenTerm();
    $section = makeSection($term);

    $admin = createUserWithRole('admin');

    $enrollment = Enrollment::create([
        'student_id'       => $s->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'completed',
        'registered_at'    => now(),
    ]);

    Grade::create([
        'enrollment_id' => $enrollment->id,
        'midterm'       => 25,
        'final'         => 35,
        'coursework'    => 15,
        'total'         => 75,
        'letter_grade'  => 'B',
        'grade_points'  => 3.0,
        'graded_by'     => $admin->id,
        'graded_at'     => now(),
    ]);

    $response = $this->actingAs($admin)
        ->get(route('dashboard.students.transcript.pdf', $s));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/pdf');
    expect($response->headers->get('Content-Disposition'))->toContain($s->student_number);
});

test('admin can download transcript for student with no grades', function () {
    ['faculty' => $f, 'department' => $d] = makeFacultyDeptFixture();
    ['student' => $s] = makeStudent($f, $d);

    $admin = createUserWithRole('admin');

    $response = $this->actingAs($admin)
        ->get(route('dashboard.students.transcript.pdf', $s));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/pdf');
});

test('unauthenticated user cannot access dashboard transcript PDF', function () {
    ['faculty' => $f, 'department' => $d] = makeFacultyDeptFixture();
    ['student' => $s] = makeStudent($f, $d);

    $this->get(route('dashboard.students.transcript.pdf', $s))
        ->assertRedirect();
});

test('employee without scoped admin role cannot access dashboard transcript PDF', function () {
    ['faculty' => $f, 'department' => $d] = makeFacultyDeptFixture();
    ['student' => $s] = makeStudent($f, $d);

    $employee = createUserWithRole('employee');

    $this->actingAs($employee)
        ->get(route('dashboard.students.transcript.pdf', $s))
        ->assertForbidden();
});
