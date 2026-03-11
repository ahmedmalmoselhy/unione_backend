<?php

use App\Models\AcademicTerm;
use App\Models\Department;
use App\Models\Enrollment;
use App\Models\Faculty;
use App\Models\Student;
use App\Models\StudentDepartmentHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

test('student belongs to a user', function () {
    $student = new Student();
    expect($student->user())->toBeInstanceOf(BelongsTo::class);
});

test('student belongs to a faculty', function () {
    $student = new Student();
    expect($student->faculty())->toBeInstanceOf(BelongsTo::class);
});

test('student belongs to a department', function () {
    $student = new Student();
    expect($student->department())->toBeInstanceOf(BelongsTo::class);
});

test('student has many enrollments', function () {
    $student = new Student();
    expect($student->enrollments())->toBeInstanceOf(HasMany::class);
});

test('student has many department history records', function () {
    $student = new Student();
    expect($student->departmentHistory())->toBeInstanceOf(HasMany::class);
});

test('student user relationship resolves correctly', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user, 'student' => $student] = makeStudent($fac, $dept);

    expect($student->user->id)->toBe($user->id);
});

test('student enrollments relationship resolves correctly', function () {
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

    expect($student->enrollments()->count())->toBe(1);
});
