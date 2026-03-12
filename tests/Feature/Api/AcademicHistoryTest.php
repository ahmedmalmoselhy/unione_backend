<?php

use App\Models\Enrollment;
use App\Models\Grade;

// ── GET /api/student/academic-history ────────────────────────────────────────

test('student can retrieve their academic history', function () {
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
        'midterm'       => 25,
        'final'         => 35,
        'coursework'    => 15,
        'total'         => 75,
        'letter_grade'  => 'B',
        'grade_points'  => 3.0,
        'graded_by'     => $u->id,
        'graded_at'     => now(),
    ]);

    $this->actingAs($u, 'sanctum')
         ->getJson('/api/student/academic-history')
         ->assertOk()
         ->assertJsonStructure([
             'student'  => ['student_number', 'name', 'faculty', 'department', 'gpa', 'academic_standing', 'enrollment_status'],
             'progress' => ['credits_earned', 'credits_required', 'progress_pct'],
             'terms'    => [['academic_term', 'term_gpa', 'term_credits', 'courses']],
         ]);
});

test('academic history includes all enrollment statuses not just completed', function () {
    ['faculty' => $f, 'department' => $d] = makeFacultyDeptFixture();
    ['user' => $u, 'student' => $s] = makeStudent($f, $d);

    $term     = makeOpenTerm();
    $section1 = makeSection($term);
    $section2 = makeSection($term);

    // One registered, one dropped
    Enrollment::create([
        'student_id'       => $s->id,
        'section_id'       => $section1->id,
        'academic_term_id' => $term->id,
        'status'           => 'registered',
        'registered_at'    => now(),
    ]);

    Enrollment::create([
        'student_id'       => $s->id,
        'section_id'       => $section2->id,
        'academic_term_id' => $term->id,
        'status'           => 'dropped',
        'registered_at'    => now()->subDay(),
        'dropped_at'       => now(),
    ]);

    $response = $this->actingAs($u, 'sanctum')
         ->getJson('/api/student/academic-history')
         ->assertOk();

    // Both enrollments in the same term → 1 term, 2 courses
    expect($response->json('terms'))->toHaveCount(1);
    expect($response->json('terms.0.courses'))->toHaveCount(2);
});

test('academic history credits_earned counts only completed enrollments', function () {
    ['faculty' => $f, 'department' => $d] = makeFacultyDeptFixture();
    ['user' => $u, 'student' => $s] = makeStudent($f, $d);

    $term    = makeOpenTerm();
    $section = makeSection($term); // course credit_hours = 3 by default helper

    Enrollment::create([
        'student_id'       => $s->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'completed',
        'registered_at'    => now(),
    ]);

    $response = $this->actingAs($u, 'sanctum')
         ->getJson('/api/student/academic-history')
         ->assertOk();

    expect($response->json('progress.credits_earned'))->toBe(3);
});

test('academic history shows progress_pct when department has required_credit_hours', function () {
    ['faculty' => $f, 'department' => $d] = makeFacultyDeptFixture();
    ['user' => $u, 'student' => $s] = makeStudent($f, $d);

    // Set graduation requirement
    $d->update(['required_credit_hours' => 120]);

    $term    = makeOpenTerm();
    $section = makeSection($term); // 3 credit hours

    Enrollment::create([
        'student_id'       => $s->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'completed',
        'registered_at'    => now(),
    ]);

    $response = $this->actingAs($u, 'sanctum')
         ->getJson('/api/student/academic-history')
         ->assertOk();

    expect($response->json('progress.credits_required'))->toBe(120);
    expect($response->json('progress.progress_pct'))->toBe(2.5); // 3/120 * 100
});

test('academic history progress_pct is null when no graduation requirement set', function () {
    ['faculty' => $f, 'department' => $d] = makeFacultyDeptFixture();
    ['user' => $u] = makeStudent($f, $d);

    $this->actingAs($u, 'sanctum')
         ->getJson('/api/student/academic-history')
         ->assertOk()
         ->assertJsonPath('progress.credits_required', null)
         ->assertJsonPath('progress.progress_pct', null);
});

test('academic history returns empty terms when student has no enrollments', function () {
    ['faculty' => $f, 'department' => $d] = makeFacultyDeptFixture();
    ['user' => $u] = makeStudent($f, $d);

    $this->actingAs($u, 'sanctum')
         ->getJson('/api/student/academic-history')
         ->assertOk()
         ->assertJsonCount(0, 'terms')
         ->assertJsonPath('progress.credits_earned', 0);
});

test('non-student user cannot access academic history', function () {
    $user = createUserWithRole('professor');

    $this->actingAs($user, 'sanctum')
         ->getJson('/api/student/academic-history')
         ->assertForbidden();
});
