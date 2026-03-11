<?php

use App\Models\AcademicTerm;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Section;

// ── helpers ──────────────────────────────────────────────────────────────────

function makeAttSection(\App\Models\Professor $professor, AcademicTerm $term): Section
{
    static $i = 0;
    $i++;
    $course = Course::create([
        'code'          => "AC{$i}",
        'name'          => "Attendance Course {$i}",
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

function makeAttEnrollment(\App\Models\Student $student, Section $section, AcademicTerm $term): Enrollment
{
    return Enrollment::create([
        'student_id'       => $student->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'registered',
        'registered_at'    => now(),
    ]);
}

// ── GET /api/professor/sections/{section}/attendance ─────────────────────────

test('professor can list attendance sessions for their section', function () {
    ['department' => $dept] = makeFacultyDeptFixture();
    ['user' => $profUser, 'professor' => $professor] = makeProfessor($dept);

    $term    = makeOpenTerm();
    $section = makeAttSection($professor, $term);

    AttendanceSession::create([
        'section_id'   => $section->id,
        'created_by'   => $profUser->id,
        'session_date' => today()->toDateString(),
        'topic'        => 'Intro',
    ]);

    $this->actingAs($profUser, 'sanctum')
         ->getJson("/api/professor/sections/{$section->id}/attendance")
         ->assertOk()
         ->assertJsonCount(1, 'sessions')
         ->assertJsonPath('sessions.0.topic', 'Intro');
});

test('professor cannot view attendance for another professor\'s section', function () {
    ['department' => $dept] = makeFacultyDeptFixture();
    ['user' => $profUser] = makeProfessor($dept);
    ['department' => $dept2] = makeFacultyDeptFixture();
    ['professor' => $other] = makeProfessor($dept2);

    $term    = makeOpenTerm();
    $section = makeAttSection($other, $term);

    $this->actingAs($profUser, 'sanctum')
         ->getJson("/api/professor/sections/{$section->id}/attendance")
         ->assertForbidden();
});

// ── POST /api/professor/sections/{section}/attendance ────────────────────────

test('professor can create an attendance session with student records', function () {
    ['department' => $dept] = makeFacultyDeptFixture();
    ['user' => $profUser, 'professor' => $professor] = makeProfessor($dept);

    ['faculty' => $fac, 'department' => $sd] = makeFacultyDeptFixture();
    ['student' => $student] = makeStudent($fac, $sd);

    $term       = makeOpenTerm();
    $section    = makeAttSection($professor, $term);
    makeAttEnrollment($student, $section, $term);

    $this->actingAs($profUser, 'sanctum')
         ->postJson("/api/professor/sections/{$section->id}/attendance", [
             'session_date' => today()->toDateString(),
             'topic'        => 'Chapter 1',
             'records'      => [
                 ['student_id' => $student->id, 'status' => 'present'],
             ],
         ])
         ->assertCreated()
         ->assertJsonPath('session.session_date', today()->toDateString());

    $this->assertDatabaseHas('attendance_sessions', [
        'section_id' => $section->id,
        'topic'      => 'Chapter 1',
    ]);
    $this->assertDatabaseHas('attendance_records', [
        'student_id' => $student->id,
        'status'     => 'present',
    ]);
});

test('professor cannot add a student not enrolled in the section to attendance', function () {
    ['department' => $dept] = makeFacultyDeptFixture();
    ['user' => $profUser, 'professor' => $professor] = makeProfessor($dept);

    ['faculty' => $fac, 'department' => $sd] = makeFacultyDeptFixture();
    ['student' => $outsider] = makeStudent($fac, $sd);

    $term    = makeOpenTerm();
    $section = makeAttSection($professor, $term);
    // No enrollment created — outsider is not enrolled

    $this->actingAs($profUser, 'sanctum')
         ->postJson("/api/professor/sections/{$section->id}/attendance", [
             'session_date' => today()->toDateString(),
             'records'      => [
                 ['student_id' => $outsider->id, 'status' => 'present'],
             ],
         ])
         ->assertUnprocessable();
});

// ── GET /api/professor/sections/{section}/attendance/{session} ───────────────

test('professor can view a single attendance session with records', function () {
    ['department' => $dept] = makeFacultyDeptFixture();
    ['user' => $profUser, 'professor' => $professor] = makeProfessor($dept);

    ['faculty' => $fac, 'department' => $sd] = makeFacultyDeptFixture();
    ['student' => $student] = makeStudent($fac, $sd);

    $term    = makeOpenTerm();
    $section = makeAttSection($professor, $term);
    makeAttEnrollment($student, $section, $term);

    $session = AttendanceSession::create([
        'section_id'   => $section->id,
        'created_by'   => $profUser->id,
        'session_date' => today()->toDateString(),
    ]);
    AttendanceRecord::create([
        'attendance_session_id' => $session->id,
        'student_id'            => $student->id,
        'status'                => 'absent',
    ]);

    $this->actingAs($profUser, 'sanctum')
         ->getJson("/api/professor/sections/{$section->id}/attendance/{$session->id}")
         ->assertOk()
         ->assertJsonPath('session.id', $session->id)
         ->assertJsonCount(1, 'records');
});

// ── PUT /api/professor/sections/{section}/attendance/{session} ───────────────

test('professor can update attendance record statuses', function () {
    ['department' => $dept] = makeFacultyDeptFixture();
    ['user' => $profUser, 'professor' => $professor] = makeProfessor($dept);

    ['faculty' => $fac, 'department' => $sd] = makeFacultyDeptFixture();
    ['student' => $student] = makeStudent($fac, $sd);

    $term    = makeOpenTerm();
    $section = makeAttSection($professor, $term);
    makeAttEnrollment($student, $section, $term);

    $session = AttendanceSession::create([
        'section_id'   => $section->id,
        'created_by'   => $profUser->id,
        'session_date' => today()->toDateString(),
    ]);
    $record = AttendanceRecord::create([
        'attendance_session_id' => $session->id,
        'student_id'            => $student->id,
        'status'                => 'absent',
    ]);

    $this->actingAs($profUser, 'sanctum')
         ->putJson("/api/professor/sections/{$section->id}/attendance/{$session->id}", [
             'records' => [
                 ['student_id' => $student->id, 'status' => 'excused', 'note' => 'Medical'],
             ],
         ])
         ->assertOk();

    $this->assertDatabaseHas('attendance_records', [
        'id'     => $record->id,
        'status' => 'excused',
        'note'   => 'Medical',
    ]);
});

// ── GET /api/student/attendance ───────────────────────────────────────────────

test('student can view their own attendance records', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $studentUser, 'student' => $student] = makeStudent($fac, $dept);

    ['department' => $pd] = makeFacultyDeptFixture();
    ['professor' => $professor, 'user' => $profUser] = makeProfessor($pd);

    $term    = makeOpenTerm();
    $section = makeAttSection($professor, $term);
    makeAttEnrollment($student, $section, $term);

    $session = AttendanceSession::create([
        'section_id'   => $section->id,
        'created_by'   => $profUser->id,
        'session_date' => today()->toDateString(),
    ]);
    AttendanceRecord::create([
        'attendance_session_id' => $session->id,
        'student_id'            => $student->id,
        'status'                => 'present',
    ]);

    $this->actingAs($studentUser, 'sanctum')
         ->getJson('/api/student/attendance')
         ->assertOk()
         ->assertJsonCount(1, 'records');
});

test('student cannot view another student\'s attendance', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $studentA] = makeStudent($fac, $dept);
    ['user' => $studentB] = makeStudent($fac, $dept);

    // studentA's endpoint only returns their own records
    $responseA = $this->actingAs($studentA, 'sanctum')
                      ->getJson('/api/student/attendance')
                      ->assertOk()
                      ->json('records');
    $responseB = $this->actingAs($studentB, 'sanctum')
                      ->getJson('/api/student/attendance')
                      ->assertOk()
                      ->json('records');

    expect($responseA)->toBeEmpty();
    expect($responseB)->toBeEmpty();
});
