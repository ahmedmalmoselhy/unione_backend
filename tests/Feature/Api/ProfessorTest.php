<?php

use App\Models\AcademicTerm;
use App\Models\Course;
use App\Models\Section;

// ── GET /api/professor/profile ───────────────────────────────────────────────

test('professor can retrieve own profile', function () {
    ['faculty' => $faculty, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user, 'professor' => $professor] = makeProfessor($dept);

    $response = $this->actingAs($user, 'sanctum')
                     ->getJson('/api/professor/profile');

    $response->assertOk()
             ->assertJsonPath('professor.staff_number', $professor->staff_number)
             ->assertJsonPath('professor.department.id', $dept->id)
             ->assertJsonPath('professor.department.faculty.id', $faculty->id);
});

test('user with professor role but no professor record gets 404', function () {
    $user = createUserWithRole('professor'); // role but no professor row

    $this->actingAs($user, 'sanctum')
         ->getJson('/api/professor/profile')
         ->assertNotFound();
});

// ── GET /api/professor/sections ─────────────────────────────────────────────

test('professor can retrieve their sections', function () {
    ['department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user, 'professor' => $professor] = makeProfessor($dept);

    $term = AcademicTerm::create([
        'name'                    => 'Term 2',
        'name_ar'                 => 'الفصل 2',
        'academic_year'           => 2025,
        'semester'                => 'second',
        'starts_at'               => '2026-02-01',
        'ends_at'                 => '2026-06-30',
        'registration_starts_at'  => '2026-01-01',
        'registration_ends_at'    => '2026-02-14',
    ]);

    $course = Course::create([
        'code'          => 'MATH201',
        'name'          => 'Calculus',
        'name_ar'       => 'التفاضل والتكامل',
        'credit_hours'  => 4,
        'lecture_hours' => 3,
        'level'         => 2,
        'is_elective'   => false,
        'is_active'     => true,
    ]);

    Section::create([
        'course_id'        => $course->id,
        'professor_id'     => $professor->id,
        'academic_term_id' => $term->id,
        'capacity'         => 25,
        'is_active'        => true,
    ]);

    $response = $this->actingAs($user, 'sanctum')
                     ->getJson('/api/professor/sections');

    $response->assertOk()
             ->assertJsonCount(1, 'sections')
             ->assertJsonPath('sections.0.course.code', 'MATH201')
             ->assertJsonPath('sections.0.academic_term.semester', 'second')
             ->assertJsonPath('sections.0.enrollments_count', 0);
});

test('professor with no sections gets an empty list', function () {
    ['department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user] = makeProfessor($dept);

    $this->actingAs($user, 'sanctum')
         ->getJson('/api/professor/sections')
         ->assertOk()
         ->assertJsonCount(0, 'sections');
});

// ── GET /api/professor/schedule ──────────────────────────────────────────────

test('professor can retrieve their schedule', function () {
    $term    = makeOpenTerm();
    $section = makeSection($term);
    $profUser = $section->professor->user;

    $this->actingAs($profUser, 'sanctum')
         ->getJson('/api/professor/schedule')
         ->assertOk()
         ->assertJsonStructure(['schedule']);
});

test('professor with no active-term sections gets empty schedule', function () {
    ['department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user] = makeProfessor($dept);

    $this->actingAs($user, 'sanctum')
         ->getJson('/api/professor/schedule')
         ->assertOk()
         ->assertJsonStructure(['schedule']);
});

// ── GET /api/professor/sections/{section}/students ───────────────────────────

test('professor can list students in their section', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['student' => $student] = makeStudent($fac, $dept);

    $term    = makeOpenTerm();
    $section = makeSection($term);
    $profUser = $section->professor->user;

    \App\Models\Enrollment::create([
        'student_id'       => $student->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'registered',
        'registered_at'    => now(),
    ]);

    $this->actingAs($profUser, 'sanctum')
         ->getJson("/api/professor/sections/{$section->id}/students")
         ->assertOk()
         ->assertJsonStructure(['students'])
         ->assertJsonCount(1, 'students');
});

test('professor cannot list students in another professor\'s section', function () {
    $term     = makeOpenTerm();
    $section  = makeSection($term);
    $section2 = makeSection($term);
    $otherUser = $section2->professor->user;

    $this->actingAs($otherUser, 'sanctum')
         ->getJson("/api/professor/sections/{$section->id}/students")
         ->assertForbidden();
});

// ── GET /api/professor/sections/{section}/grades ─────────────────────────────

test('professor can list grades in their section', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['student' => $student] = makeStudent($fac, $dept);

    $term    = makeOpenTerm();
    $section = makeSection($term);
    $profUser = $section->professor->user;

    $enrollment = \App\Models\Enrollment::create([
        'student_id'       => $student->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'registered',
        'registered_at'    => now(),
    ]);
    \App\Models\Grade::create([
        'enrollment_id' => $enrollment->id,
        'midterm'       => 40,
        'final'         => 50,
        'total'         => 90,
        'letter_grade'  => 'A',
        'grade_points'  => 4.0,
        'graded_by'     => $profUser->id,
        'graded_at'     => now(),
    ]);

    $this->actingAs($profUser, 'sanctum')
         ->getJson("/api/professor/sections/{$section->id}/grades")
         ->assertOk()
         ->assertJsonStructure(['grades'])
         ->assertJsonCount(1, 'grades');
});

test('professor cannot list grades in another professor\'s section', function () {
    $term     = makeOpenTerm();
    $section  = makeSection($term);
    $section2 = makeSection($term);
    $otherUser = $section2->professor->user;

    $this->actingAs($otherUser, 'sanctum')
         ->getJson("/api/professor/sections/{$section->id}/grades")
         ->assertForbidden();
});
