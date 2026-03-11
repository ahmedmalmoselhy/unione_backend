<?php

use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

test('grade belongs to an enrollment', function () {
    $grade = new Grade();
    expect($grade->enrollment())->toBeInstanceOf(BelongsTo::class);
});

test('grade belongs to the user who graded it', function () {
    $grade = new Grade();
    expect($grade->gradedBy())->toBeInstanceOf(BelongsTo::class);
});

test('grade enrollment relationship resolves correctly', function () {
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

    $grader = createUserWithRole('professor');
    $grade = Grade::create([
        'enrollment_id' => $enrollment->id,
        'midterm'       => 40,
        'final'         => 50,
        'total'         => 90,
        'letter_grade'  => 'A',
        'grade_points'  => 4.0,
        'graded_by'     => $grader->id,
        'graded_at'     => now(),
    ]);

    expect($grade->enrollment->id)->toBe($enrollment->id);
    expect($grade->gradedBy->id)->toBe($grader->id);
});
