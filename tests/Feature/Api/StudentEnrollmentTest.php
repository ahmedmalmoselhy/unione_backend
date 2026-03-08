<?php

use App\Models\AcademicTerm;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Section;

// ── helpers ─────────────────────────────────────────────────────────────────

/**
 * Create an AcademicTerm whose registration window is currently open.
 */
function makeOpenTerm(string $suffix = ''): AcademicTerm
{
    static $t = 0;
    $t++;

    return AcademicTerm::create([
        'name'                   => "Open Term {$t}{$suffix}",
        'name_ar'                => "فصل مفتوح {$t}",
        'academic_year'          => 2026,
        'semester'               => 'first',
        'starts_at'              => today()->subMonth()->toDateString(),
        'ends_at'                => today()->addMonths(4)->toDateString(),
        'registration_starts_at' => today()->subDays(10)->toDateString(),
        'registration_ends_at'   => today()->addDays(10)->toDateString(),
    ]);
}

/**
 * Create an AcademicTerm whose registration window has closed.
 */
function makeClosedTerm(string $suffix = ''): AcademicTerm
{
    static $c = 0;
    $c++;

    return AcademicTerm::create([
        'name'                   => "Closed Term {$c}{$suffix}",
        'name_ar'                => "فصل مغلق {$c}",
        'academic_year'          => 2025,
        'semester'               => 'second',
        'starts_at'              => today()->subMonths(6)->toDateString(),
        'ends_at'                => today()->subMonths(2)->toDateString(),
        'registration_starts_at' => today()->subMonths(7)->toDateString(),
        'registration_ends_at'   => today()->subMonths(6)->toDateString(),
    ]);
}

function makeSection(AcademicTerm $term, int $capacity = 30): Section
{
    static $sc = 0;
    $sc++;

    $course = Course::create([
        'code'          => "CRS{$sc}",
        'name'          => "Course {$sc}",
        'name_ar'       => "مادة {$sc}",
        'credit_hours'  => 3,
        'lecture_hours' => 2,
        'level'         => 1,
        'is_elective'   => false,
        'is_active'     => true,
    ]);

    ['department' => $dept] = makeFacultyDeptFixture("SC{$sc}");
    ['professor' => $prof]  = makeProfessor($dept);

    return Section::create([
        'course_id'        => $course->id,
        'professor_id'     => $prof->id,
        'academic_term_id' => $term->id,
        'capacity'         => $capacity,
        'is_active'        => true,
    ]);
}

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

test('student cannot enroll in a full section', function () {
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
         ->assertUnprocessable()
         ->assertJsonFragment(['message' => 'Section is at full capacity.']);
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
