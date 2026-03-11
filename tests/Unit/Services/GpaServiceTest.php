<?php

use App\Models\Enrollment;
use App\Models\Grade;
use App\Services\GpaService;

// ── GpaService::deriveStanding ─────────────────────────────────────────────────

test('deriveStanding returns null when gpa is null', function () {
    expect(GpaService::deriveStanding(null))->toBeNull();
});

test('deriveStanding returns good_standing for gpa >= 2.0', function () {
    expect(GpaService::deriveStanding(4.0))->toBe('good_standing');
    expect(GpaService::deriveStanding(2.0))->toBe('good_standing');
    expect(GpaService::deriveStanding(3.5))->toBe('good_standing');
});

test('deriveStanding returns probation for 1.0 <= gpa < 2.0', function () {
    expect(GpaService::deriveStanding(1.9))->toBe('probation');
    expect(GpaService::deriveStanding(1.0))->toBe('probation');
    expect(GpaService::deriveStanding(1.5))->toBe('probation');
});

test('deriveStanding returns dismissal for gpa < 1.0', function () {
    expect(GpaService::deriveStanding(0.9))->toBe('dismissal');
    expect(GpaService::deriveStanding(0.0))->toBe('dismissal');
});

// ── GpaService::recalculate ────────────────────────────────────────────────────

test('recalculate sets weighted GPA based on credit_hours and grade_points', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['student' => $student] = makeStudent($fac, $dept);

    $term = makeOpenTerm();

    // Section A: 3 credit-hour course, grade_points = 4.0  → weighted = 12
    $sectionA = makeSection($term);
    $sectionA->course()->update(['credit_hours' => 3]);

    // Section B: 2 credit-hour course, grade_points = 2.0  → weighted = 4
    $sectionB = makeSection($term);
    $sectionB->course()->update(['credit_hours' => 2]);

    $user = $student->user;

    $enrollA = Enrollment::create([
        'student_id'       => $student->id,
        'section_id'       => $sectionA->id,
        'academic_term_id' => $term->id,
        'status'           => 'completed',
        'registered_at'    => now(),
    ]);
    Grade::create([
        'enrollment_id' => $enrollA->id,
        'grade_points'  => 4.0,
        'graded_by'     => $user->id,
        'graded_at'     => now(),
    ]);

    $enrollB = Enrollment::create([
        'student_id'       => $student->id,
        'section_id'       => $sectionB->id,
        'academic_term_id' => $term->id,
        'status'           => 'completed',
        'registered_at'    => now(),
    ]);
    Grade::create([
        'enrollment_id' => $enrollB->id,
        'grade_points'  => 2.0,
        'graded_by'     => $user->id,
        'graded_at'     => now(),
    ]);

    GpaService::recalculate($student->id);

    // Expected GPA = (4.0 * 3 + 2.0 * 2) / (3 + 2) = 16 / 5 = 3.20
    $student->refresh();
    expect((float) $student->gpa)->toBe(3.20);
    expect($student->academic_standing)->toBe('good_standing');
});

test('recalculate sets gpa to null and standing to null when no graded courses exist', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['student' => $student] = makeStudent($fac, $dept);

    // Pre-set some values to ensure they get cleared
    $student->update(['gpa' => 3.5, 'academic_standing' => 'good_standing']);

    GpaService::recalculate($student->id);

    $student->refresh();
    expect($student->gpa)->toBeNull();
    expect($student->academic_standing)->toBeNull();
});

test('recalculate sets probation standing when GPA is between 1.0 and 2.0', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['student' => $student] = makeStudent($fac, $dept);
    $user = $student->user;

    $term    = makeOpenTerm();
    $section = makeSection($term);
    $section->course()->update(['credit_hours' => 3]);

    $enrollment = Enrollment::create([
        'student_id'       => $student->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'completed',
        'registered_at'    => now(),
    ]);
    Grade::create([
        'enrollment_id' => $enrollment->id,
        'grade_points'  => 1.5,
        'graded_by'     => $user->id,
        'graded_at'     => now(),
    ]);

    GpaService::recalculate($student->id);

    $student->refresh();
    expect($student->academic_standing)->toBe('probation');
});

test('recalculate sets dismissal standing when GPA is below 1.0', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['student' => $student] = makeStudent($fac, $dept);
    $user = $student->user;

    $term    = makeOpenTerm();
    $section = makeSection($term);
    $section->course()->update(['credit_hours' => 3]);

    $enrollment = Enrollment::create([
        'student_id'       => $student->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'completed',
        'registered_at'    => now(),
    ]);
    Grade::create([
        'enrollment_id' => $enrollment->id,
        'grade_points'  => 0.5,
        'graded_by'     => $user->id,
        'graded_at'     => now(),
    ]);

    GpaService::recalculate($student->id);

    $student->refresh();
    expect($student->academic_standing)->toBe('dismissal');
});
