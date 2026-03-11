<?php

use App\Models\AcademicTerm;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Professor;
use App\Models\Section;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

test('section belongs to a course', function () {
    $section = new Section();
    expect($section->course())->toBeInstanceOf(BelongsTo::class);
});

test('section belongs to a professor', function () {
    $section = new Section();
    expect($section->professor())->toBeInstanceOf(BelongsTo::class);
});

test('section belongs to an academic term', function () {
    $section = new Section();
    expect($section->academicTerm())->toBeInstanceOf(BelongsTo::class);
});

test('section has many enrollments', function () {
    $section = new Section();
    expect($section->enrollments())->toBeInstanceOf(HasMany::class);
});

test('section relationships resolve correctly', function () {
    $term    = makeOpenTerm();
    $section = makeSection($term);

    expect($section->course)->toBeInstanceOf(Course::class);
    expect($section->professor)->toBeInstanceOf(Professor::class);
    expect($section->academicTerm->id)->toBe($term->id);
    expect($section->enrollments()->count())->toBe(0);
});

test('section enrollment count increases when students enroll', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['student' => $student] = makeStudent($fac, $dept);

    $term    = makeOpenTerm();
    $section = makeSection($term);

    Enrollment::create([
        'student_id'       => $student->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'registered',
        'registered_at'    => now(),
    ]);

    expect($section->enrollments()->count())->toBe(1);
});
