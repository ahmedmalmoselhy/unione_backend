<?php

use App\Models\AcademicTerm;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Section;

// ── GET /api/student/profile ────────────────────────────────────────────────

test('student can retrieve own profile', function () {
    ['faculty' => $faculty, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user, 'student' => $student] = makeStudent($faculty, $dept);

    $response = $this->actingAs($user, 'sanctum')
                     ->getJson('/api/student/profile');

    $response->assertOk()
             ->assertJsonPath('student.student_number', $student->student_number)
             ->assertJsonPath('student.faculty.id', $faculty->id)
             ->assertJsonPath('student.department.id', $dept->id);
});

test('user without a student record gets 404 on profile', function () {
    $admin = createUserWithRole('student'); // role exists but no Student model row

    $this->actingAs($admin, 'sanctum')
         ->getJson('/api/student/profile')
         ->assertNotFound();
});

// ── GET /api/student/enrollments ────────────────────────────────────────────

test('student can retrieve enrollments with grades', function () {
    ['faculty' => $faculty, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user, 'student' => $student] = makeStudent($faculty, $dept);
    ['professor' => $professor] = makeProfessor($dept);

    $term = AcademicTerm::create([
        'name'                    => 'Term 1',
        'name_ar'                 => 'الفصل 1',
        'academic_year'           => 2025,
        'semester'                => 'first',
        'starts_at'               => '2025-09-01',
        'ends_at'                 => '2026-01-31',
        'registration_starts_at'  => '2025-08-01',
        'registration_ends_at'    => '2025-09-15',
    ]);

    $course = \App\Models\Course::create([
        'code'          => 'CS101',
        'name'          => 'Intro to CS',
        'name_ar'       => 'مقدمة في علوم الحاسوب',
        'credit_hours'  => 3,
        'lecture_hours' => 2,
        'level'         => 1,
        'is_elective'   => false,
        'is_active'     => true,
    ]);

    $section = Section::create([
        'course_id'        => $course->id,
        'professor_id'     => $professor->id,
        'academic_term_id' => $term->id,
        'capacity'         => 30,
        'is_active'        => true,
    ]);

    $enrollment = Enrollment::create([
        'student_id'       => $student->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'completed',
        'registered_at'    => now(),
    ]);

    Grade::create([
        'enrollment_id' => $enrollment->id,
        'midterm'       => 40,
        'final'         => 50,
        'coursework'    => 10,
        'total'         => 85,
        'letter_grade'  => 'B',
        'grade_points'  => 3.00,
        'graded_by'     => $user->id,
        'graded_at'     => now(),
    ]);

    $response = $this->actingAs($user, 'sanctum')
                     ->getJson('/api/student/enrollments');

    $response->assertOk()
             ->assertJsonCount(1, 'enrollments')
             ->assertJsonPath('enrollments.0.status', 'completed')
             ->assertJsonPath('enrollments.0.course.code', 'CS101')
             ->assertJsonPath('enrollments.0.grade.letter_grade', 'B');
});

test('student with no enrollments gets an empty list', function () {
    ['faculty' => $faculty, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user] = makeStudent($faculty, $dept);

    $this->actingAs($user, 'sanctum')
         ->getJson('/api/student/enrollments')
         ->assertOk()
         ->assertJsonCount(0, 'enrollments');
});

// ── GET /api/student/grades ─────────────────────────────────────────────────

test('student can retrieve their grades', function () {
    ['faculty' => $faculty, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user, 'student' => $student] = makeStudent($faculty, $dept);

    $term    = makeOpenTerm();
    $section = makeSection($term);
    $enrollment = Enrollment::create([
        'student_id'       => $student->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'completed',
        'registered_at'    => now()->subMonth(),
    ]);
    Grade::create([
        'enrollment_id' => $enrollment->id,
        'midterm'       => 40,
        'final'         => 50,
        'total'         => 90,
        'letter_grade'  => 'A',
        'grade_points'  => 4.0,
        'graded_by'     => $user->id,
        'graded_at'     => now(),
    ]);

    $this->actingAs($user, 'sanctum')
         ->getJson('/api/student/grades')
         ->assertOk()
         ->assertJsonStructure(['grades'])
         ->assertJsonCount(1, 'grades');
});

test('student with no grades gets empty grades list', function () {
    ['faculty' => $faculty, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user] = makeStudent($faculty, $dept);

    $this->actingAs($user, 'sanctum')
         ->getJson('/api/student/grades')
         ->assertOk()
         ->assertJsonCount(0, 'grades');
});

// ── GET /api/student/schedule ────────────────────────────────────────────────

test('student can retrieve their schedule', function () {
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
         ->getJson('/api/student/schedule')
         ->assertOk()
         ->assertJsonStructure(['schedule']);
});

test('student with no active-term enrollment gets empty schedule', function () {
    ['faculty' => $faculty, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user] = makeStudent($faculty, $dept);

    $this->actingAs($user, 'sanctum')
         ->getJson('/api/student/schedule')
         ->assertOk()
         ->assertJsonStructure(['schedule']);
});
