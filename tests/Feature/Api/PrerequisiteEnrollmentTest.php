<?php

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Section;

// Helper: attach a prerequisite course to the section's course, and return the prereq
function makePrerequisiteCourse(Section $section, string $suffix = ''): Course
{
    static $n = 0;
    $n++;

    $prereq = Course::create([
        'code'          => "PRE{$n}{$suffix}",
        'name'          => "Prerequisite {$n}",
        'name_ar'       => "متطلب {$n}",
        'credit_hours'  => 3,
        'lecture_hours' => 2,
        'level'         => 1,
        'is_elective'   => false,
        'is_active'     => true,
    ]);

    $section->course->prerequisites()->attach($prereq->id);

    return $prereq;
}

// ── Prerequisite enforcement ──────────────────────────────────────────────────

test('student can enroll when no prerequisites are set', function () {
    ['faculty' => $f, 'department' => $d] = makeFacultyDeptFixture();
    ['user' => $u, 'student' => $s] = makeStudent($f, $d);

    $term    = makeOpenTerm();
    $section = makeSection($term);

    // No prerequisites attached

    $this->actingAs($u, 'sanctum')
         ->postJson('/api/student/enrollments', ['section_id' => $section->id])
         ->assertCreated();
});

test('student cannot enroll when prerequisite is not completed', function () {
    ['faculty' => $f, 'department' => $d] = makeFacultyDeptFixture();
    ['user' => $u, 'student' => $s] = makeStudent($f, $d);

    $term    = makeOpenTerm();
    $section = makeSection($term);
    $prereq  = makePrerequisiteCourse($section);

    $this->actingAs($u, 'sanctum')
         ->postJson('/api/student/enrollments', ['section_id' => $section->id])
         ->assertUnprocessable()
         ->assertJsonFragment(['message' => 'Prerequisites not satisfied.'])
         ->assertJsonPath('missing_prerequisites.0.code', $prereq->code);
});

test('student can enroll when prerequisite is completed', function () {
    ['faculty' => $f, 'department' => $d] = makeFacultyDeptFixture();
    ['user' => $u, 'student' => $s] = makeStudent($f, $d);

    $term    = makeOpenTerm();
    $prereqSection = makeSection($term);
    $prereq  = $prereqSection->course;

    $targetSection = makeSection($term);
    $targetSection->course->prerequisites()->attach($prereq->id);

    // Create a completed enrollment in the prerequisite course
    $prereqEnrollment = Enrollment::create([
        'student_id'       => $s->id,
        'section_id'       => $prereqSection->id,
        'academic_term_id' => $term->id,
        'status'           => 'completed',
        'registered_at'    => now(),
    ]);

    $this->actingAs($u, 'sanctum')
         ->postJson('/api/student/enrollments', ['section_id' => $targetSection->id])
         ->assertCreated();
});

test('student cannot enroll when prerequisite is only registered (not completed)', function () {
    ['faculty' => $f, 'department' => $d] = makeFacultyDeptFixture();
    ['user' => $u, 'student' => $s] = makeStudent($f, $d);

    $term    = makeOpenTerm();
    $prereqSection = makeSection($term);
    $prereq  = $prereqSection->course;

    $targetSection = makeSection($term);
    $targetSection->course->prerequisites()->attach($prereq->id);

    // Registered (not completed) — should not satisfy
    Enrollment::create([
        'student_id'       => $s->id,
        'section_id'       => $prereqSection->id,
        'academic_term_id' => $term->id,
        'status'           => 'registered',
        'registered_at'    => now(),
    ]);

    $this->actingAs($u, 'sanctum')
         ->postJson('/api/student/enrollments', ['section_id' => $targetSection->id])
         ->assertUnprocessable()
         ->assertJsonFragment(['message' => 'Prerequisites not satisfied.']);
});

test('all missing prerequisites are returned in the error response', function () {
    ['faculty' => $f, 'department' => $d] = makeFacultyDeptFixture();
    ['user' => $u, 'student' => $s] = makeStudent($f, $d);

    $term    = makeOpenTerm();
    $section = makeSection($term);
    $p1 = makePrerequisiteCourse($section, 'A');
    $p2 = makePrerequisiteCourse($section, 'B');

    $response = $this->actingAs($u, 'sanctum')
         ->postJson('/api/student/enrollments', ['section_id' => $section->id])
         ->assertUnprocessable();

    expect($response->json('missing_prerequisites'))->toHaveCount(2);
});
