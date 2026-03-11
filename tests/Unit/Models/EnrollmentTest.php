<?php

use App\Models\AcademicTerm;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

test('enrollment belongs to a student', function () {
    $enrollment = new Enrollment();
    expect($enrollment->student())->toBeInstanceOf(BelongsTo::class);
});

test('enrollment belongs to a section', function () {
    $enrollment = new Enrollment();
    expect($enrollment->section())->toBeInstanceOf(BelongsTo::class);
});

test('enrollment belongs to an academic term', function () {
    $enrollment = new Enrollment();
    expect($enrollment->academicTerm())->toBeInstanceOf(BelongsTo::class);
});

test('enrollment has one grade', function () {
    $enrollment = new Enrollment();
    expect($enrollment->grade())->toBeInstanceOf(HasOne::class);
});

test('enrollment relationships resolve correctly', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['student' => $student] = makeStudent($fac, $dept);

    $term    = makeOpenTerm();
    $section = makeSection($term);

    $enrollment = Enrollment::create([
        'student_id'       => $student->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'registered',
        'registered_at'    => now(),
    ]);

    expect($enrollment->student->id)->toBe($student->id);
    expect($enrollment->section->id)->toBe($section->id);
    expect($enrollment->academicTerm->id)->toBe($term->id);
    expect($enrollment->grade)->toBeNull();
});
