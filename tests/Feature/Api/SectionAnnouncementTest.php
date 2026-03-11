<?php

use App\Models\AcademicTerm;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Section;
use App\Models\SectionAnnouncement;

// ── helpers ──────────────────────────────────────────────────────────────────

function makeAnnSection(\App\Models\Professor $professor, AcademicTerm $term): Section
{
    static $i = 0;
    $i++;
    $course = Course::create([
        'code'          => "AN{$i}",
        'name'          => "Ann Course {$i}",
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

function makeAnnEnrollment(\App\Models\Student $student, Section $section, AcademicTerm $term): Enrollment
{
    return Enrollment::create([
        'student_id'       => $student->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'registered',
        'registered_at'    => now(),
    ]);
}

// ── GET /api/professor/sections/{section}/announcements ──────────────────────

test('professor can list announcements for their section', function () {
    ['department' => $dept] = makeFacultyDeptFixture();
    ['user' => $profUser, 'professor' => $professor] = makeProfessor($dept);

    $term    = makeOpenTerm();
    $section = makeAnnSection($professor, $term);

    SectionAnnouncement::create([
        'section_id'   => $section->id,
        'author_id'    => $profUser->id,
        'title'        => 'Test Announcement',
        'body'         => 'Details here.',
        'published_at' => now(),
    ]);

    $this->actingAs($profUser, 'sanctum')
         ->getJson("/api/professor/sections/{$section->id}/announcements")
         ->assertOk()
         ->assertJsonCount(1, 'announcements')
         ->assertJsonPath('announcements.0.title', 'Test Announcement');
});

test('professor cannot list announcements for another professor\'s section', function () {
    ['department' => $dept] = makeFacultyDeptFixture();
    ['user' => $profUser] = makeProfessor($dept);
    ['department' => $dept2] = makeFacultyDeptFixture();
    ['professor' => $other] = makeProfessor($dept2);

    $term    = makeOpenTerm();
    $section = makeAnnSection($other, $term);

    $this->actingAs($profUser, 'sanctum')
         ->getJson("/api/professor/sections/{$section->id}/announcements")
         ->assertForbidden();
});

// ── POST /api/professor/sections/{section}/announcements ─────────────────────

test('professor can post an announcement and students get notified', function () {
    ['department' => $dept] = makeFacultyDeptFixture();
    ['user' => $profUser, 'professor' => $professor] = makeProfessor($dept);

    ['faculty' => $fac, 'department' => $sd] = makeFacultyDeptFixture();
    ['user' => $studentUser, 'student' => $student] = makeStudent($fac, $sd);

    $term    = makeOpenTerm();
    $section = makeAnnSection($professor, $term);
    makeAnnEnrollment($student, $section, $term);

    $this->actingAs($profUser, 'sanctum')
         ->postJson("/api/professor/sections/{$section->id}/announcements", [
             'title' => 'Quiz Next Week',
             'body'  => 'Chapters 1-3 will be covered.',
         ])
         ->assertCreated()
         ->assertJsonPath('announcement.title', 'Quiz Next Week');

    $this->assertDatabaseHas('section_announcements', [
        'section_id' => $section->id,
        'title'      => 'Quiz Next Week',
    ]);

    // Student should have a database notification
    $this->assertDatabaseHas('notifications', [
        'notifiable_id'   => $studentUser->id,
        'notifiable_type' => \App\Models\User::class,
    ]);
});

test('professor cannot post an empty announcement', function () {
    ['department' => $dept] = makeFacultyDeptFixture();
    ['user' => $profUser, 'professor' => $professor] = makeProfessor($dept);

    $term    = makeOpenTerm();
    $section = makeAnnSection($professor, $term);

    $this->actingAs($profUser, 'sanctum')
         ->postJson("/api/professor/sections/{$section->id}/announcements", [
             'title' => '',
             'body'  => '',
         ])
         ->assertUnprocessable();
});

// ── DELETE /api/professor/sections/{section}/announcements/{announcement} ────

test('professor can delete their own announcement', function () {
    ['department' => $dept] = makeFacultyDeptFixture();
    ['user' => $profUser, 'professor' => $professor] = makeProfessor($dept);

    $term    = makeOpenTerm();
    $section = makeAnnSection($professor, $term);

    $announcement = SectionAnnouncement::create([
        'section_id'   => $section->id,
        'author_id'    => $profUser->id,
        'title'        => 'To be deleted',
        'body'         => 'Gone soon.',
        'published_at' => now(),
    ]);

    $this->actingAs($profUser, 'sanctum')
         ->deleteJson("/api/professor/sections/{$section->id}/announcements/{$announcement->id}")
         ->assertOk();

    $this->assertDatabaseMissing('section_announcements', ['id' => $announcement->id]);
});

// ── GET /api/student/sections/{section}/announcements ────────────────────────

test('student enrolled in section can view its announcements', function () {
    ['department' => $dept] = makeFacultyDeptFixture();
    ['user' => $profUser, 'professor' => $professor] = makeProfessor($dept);

    ['faculty' => $fac, 'department' => $sd] = makeFacultyDeptFixture();
    ['user' => $studentUser, 'student' => $student] = makeStudent($fac, $sd);

    $term    = makeOpenTerm();
    $section = makeAnnSection($professor, $term);
    makeAnnEnrollment($student, $section, $term);

    SectionAnnouncement::create([
        'section_id'   => $section->id,
        'author_id'    => $profUser->id,
        'title'        => 'Welcome',
        'body'         => 'Welcome to the course.',
        'published_at' => now(),
    ]);

    $this->actingAs($studentUser, 'sanctum')
         ->getJson("/api/student/sections/{$section->id}/announcements")
         ->assertOk()
         ->assertJsonCount(1, 'announcements')
         ->assertJsonPath('announcements.0.title', 'Welcome');
});

test('student not enrolled in section cannot view its announcements', function () {
    ['department' => $dept] = makeFacultyDeptFixture();
    ['professor' => $professor] = makeProfessor($dept);

    ['faculty' => $fac, 'department' => $sd] = makeFacultyDeptFixture();
    ['user' => $outsider] = makeStudent($fac, $sd);

    $term    = makeOpenTerm();
    $section = makeAnnSection($professor, $term);
    // No enrollment for outsider

    $this->actingAs($outsider, 'sanctum')
         ->getJson("/api/student/sections/{$section->id}/announcements")
         ->assertForbidden();
});
