<?php

use App\Models\AcademicTerm;
use App\Models\Enrollment;

// ── GET /api/student/schedule/ics ─────────────────────────────────────────────

test('student can download their schedule as an ics file', function () {
    ['faculty' => $f, 'department' => $d] = makeFacultyDeptFixture();
    ['user' => $u, 'student' => $s] = makeStudent($f, $d);

    $term    = makeOpenTerm();
    $section = makeSection($term);

    // Give the section a weekly schedule
    $section->update([
        'room'     => 'B-101',
        'schedule' => [
            ['day' => 'monday',    'start_time' => '09:00', 'end_time' => '10:30', 'type' => 'lecture'],
            ['day' => 'wednesday', 'start_time' => '09:00', 'end_time' => '10:30', 'type' => 'lecture'],
        ],
    ]);

    Enrollment::create([
        'student_id'       => $s->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'registered',
        'registered_at'    => now(),
    ]);

    $response = $this->actingAs($u, 'sanctum')
        ->get('/api/student/schedule/ics');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/calendar; charset=utf-8');

    $body = $response->getContent();
    expect($body)->toContain('BEGIN:VCALENDAR');
    expect($body)->toContain('END:VCALENDAR');
    expect($body)->toContain('BEGIN:VEVENT');
    expect($body)->toContain('RRULE:FREQ=WEEKLY;BYDAY=MO');
    expect($body)->toContain('RRULE:FREQ=WEEKLY;BYDAY=WE');
    expect($body)->toContain('END:VEVENT');
});

test('student with no enrollments gets an empty but valid ics feed', function () {
    ['faculty' => $f, 'department' => $d] = makeFacultyDeptFixture();
    ['user' => $u] = makeStudent($f, $d);

    makeOpenTerm();

    $response = $this->actingAs($u, 'sanctum')
        ->get('/api/student/schedule/ics');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/calendar; charset=utf-8');

    $body = $response->getContent();
    expect($body)->toContain('BEGIN:VCALENDAR');
    expect($body)->toContain('END:VCALENDAR');
    expect($body)->not()->toContain('BEGIN:VEVENT');
});

test('ics response includes content-disposition attachment header', function () {
    ['faculty' => $f, 'department' => $d] = makeFacultyDeptFixture();
    ['user' => $u] = makeStudent($f, $d);

    $response = $this->actingAs($u, 'sanctum')
        ->get('/api/student/schedule/ics');

    $response->assertOk();
    expect($response->headers->get('Content-Disposition'))->toContain('attachment');
    expect($response->headers->get('Content-Disposition'))->toContain('schedule.ics');
});

test('unauthenticated user cannot access schedule ics endpoint', function () {
    $this->getJson('/api/student/schedule/ics')
        ->assertUnauthorized();
});

test('non-student user cannot access schedule ics endpoint', function () {
    $admin = createUserWithRole('admin');

    $this->actingAs($admin, 'sanctum')
        ->getJson('/api/student/schedule/ics')
        ->assertForbidden();
});
