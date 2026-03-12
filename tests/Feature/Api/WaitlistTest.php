<?php

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\EnrollmentWaitlist;

// ── Waitlist join ─────────────────────────────────────────────────────────────

test('student is placed on waitlist with correct position when section is full', function () {
    ['faculty' => $f1, 'department' => $d1] = makeFacultyDeptFixture();
    ['user' => $user1, 'student' => $s1] = makeStudent($f1, $d1);

    ['faculty' => $f2, 'department' => $d2] = makeFacultyDeptFixture();
    ['student' => $filler] = makeStudent($f2, $d2);

    $term    = makeOpenTerm();
    $section = makeSection($term, capacity: 1);

    Enrollment::create([
        'student_id'       => $filler->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'registered',
        'registered_at'    => now(),
    ]);

    $this->actingAs($user1, 'sanctum')
         ->postJson('/api/student/enrollments', ['section_id' => $section->id])
         ->assertStatus(202)
         ->assertJsonPath('waitlist.position', 1)
         ->assertJsonFragment(['message' => 'Section is full. You have been added to the waitlist.']);

    $this->assertDatabaseHas('enrollment_waitlist', [
        'student_id' => $s1->id,
        'section_id' => $section->id,
        'position'   => 1,
    ]);
});

test('multiple students on waitlist get sequential positions', function () {
    ['faculty' => $f, 'department' => $d] = makeFacultyDeptFixture();
    ['student' => $filler] = makeStudent($f, $d);
    ['user' => $u1, 'student' => $s1] = makeStudent($f, $d);
    ['user' => $u2, 'student' => $s2] = makeStudent($f, $d);

    $term    = makeOpenTerm();
    $section = makeSection($term, capacity: 1);

    Enrollment::create([
        'student_id'       => $filler->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'registered',
        'registered_at'    => now(),
    ]);

    $this->actingAs($u1, 'sanctum')
         ->postJson('/api/student/enrollments', ['section_id' => $section->id])
         ->assertStatus(202)
         ->assertJsonPath('waitlist.position', 1);

    $this->actingAs($u2, 'sanctum')
         ->postJson('/api/student/enrollments', ['section_id' => $section->id])
         ->assertStatus(202)
         ->assertJsonPath('waitlist.position', 2);
});

test('student cannot join the same waitlist twice', function () {
    ['faculty' => $f, 'department' => $d] = makeFacultyDeptFixture();
    ['student' => $filler] = makeStudent($f, $d);
    ['user' => $u1, 'student' => $s1] = makeStudent($f, $d);

    $term    = makeOpenTerm();
    $section = makeSection($term, capacity: 1);

    Enrollment::create([
        'student_id'       => $filler->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'registered',
        'registered_at'    => now(),
    ]);

    $this->actingAs($u1, 'sanctum')
         ->postJson('/api/student/enrollments', ['section_id' => $section->id])
         ->assertStatus(202);

    $this->actingAs($u1, 'sanctum')
         ->postJson('/api/student/enrollments', ['section_id' => $section->id])
         ->assertUnprocessable()
         ->assertJsonFragment(['message' => 'Already on the waitlist for this section.']);
});

// ── Auto-promote on drop ──────────────────────────────────────────────────────

test('dropping an enrollment auto-promotes the first student on the waitlist', function () {
    ['faculty' => $f, 'department' => $d] = makeFacultyDeptFixture();
    ['user' => $holder, 'student' => $holderStudent] = makeStudent($f, $d);
    ['user' => $waiter, 'student' => $waiterStudent] = makeStudent($f, $d);

    $term    = makeOpenTerm();
    $section = makeSection($term, capacity: 1);

    $enrollment = Enrollment::create([
        'student_id'       => $holderStudent->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'registered',
        'registered_at'    => now(),
    ]);

    EnrollmentWaitlist::create([
        'student_id'       => $waiterStudent->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'position'         => 1,
        'joined_at'        => now(),
    ]);

    $this->actingAs($holder, 'sanctum')
         ->deleteJson("/api/student/enrollments/{$enrollment->id}")
         ->assertOk();

    // Waitlist entry should be cleared
    $this->assertDatabaseMissing('enrollment_waitlist', [
        'student_id' => $waiterStudent->id,
        'section_id' => $section->id,
    ]);

    // Promoted student should have a new enrollment
    $this->assertDatabaseHas('enrollments', [
        'student_id' => $waiterStudent->id,
        'section_id' => $section->id,
        'status'     => 'registered',
    ]);

    // Promoted student should have a notification
    $this->assertDatabaseCount('notifications', 1);
});

test('dropping without a waitlist does not promote anyone', function () {
    ['faculty' => $f, 'department' => $d] = makeFacultyDeptFixture();
    ['user' => $u, 'student' => $s] = makeStudent($f, $d);

    $term    = makeOpenTerm();
    $section = makeSection($term, capacity: 5);

    $enrollment = Enrollment::create([
        'student_id'       => $s->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'registered',
        'registered_at'    => now(),
    ]);

    $this->actingAs($u, 'sanctum')
         ->deleteJson("/api/student/enrollments/{$enrollment->id}")
         ->assertOk();

    $this->assertDatabaseCount('enrollments', 1);
    $this->assertDatabaseCount('enrollment_waitlist', 0);
});

// ── View waitlist ─────────────────────────────────────────────────────────────

test('student can view their waitlist positions', function () {
    ['faculty' => $f, 'department' => $d] = makeFacultyDeptFixture();
    ['user' => $u, 'student' => $s] = makeStudent($f, $d);

    $term    = makeOpenTerm();
    $section = makeSection($term, capacity: 1);

    EnrollmentWaitlist::create([
        'student_id'       => $s->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'position'         => 1,
        'joined_at'        => now(),
    ]);

    $this->actingAs($u, 'sanctum')
         ->getJson('/api/student/waitlist')
         ->assertOk()
         ->assertJsonCount(1, 'waitlist')
         ->assertJsonPath('waitlist.0.position', 1);
});

// ── Leave waitlist ────────────────────────────────────────────────────────────

test('student can leave the waitlist for a section', function () {
    ['faculty' => $f, 'department' => $d] = makeFacultyDeptFixture();
    ['user' => $u, 'student' => $s] = makeStudent($f, $d);

    $term    = makeOpenTerm();
    $section = makeSection($term, capacity: 1);

    EnrollmentWaitlist::create([
        'student_id'       => $s->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'position'         => 1,
        'joined_at'        => now(),
    ]);

    $this->actingAs($u, 'sanctum')
         ->deleteJson("/api/student/waitlist/{$section->id}")
         ->assertOk()
         ->assertJsonFragment(['message' => 'Removed from waitlist successfully.']);

    $this->assertDatabaseMissing('enrollment_waitlist', [
        'student_id' => $s->id,
        'section_id' => $section->id,
    ]);
});

test('leaving waitlist re-numbers remaining positions', function () {
    ['faculty' => $f, 'department' => $d] = makeFacultyDeptFixture();
    ['user' => $u1, 'student' => $s1] = makeStudent($f, $d);
    ['user' => $u2, 'student' => $s2] = makeStudent($f, $d);

    $term    = makeOpenTerm();
    $section = makeSection($term, capacity: 1);

    EnrollmentWaitlist::create([
        'student_id'       => $s1->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'position'         => 1,
        'joined_at'        => now(),
    ]);

    EnrollmentWaitlist::create([
        'student_id'       => $s2->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'position'         => 2,
        'joined_at'        => now(),
    ]);

    $this->actingAs($u1, 'sanctum')
         ->deleteJson("/api/student/waitlist/{$section->id}")
         ->assertOk();

    $this->assertDatabaseHas('enrollment_waitlist', [
        'student_id' => $s2->id,
        'section_id' => $section->id,
        'position'   => 1,
    ]);
});
