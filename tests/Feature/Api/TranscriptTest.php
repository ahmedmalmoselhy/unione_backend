<?php

use App\Models\Enrollment;
use App\Models\Grade;

// ── GET /api/student/transcript ───────────────────────────────────────────────

test('student can retrieve their transcript', function () {
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
         ->getJson('/api/student/transcript')
         ->assertOk();

    $response->assertJsonStructure([
        'student' => ['student_number', 'name', 'faculty', 'department', 'gpa', 'academic_standing'],
        'terms'   => [['academic_term', 'term_gpa', 'term_credits', 'courses']],
    ]);

    expect($response->json('terms'))->toHaveCount(1);
    expect($response->json('terms.0.courses'))->toHaveCount(1);
    expect($response->json('terms.0.courses.0.grade.letter_grade'))->toBe('A');
});

test('transcript is empty when student has no completed grades', function () {
    ['faculty' => $f, 'department' => $d] = makeFacultyDeptFixture();
    ['user' => $u] = makeStudent($f, $d);

    $this->actingAs($u, 'sanctum')
         ->getJson('/api/student/transcript')
         ->assertOk()
         ->assertJsonCount(0, 'terms');
});

test('transcript groups courses by academic term', function () {
    ['faculty' => $f, 'department' => $d] = makeFacultyDeptFixture();
    ['user' => $u, 'student' => $s] = makeStudent($f, $d);

    $term1 = makeOpenTerm();
    // Second term needs a different academic_year+semester to avoid unique constraint
    $term2 = \App\Models\AcademicTerm::create([
        'name'                   => 'Second Open Term',
        'name_ar'                => 'فصل ثانٍ',
        'academic_year'          => 2025,
        'semester'               => 'second',
        'starts_at'              => today()->subMonth(),
        'ends_at'                => today()->addMonths(4),
        'registration_starts_at' => today()->subDays(10),
        'registration_ends_at'   => today()->addDays(10),
        'is_active'              => false,
    ]);

    $section1 = makeSection($term1);
    $section2 = makeSection($term2);

    foreach ([[$section1, $term1], [$section2, $term2]] as [$sec, $trm]) {
        $e = Enrollment::create([
            'student_id'       => $s->id,
            'section_id'       => $sec->id,
            'academic_term_id' => $trm->id,
            'status'           => 'completed',
            'registered_at'    => now(),
        ]);

        Grade::create([
            'enrollment_id' => $e->id,
            'midterm'       => 25,
            'final'         => 35,
            'coursework'    => 15,
            'total'         => 75,
            'letter_grade'  => 'B',
            'grade_points'  => 3.0,
            'graded_by'     => $u->id,
            'graded_at'     => now(),
        ]);
    }

    $this->actingAs($u, 'sanctum')
         ->getJson('/api/student/transcript')
         ->assertOk()
         ->assertJsonCount(2, 'terms');
});

test('transcript only includes completed enrollments', function () {
    ['faculty' => $f, 'department' => $d] = makeFacultyDeptFixture();
    ['user' => $u, 'student' => $s] = makeStudent($f, $d);

    $term    = makeOpenTerm();
    $section = makeSection($term);

    // A registered (not completed) enrollment with a grade — should NOT appear
    $enrollment = Enrollment::create([
        'student_id'       => $s->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'registered',
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

    $this->actingAs($u, 'sanctum')
         ->getJson('/api/student/transcript')
         ->assertOk()
         ->assertJsonCount(0, 'terms');
});

test('non-student user cannot access transcript', function () {
    $user = createUserWithRole('professor');

    $this->actingAs($user, 'sanctum')
         ->getJson('/api/student/transcript')
         ->assertForbidden();
});
