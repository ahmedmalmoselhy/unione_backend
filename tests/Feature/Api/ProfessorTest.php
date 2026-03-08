<?php

use App\Models\AcademicTerm;
use App\Models\Course;
use App\Models\Section;

// ── GET /api/professor/profile ───────────────────────────────────────────────

test('professor can retrieve own profile', function () {
    ['faculty' => $faculty, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user, 'professor' => $professor] = makeProfessor($dept);

    $response = $this->actingAs($user, 'sanctum')
                     ->getJson('/api/professor/profile');

    $response->assertOk()
             ->assertJsonPath('professor.staff_number', $professor->staff_number)
             ->assertJsonPath('professor.department.id', $dept->id)
             ->assertJsonPath('professor.department.faculty.id', $faculty->id);
});

test('user with professor role but no professor record gets 404', function () {
    $user = createUserWithRole('professor'); // role but no professor row

    $this->actingAs($user, 'sanctum')
         ->getJson('/api/professor/profile')
         ->assertNotFound();
});

// ── GET /api/professor/sections ─────────────────────────────────────────────

test('professor can retrieve their sections', function () {
    ['department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user, 'professor' => $professor] = makeProfessor($dept);

    $term = AcademicTerm::create([
        'name'                    => 'Term 2',
        'name_ar'                 => 'الفصل 2',
        'academic_year'           => 2025,
        'semester'                => 'second',
        'starts_at'               => '2026-02-01',
        'ends_at'                 => '2026-06-30',
        'registration_starts_at'  => '2026-01-01',
        'registration_ends_at'    => '2026-02-14',
    ]);

    $course = Course::create([
        'code'          => 'MATH201',
        'name'          => 'Calculus',
        'name_ar'       => 'التفاضل والتكامل',
        'credit_hours'  => 4,
        'lecture_hours' => 3,
        'level'         => 2,
        'is_elective'   => false,
        'is_active'     => true,
    ]);

    Section::create([
        'course_id'        => $course->id,
        'professor_id'     => $professor->id,
        'academic_term_id' => $term->id,
        'capacity'         => 25,
        'is_active'        => true,
    ]);

    $response = $this->actingAs($user, 'sanctum')
                     ->getJson('/api/professor/sections');

    $response->assertOk()
             ->assertJsonCount(1, 'sections')
             ->assertJsonPath('sections.0.course.code', 'MATH201')
             ->assertJsonPath('sections.0.academic_term.semester', 'second')
             ->assertJsonPath('sections.0.enrollments_count', 0);
});

test('professor with no sections gets an empty list', function () {
    ['department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user] = makeProfessor($dept);

    $this->actingAs($user, 'sanctum')
         ->getJson('/api/professor/sections')
         ->assertOk()
         ->assertJsonCount(0, 'sections');
});
