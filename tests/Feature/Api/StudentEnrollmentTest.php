<?php

use App\Models\AcademicTerm;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Section;

// ── POST /api/student/enrollments ────────────────────────────────────────────

test('student can enroll in an active open section', function () {
    ['faculty' => $faculty, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user, 'student' => $student] = makeStudent($faculty, $dept);
    $term    = makeOpenTerm();
    $section = makeSection($term);

    $this->actingAs($user, 'sanctum')
         ->postJson('/api/student/enrollments', ['section_id' => $section->id])
         ->assertCreated()
         ->assertJsonPath('enrollment.section.id', $section->id);

    $this->assertDatabaseHas('enrollments', [
        'student_id' => $student->id,
        'section_id' => $section->id,
        'status'     => 'registered',
    ]);
});

test('student cannot enroll when registration period is closed', function () {
    ['faculty' => $faculty, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user] = makeStudent($faculty, $dept);
    $term    = makeClosedTerm();
    $section = makeSection($term);

    $this->actingAs($user, 'sanctum')
         ->postJson('/api/student/enrollments', ['section_id' => $section->id])
         ->assertUnprocessable()
         ->assertJsonFragment(['message' => 'Registration period is not open.']);
});

test('student is added to waitlist when section is full', function () {
    ['faculty' => $faculty, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user, 'student' => $student] = makeStudent($faculty, $dept);

    // Create a second student to fill the only seat
    ['faculty' => $f2, 'department' => $d2] = makeFacultyDeptFixture();
    ['student' => $other] = makeStudent($f2, $d2);

    $term    = makeOpenTerm();
    $section = makeSection($term, capacity: 1);

    // Fill the seat
    Enrollment::create([
        'student_id'       => $other->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'registered',
        'registered_at'    => now(),
    ]);

    $this->actingAs($user, 'sanctum')
         ->postJson('/api/student/enrollments', ['section_id' => $section->id])
         ->assertStatus(202)
         ->assertJsonPath('waitlist.position', 1);

    $this->assertDatabaseHas('enrollment_waitlist', [
        'student_id' => $student->id,
        'section_id' => $section->id,
        'position'   => 1,
    ]);
});

test('student cannot enroll in the same section twice', function () {
    ['faculty' => $faculty, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user, 'student' => $student] = makeStudent($faculty, $dept);
    $term    = makeOpenTerm();
    $section = makeSection($term);

    Enrollment::create([
        'student_id'       => $student->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'registered',
        'registered_at'    => now(),
    ]);

    $this->actingAs($user, 'sanctum')
         ->postJson('/api/student/enrollments', ['section_id' => $section->id])
         ->assertUnprocessable()
         ->assertJsonFragment(['message' => 'Already enrolled in this section.']);
});

test('user without student record cannot enroll', function () {
    $user = createUserWithRole('student');

    $this->actingAs($user, 'sanctum')
         ->postJson('/api/student/enrollments', ['section_id' => 1])
         ->assertNotFound();
});

// ── DELETE /api/student/enrollments/{enrollment} ─────────────────────────────

test('student can drop an enrollment during registration period', function () {
    ['faculty' => $faculty, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user, 'student' => $student] = makeStudent($faculty, $dept);
    $term    = makeOpenTerm();
    $section = makeSection($term);

    $enrollment = Enrollment::create([
        'student_id'       => $student->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'registered',
        'registered_at'    => now(),
    ]);

    $this->actingAs($user, 'sanctum')
         ->deleteJson("/api/student/enrollments/{$enrollment->id}")
         ->assertOk()
         ->assertJsonFragment(['message' => 'Enrollment dropped successfully.']);

    $this->assertDatabaseHas('enrollments', [
        'id'     => $enrollment->id,
        'status' => 'dropped',
    ]);
});

test('student cannot drop an enrollment after registration closes', function () {
    ['faculty' => $faculty, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user, 'student' => $student] = makeStudent($faculty, $dept);
    $term    = makeClosedTerm();
    $section = makeSection($term);

    $enrollment = Enrollment::create([
        'student_id'       => $student->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'registered',
        'registered_at'    => now(),
    ]);

    $this->actingAs($user, 'sanctum')
         ->deleteJson("/api/student/enrollments/{$enrollment->id}")
         ->assertUnprocessable()
         ->assertJsonFragment(['message' => 'Drop period is not open.']);
});

test('student cannot drop another student\'s enrollment', function () {
    ['faculty' => $fa, 'department' => $da] = makeFacultyDeptFixture();
    ['user' => $userA, 'student' => $studentA] = makeStudent($fa, $da);

    ['faculty' => $fb, 'department' => $db] = makeFacultyDeptFixture();
    ['student' => $studentB] = makeStudent($fb, $db);

    $term    = makeOpenTerm();
    $section = makeSection($term);

    $enrollment = Enrollment::create([
        'student_id'       => $studentB->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'registered',
        'registered_at'    => now(),
    ]);

    $this->actingAs($userA, 'sanctum')
         ->deleteJson("/api/student/enrollments/{$enrollment->id}")
         ->assertNotFound();
});

test('unauthenticated user cannot access enrollment endpoints', function () {
    $this->postJson('/api/student/enrollments', ['section_id' => 1])->assertUnauthorized();
    $this->deleteJson('/api/student/enrollments/1')->assertUnauthorized();
});
