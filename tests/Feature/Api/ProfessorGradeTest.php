<?php

use App\Models\AcademicTerm;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Section;

// ── helpers ─────────────────────────────────────────────────────────────────

/**
 * Create a Section owned by the given professor in the given term.
 */
function makeProfessorSection(\App\Models\Professor $professor, AcademicTerm $term): Section
{
    static $ps = 0;
    $ps++;

    $course = Course::create([
        'code'          => "GC{$ps}",
        'name'          => "Grade Course {$ps}",
        'name_ar'       => "مادة {$ps}",
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

/**
 * Create an Enrollment for the given student in the given section/term.
 */
function makeEnrollment(\App\Models\Student $student, Section $section, AcademicTerm $term): Enrollment
{
    return Enrollment::create([
        'student_id'       => $student->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'registered',
        'registered_at'    => now(),
    ]);
}

// ── POST /api/professor/sections/{section}/grades ────────────────────────────

test('professor can submit a grade for a student in their section', function () {
    ['department' => $dept] = makeFacultyDeptFixture();
    ['user' => $profUser, 'professor' => $professor] = makeProfessor($dept);

    ['faculty' => $fac, 'department' => $sd] = makeFacultyDeptFixture();
    ['student' => $student] = makeStudent($fac, $sd);

    $term       = AcademicTerm::create([
        'name'                   => 'Grade Term',
        'name_ar'                => 'فصل الدرجات',
        'academic_year'          => 2026,
        'semester'               => 'second',
        'starts_at'              => today()->subMonth()->toDateString(),
        'ends_at'                => today()->addMonths(3)->toDateString(),
        'registration_starts_at' => today()->subMonths(2)->toDateString(),
        'registration_ends_at'   => today()->subMonth()->toDateString(),
    ]);
    $section    = makeProfessorSection($professor, $term);
    $enrollment = makeEnrollment($student, $section, $term);

    $this->actingAs($profUser, 'sanctum')
         ->postJson("/api/professor/sections/{$section->id}/grades", [
             'enrollment_id' => $enrollment->id,
             'midterm'       => 40,
             'final'         => 50,
             'coursework'    => 10,
             'total'         => 85,
             'letter_grade'  => 'B',
             'grade_points'  => 3.00,
         ])
         ->assertCreated()
         ->assertJsonPath('grade.letter_grade', 'B');

    $this->assertDatabaseHas('grades', [
        'enrollment_id' => $enrollment->id,
        'letter_grade'  => 'B',
        'graded_by'     => $profUser->id,
    ]);
});

test('professor can update an existing grade', function () {
    ['department' => $dept] = makeFacultyDeptFixture();
    ['user' => $profUser, 'professor' => $professor] = makeProfessor($dept);

    ['faculty' => $fac, 'department' => $sd] = makeFacultyDeptFixture();
    ['student' => $student] = makeStudent($fac, $sd);

    $term       = AcademicTerm::create([
        'name'                   => 'Update Grade Term',
        'name_ar'                => 'فصل تعديل',
        'academic_year'          => 2026,
        'semester'               => 'summer',
        'starts_at'              => today()->subMonth()->toDateString(),
        'ends_at'                => today()->addMonths(3)->toDateString(),
        'registration_starts_at' => today()->subMonths(2)->toDateString(),
        'registration_ends_at'   => today()->subMonth()->toDateString(),
    ]);
    $section    = makeProfessorSection($professor, $term);
    $enrollment = makeEnrollment($student, $section, $term);

    // Pre-existing grade
    Grade::create([
        'enrollment_id' => $enrollment->id,
        'midterm'       => 30,
        'final'         => 40,
        'coursework'    => 5,
        'total'         => 65,
        'letter_grade'  => 'C',
        'grade_points'  => 2.00,
        'graded_by'     => $profUser->id,
        'graded_at'     => now(),
    ]);

    $this->actingAs($profUser, 'sanctum')
         ->postJson("/api/professor/sections/{$section->id}/grades", [
             'enrollment_id' => $enrollment->id,
             'total'         => 90,
             'letter_grade'  => 'A',
             'grade_points'  => 4.00,
         ])
         ->assertOk()
         ->assertJsonPath('grade.letter_grade', 'A');
});

test('professor cannot grade a section owned by another professor', function () {
    ['department' => $dept] = makeFacultyDeptFixture();
    ['user' => $profUser] = makeProfessor($dept);

    // Another professor owns this section
    ['department' => $dept2] = makeFacultyDeptFixture();
    ['professor' => $otherProf] = makeProfessor($dept2);

    $term    = AcademicTerm::create([
        'name'                   => 'Other Term',
        'name_ar'                => 'فصل آخر',
        'academic_year'          => 2025,
        'semester'               => 'first',
        'starts_at'              => today()->subMonths(6)->toDateString(),
        'ends_at'                => today()->subMonth()->toDateString(),
        'registration_starts_at' => today()->subMonths(7)->toDateString(),
        'registration_ends_at'   => today()->subMonths(6)->toDateString(),
    ]);
    $section = makeProfessorSection($otherProf, $term);

    $this->actingAs($profUser, 'sanctum')
         ->postJson("/api/professor/sections/{$section->id}/grades", [
             'enrollment_id' => 1,
             'total'         => 80,
         ])
         ->assertForbidden();
});

test('enrollment not belonging to section is rejected', function () {
    ['department' => $dept] = makeFacultyDeptFixture();
    ['user' => $profUser, 'professor' => $professor] = makeProfessor($dept);

    ['faculty' => $fac, 'department' => $sd] = makeFacultyDeptFixture();
    ['student' => $student] = makeStudent($fac, $sd);

    // Two terms (different semesters to avoid unique constraint)
    $term1 = AcademicTerm::create([
        'name'                   => 'Term A',
        'name_ar'                => 'فصل أ',
        'academic_year'          => 2024,
        'semester'               => 'first',
        'starts_at'              => '2024-09-01',
        'ends_at'                => '2025-01-31',
        'registration_starts_at' => '2024-08-01',
        'registration_ends_at'   => '2024-09-15',
    ]);
    $term2 = AcademicTerm::create([
        'name'                   => 'Term B',
        'name_ar'                => 'فصل ب',
        'academic_year'          => 2024,
        'semester'               => 'second',
        'starts_at'              => '2025-02-01',
        'ends_at'                => '2025-06-30',
        'registration_starts_at' => '2025-01-01',
        'registration_ends_at'   => '2025-02-14',
    ]);

    $sectionA = makeProfessorSection($professor, $term1);
    $sectionB = makeProfessorSection($professor, $term2);

    $enrollment = makeEnrollment($student, $sectionB, $term2); // enrolled in B

    // Try to submit grade for sectionA but pass enrollment from sectionB
    $this->actingAs($profUser, 'sanctum')
         ->postJson("/api/professor/sections/{$sectionA->id}/grades", [
             'enrollment_id' => $enrollment->id,
             'total'         => 80,
             'letter_grade'  => 'B',
         ])
         ->assertUnprocessable()
         ->assertJsonFragment(['message' => 'Enrollment does not belong to this section.']);
});

test('unauthenticated user cannot submit grades', function () {
    $this->postJson('/api/professor/sections/1/grades', [])->assertUnauthorized();
});

test('posting a grade via API recalculates student GPA and academic standing', function () {
    ['department' => $dept] = makeFacultyDeptFixture();
    ['user' => $profUser, 'professor' => $professor] = makeProfessor($dept);

    ['faculty' => $fac, 'department' => $sd] = makeFacultyDeptFixture();
    ['student' => $student] = makeStudent($fac, $sd);

    $term       = AcademicTerm::create([
        'name'                   => 'GPA Integration Term',
        'name_ar'                => 'فصل تكامل',
        'academic_year'          => 2027,
        'semester'               => 'first',
        'starts_at'              => today()->subMonth()->toDateString(),
        'ends_at'                => today()->addMonths(3)->toDateString(),
        'registration_starts_at' => today()->subMonths(2)->toDateString(),
        'registration_ends_at'   => today()->subMonth()->toDateString(),
    ]);
    $section    = makeProfessorSection($professor, $term);
    $enrollment = makeEnrollment($student, $section, $term);

    $this->actingAs($profUser, 'sanctum')
         ->postJson("/api/professor/sections/{$section->id}/grades", [
             'enrollment_id' => $enrollment->id,
             'total'         => 95,
             'letter_grade'  => 'A',
             'grade_points'  => 4.00,
         ])
         ->assertCreated();

    $student->refresh();
    expect((float) $student->gpa)->toBe(4.0);
    expect($student->academic_standing)->toBe('good_standing');
});
