<?php

use App\Models\ExamSchedule;
use App\Models\Enrollment;
use App\Notifications\ExamSchedulePublished;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\actingAs;

test('admin can manage section exam schedule lifecycle', function () {
    $admin = createUserWithRole('admin');

    $term = makeOpenTerm('EXAM');
    $section = makeSection($term, 40);

    ['faculty' => $faculty, 'department' => $department] = makeFacultyDeptFixture('EXAM_NOTIFY');
    ['user' => $studentUser, 'student' => $student] = makeStudent($faculty, $department);

    Enrollment::create([
        'student_id' => $student->id,
        'section_id' => $section->id,
        'academic_term_id' => $term->id,
        'status' => 'registered',
        'registered_at' => now(),
    ]);

    actingAs($admin, 'sanctum')
        ->getJson("/api/admin/sections/{$section->id}/exam-schedule")
        ->assertNotFound();

    actingAs($admin, 'sanctum')
        ->postJson("/api/admin/sections/{$section->id}/exam-schedule", [
            'exam_date' => '2027-01-15',
        ])
        ->assertUnprocessable();

    $created = actingAs($admin, 'sanctum')
        ->postJson("/api/admin/sections/{$section->id}/exam-schedule", [
            'exam_date' => '2027-01-15',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'location' => 'Main Hall',
        ])
        ->assertCreated()
        ->assertJsonPath('exam_schedule.section_id', $section->id)
        ->assertJsonPath('exam_schedule.is_published', false);

    $scheduleId = $created->json('exam_schedule.id');

    actingAs($admin, 'sanctum')
        ->postJson("/api/admin/sections/{$section->id}/exam-schedule", [
            'exam_date' => '2027-01-16',
            'start_time' => '10:00',
            'end_time' => '12:00',
        ])
        ->assertStatus(409);

    actingAs($admin, 'sanctum')
        ->getJson("/api/admin/sections/{$section->id}/exam-schedule")
        ->assertOk()
        ->assertJsonPath('exam_schedule.id', $scheduleId);

    Notification::fake();

    actingAs($admin, 'sanctum')
        ->postJson("/api/admin/sections/{$section->id}/exam-schedule/publish")
        ->assertOk()
        ->assertJsonPath('exam_schedule.is_published', true);

    Notification::assertSentTo(
        $studentUser,
        ExamSchedulePublished::class,
        function ($notification, array $channels) use ($section): bool {
            return $notification->examSchedule->section_id === $section->id
                && in_array('database', $channels, true)
                && in_array('mail', $channels, true);
        }
    );

    actingAs($admin, 'sanctum')
        ->patchJson("/api/admin/sections/{$section->id}/exam-schedule", [
            'location' => 'Hall B',
        ])
        ->assertOk()
        ->assertJsonPath('exam_schedule.location', 'Hall B')
        ->assertJsonPath('exam_schedule.is_published', false);

    expect(ExamSchedule::query()->find($scheduleId))->not->toBeNull();
});

test('non-admin user is forbidden from section exam schedule routes', function () {
    $student = createUserWithRole('student');

    $term = makeOpenTerm('EXAMF');
    $section = makeSection($term, 30);

    actingAs($student, 'sanctum')
        ->postJson("/api/admin/sections/{$section->id}/exam-schedule", [
            'exam_date' => '2027-01-15',
            'start_time' => '09:00',
            'end_time' => '11:00',
        ])
        ->assertForbidden();
});
