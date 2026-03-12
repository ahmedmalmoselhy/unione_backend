<?php

use App\Models\Enrollment;
use App\Models\Grade;

// ── GET /api/student/transcript/pdf ───────────────────────────────────────────

test('student can download their transcript as PDF', function () {
    ['faculty' => $f, 'department' => $d] = makeFacultyDeptFixture();
    ['user' => $u, 'student' => $s] = makeStudent($f, $d);

    $term    = makeOpenTerm();
    $section = makeSection($term);

    $enrollment = Enrollment::create([
        'student_id'       => $s->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'completed',
        'registered_at'    => now(),
    ]);

    Grade::create([
        'enrollment_id' => $enrollment->id,
        'midterm'       => 30,
        'final'         => 40,
        'coursework'    => 20,
        'total'         => 90,
        'letter_grade'  => 'A',
        'grade_points'  => 4.0,
        'graded_by'     => $u->id,
        'graded_at'     => now(),
    ]);

    $response = $this->actingAs($u, 'sanctum')
        ->get('/api/student/transcript/pdf');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/pdf');
    expect($response->headers->get('Content-Disposition'))->toContain('attachment');
    expect($response->headers->get('Content-Disposition'))->toContain($s->student_number);
});

test('student with no grades still gets a valid PDF transcript', function () {
    ['faculty' => $f, 'department' => $d] = makeFacultyDeptFixture();
    ['user' => $u] = makeStudent($f, $d);

    $response = $this->actingAs($u, 'sanctum')
        ->get('/api/student/transcript/pdf');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/pdf');
});

test('unauthenticated user cannot access transcript PDF endpoint', function () {
    $this->getJson('/api/student/transcript/pdf')
        ->assertUnauthorized();
});

test('non-student user cannot access transcript PDF endpoint', function () {
    $admin = createUserWithRole('admin');

    $this->actingAs($admin, 'sanctum')
        ->getJson('/api/student/transcript/pdf')
        ->assertForbidden();
});
