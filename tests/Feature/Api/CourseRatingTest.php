<?php

use App\Models\AcademicTerm;
use App\Models\Course;
use App\Models\CourseRating;
use App\Models\Enrollment;
use App\Models\Section;

// ── helpers ──────────────────────────────────────────────────────────────────

/** Create an AcademicTerm that has already ended (for rating eligibility). */
function makeEndedTerm(string $suffix = ''): AcademicTerm
{
    static $e = 0;
    $e++;
    return AcademicTerm::create([
        'name'                   => "Ended Term {$e}{$suffix}",
        'name_ar'                => "فصل منتهي {$e}",
        'academic_year'          => 2024,
        'semester'               => 'first',
        'starts_at'              => today()->subYear()->toDateString(),
        'ends_at'                => today()->subMonth()->toDateString(),
        'registration_starts_at' => today()->subYears(2)->toDateString(),
        'registration_ends_at'   => today()->subYear()->subMonth()->toDateString(),
        'is_active'              => false,
    ]);
}

function makeRatingSection(\App\Models\Professor $professor, AcademicTerm $term): Section
{
    static $i = 0;
    $i++;
    $course = Course::create([
        'code'          => "RC{$i}",
        'name'          => "Rating Course {$i}",
        'name_ar'       => "مادة {$i}",
        'credit_hours'  => 3,
        'lecture_hours' => 2,
        'level'         => 1,
        'is_elective'   => false,
        'is_active'     => true,
    ]);
    return Section::create([
        'course_id'        => $course->id,
        'professor_id'     => $professor->id,
        'academic_term_id' => $term->id,
        'capacity'         => 30,
        'is_active'        => true,
    ]);
}

function makeCompletedEnrollment(\App\Models\Student $student, Section $section, AcademicTerm $term): Enrollment
{
    return Enrollment::create([
        'student_id'       => $student->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'completed',
        'registered_at'    => today()->subYear(),
    ]);
}

// ── POST /api/student/ratings ─────────────────────────────────────────────────

test('student can submit a rating for a completed course after term ends', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $studentUser, 'student' => $student] = makeStudent($fac, $dept);

    ['department' => $pd] = makeFacultyDeptFixture();
    ['professor' => $professor] = makeProfessor($pd);

    $term       = makeEndedTerm();
    $section    = makeRatingSection($professor, $term);
    $enrollment = makeCompletedEnrollment($student, $section, $term);

    $this->actingAs($studentUser, 'sanctum')
         ->postJson('/api/student/ratings', [
             'enrollment_id' => $enrollment->id,
             'rating'        => 4,
             'comment'       => 'Great course!',
         ])
         ->assertCreated()
         ->assertJsonPath('rating.rating', 4)
         ->assertJsonPath('rating.comment', 'Great course!');

    $this->assertDatabaseHas('course_ratings', [
        'enrollment_id' => $enrollment->id,
        'rating'        => 4,
    ]);
});

test('student can update their own rating (upsert)', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $studentUser, 'student' => $student] = makeStudent($fac, $dept);

    ['department' => $pd] = makeFacultyDeptFixture();
    ['professor' => $professor] = makeProfessor($pd);

    $term       = makeEndedTerm();
    $section    = makeRatingSection($professor, $term);
    $enrollment = makeCompletedEnrollment($student, $section, $term);

    CourseRating::create(['enrollment_id' => $enrollment->id, 'rating' => 3, 'rated_at' => now()]);

    $this->actingAs($studentUser, 'sanctum')
         ->postJson('/api/student/ratings', [
             'enrollment_id' => $enrollment->id,
             'rating'        => 5,
             'comment'       => 'Even better on reflection.',
         ])
         ->assertCreated()
         ->assertJsonPath('rating.rating', 5);

    // Still only one rating row
    expect(CourseRating::where('enrollment_id', $enrollment->id)->count())->toBe(1);
});

test('student cannot rate a course that is still in progress', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $studentUser, 'student' => $student] = makeStudent($fac, $dept);

    ['department' => $pd] = makeFacultyDeptFixture();
    ['professor' => $professor] = makeProfessor($pd);

    $term       = makeOpenTerm(); // ends in the future
    $section    = makeRatingSection($professor, $term);
    $enrollment = makeCompletedEnrollment($student, $section, $term);

    $this->actingAs($studentUser, 'sanctum')
         ->postJson('/api/student/ratings', [
             'enrollment_id' => $enrollment->id,
             'rating'        => 5,
         ])
         ->assertUnprocessable();
});

test('student cannot rate another student\'s enrollment', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $studentA] = makeStudent($fac, $dept);
    ['student' => $studentB] = makeStudent($fac, $dept);

    ['department' => $pd] = makeFacultyDeptFixture();
    ['professor' => $professor] = makeProfessor($pd);

    $term       = makeEndedTerm();
    $section    = makeRatingSection($professor, $term);
    $enrollment = makeCompletedEnrollment($studentB, $section, $term);

    $this->actingAs($studentA, 'sanctum')
         ->postJson('/api/student/ratings', [
             'enrollment_id' => $enrollment->id,
             'rating'        => 5,
         ])
         ->assertForbidden();
});

test('student cannot rate a still-registered enrollment', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $studentUser, 'student' => $student] = makeStudent($fac, $dept);

    ['department' => $pd] = makeFacultyDeptFixture();
    ['professor' => $professor] = makeProfessor($pd);

    $term    = makeEndedTerm();
    $section = makeRatingSection($professor, $term);
    $enrollment = Enrollment::create([
        'student_id'       => $student->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'registered',   // not completed
        'registered_at'    => today()->subYear(),
    ]);

    $this->actingAs($studentUser, 'sanctum')
         ->postJson('/api/student/ratings', [
             'enrollment_id' => $enrollment->id,
             'rating'        => 3,
         ])
         ->assertUnprocessable();
});

// ── GET /api/student/ratings ──────────────────────────────────────────────────

test('student can view their submitted ratings', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $studentUser, 'student' => $student] = makeStudent($fac, $dept);

    ['department' => $pd] = makeFacultyDeptFixture();
    ['professor' => $professor] = makeProfessor($pd);

    $term       = makeEndedTerm();
    $section    = makeRatingSection($professor, $term);
    $enrollment = makeCompletedEnrollment($student, $section, $term);

    CourseRating::create([
        'enrollment_id' => $enrollment->id,
        'rating'        => 4,
        'comment'       => 'Excellent!',
        'rated_at'      => now(),
    ]);

    $this->actingAs($studentUser, 'sanctum')
         ->getJson('/api/student/ratings')
         ->assertOk()
         ->assertJsonCount(1, 'ratings')
         ->assertJsonPath('ratings.0.rating', 4);
});

test('student sees only their own ratings', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $studentA, 'student' => $sA] = makeStudent($fac, $dept);
    ['user' => $studentB, 'student' => $sB] = makeStudent($fac, $dept);

    ['department' => $pd] = makeFacultyDeptFixture();
    ['professor' => $professor] = makeProfessor($pd);

    $term       = makeEndedTerm();
    $section    = makeRatingSection($professor, $term);
    $enrollA    = makeCompletedEnrollment($sA, $section, $term);

    CourseRating::create(['enrollment_id' => $enrollA->id, 'rating' => 5, 'rated_at' => now()]);

    $ratingsForB = $this->actingAs($studentB, 'sanctum')
                        ->getJson('/api/student/ratings')
                        ->assertOk()
                        ->json('ratings');

    expect($ratingsForB)->toBeEmpty();
});
