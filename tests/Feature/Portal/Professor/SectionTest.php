<?php

use App\Models\Enrollment;
use App\Models\Section;

// ── GET /sections ─────────────────────────────────────────────────────────────

test('professor can view their sections list', function () {
    $term    = makeOpenTerm();
    $section = makeSection($term);
    $profUser = $section->professor->user;

    $this->actingAs($profUser)
         ->get(route('portal.sections.index'))
         ->assertOk();
});

test('guest is redirected from sections list', function () {
    $this->get(route('portal.sections.index'))
         ->assertRedirect(route('portal.login'));
});

// ── GET /sections/{section} ────────────────────────────────────────────────────

test('professor can view their own section detail', function () {
    $term    = makeOpenTerm();
    $section = makeSection($term);
    $profUser = $section->professor->user;

    $this->actingAs($profUser)
         ->get(route('portal.sections.show', $section))
         ->assertOk();
});

test('professor cannot view another professor\'s section', function () {
    $term     = makeOpenTerm();
    $section  = makeSection($term);
    $section2 = makeSection($term);

    // Login as the professor of section2, try to view section
    $otherUser = $section2->professor->user;

    $this->actingAs($otherUser)
         ->get(route('portal.sections.show', $section))
         ->assertForbidden();
});

// ── POST /sections/{section}/grades ───────────────────────────────────────────

test('professor can post a grade for a student in their section', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['student' => $student] = makeStudent($fac, $dept);

    $term    = makeOpenTerm();
    $section = makeSection($term);
    $profUser = $section->professor->user;

    $enrollment = Enrollment::create([
        'student_id'       => $student->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'registered',
        'registered_at'    => now(),
    ]);

    $this->actingAs($profUser)
         ->post(route('portal.sections.grade', $section), [
             'enrollment_id' => $enrollment->id,
             'midterm'       => 40,
             'final'         => 50,
             'total'         => 90,
             'letter_grade'  => 'A',
             'grade_points'  => 4.0,
         ])
         ->assertRedirect();

    $this->assertDatabaseHas('grades', [
        'enrollment_id' => $enrollment->id,
        'midterm'       => 40,
        'letter_grade'  => 'A',
    ]);
});

test('professor cannot post a grade on another professor\'s section', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['student' => $student] = makeStudent($fac, $dept);

    $term     = makeOpenTerm();
    $section  = makeSection($term);
    $section2 = makeSection($term);

    // Enrollment belongs to section, but we'll try to post as section2's professor
    $enrollment = Enrollment::create([
        'student_id'       => $student->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'registered',
        'registered_at'    => now(),
    ]);

    $otherProfUser = $section2->professor->user;

    $this->actingAs($otherProfUser)
         ->post(route('portal.sections.grade', $section), [
             'enrollment_id' => $enrollment->id,
             'midterm'       => 30,
             'total'         => 30,
         ])
         ->assertForbidden();
});
