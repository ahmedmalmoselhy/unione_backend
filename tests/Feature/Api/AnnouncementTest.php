<?php

use App\Models\Announcement;
use App\Models\AnnouncementRead;

/**
 * Create a published, non-expired announcement.
 */
function makeAnnouncement(string $visibility = 'university', ?int $targetId = null): Announcement
{
    $author = createUser();

    return Announcement::create([
        'author_id'    => $author->id,
        'title'        => 'Test Announcement',
        'body'         => 'Body text.',
        'type'         => 'general',
        'visibility'   => $visibility,
        'target_id'    => $targetId,
        'published_at' => now()->subMinute(),
        'expires_at'   => null,
    ]);
}

// ── GET /api/announcements ──────────────────────────────────────────────────

test('unauthenticated user cannot list announcements', function () {
    $this->getJson('/api/announcements')
         ->assertUnauthorized();
});

test('university-wide announcements are visible to all authenticated users', function () {
    makeAnnouncement('university');

    $admin = createUserWithRole('admin');

    $this->actingAs($admin, 'sanctum')
         ->getJson('/api/announcements')
         ->assertOk()
         ->assertJsonCount(1, 'data');
});

test('draft announcements (published_at null) are not returned', function () {
    $author = createUser();
    Announcement::create([
        'author_id'    => $author->id,
        'title'        => 'Draft',
        'body'         => 'Not yet.',
        'type'         => 'general',
        'visibility'   => 'university',
        'published_at' => null,
    ]);

    $admin = createUserWithRole('admin');

    $this->actingAs($admin, 'sanctum')
         ->getJson('/api/announcements')
         ->assertOk()
         ->assertJsonCount(0, 'data');
});

test('expired announcements are not returned', function () {
    $author = createUser();
    Announcement::create([
        'author_id'    => $author->id,
        'title'        => 'Expired',
        'body'         => 'Gone.',
        'type'         => 'general',
        'visibility'   => 'university',
        'published_at' => now()->subDay(),
        'expires_at'   => now()->subHour(),
    ]);

    $admin = createUserWithRole('admin');

    $this->actingAs($admin, 'sanctum')
         ->getJson('/api/announcements')
         ->assertOk()
         ->assertJsonCount(0, 'data');
});

test('faculty-scoped announcement is visible to a student in that faculty', function () {
    ['faculty' => $faculty, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user] = makeStudent($faculty, $dept);

    makeAnnouncement('faculty', $faculty->id);

    $this->actingAs($user, 'sanctum')
         ->getJson('/api/announcements')
         ->assertOk()
         ->assertJsonCount(1, 'data');
});

test('faculty-scoped announcement is not visible to a student in a different faculty', function () {
    ['faculty' => $faculty1, 'department' => $dept1] = makeFacultyDeptFixture();
    ['faculty' => $faculty2, 'department' => $dept2] = makeFacultyDeptFixture();
    ['user' => $user] = makeStudent($faculty1, $dept1);

    // Announcement targets faculty2, student is in faculty1
    makeAnnouncement('faculty', $faculty2->id);

    $this->actingAs($user, 'sanctum')
         ->getJson('/api/announcements')
         ->assertOk()
         ->assertJsonCount(0, 'data');
});

test('department-scoped announcement is visible to a student in that department', function () {
    ['faculty' => $faculty, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user] = makeStudent($faculty, $dept);

    makeAnnouncement('department', $dept->id);

    $this->actingAs($user, 'sanctum')
         ->getJson('/api/announcements')
         ->assertOk()
         ->assertJsonCount(1, 'data');
});

test('department-scoped announcement is not visible to student in a different department', function () {
    ['faculty' => $faculty, 'department' => $dept1] = makeFacultyDeptFixture();
    ['user' => $user] = makeStudent($faculty, $dept1);

    // Another department in the same faculty
    $dept2 = \App\Models\Department::create([
        'faculty_id' => $faculty->id,
        'name'       => 'Other Dept',
        'name_ar'    => 'قسم آخر',
        'code'       => 'OTH',
        'type'       => 'academic',
        'is_active'  => true,
    ]);

    makeAnnouncement('department', $dept2->id);

    $this->actingAs($user, 'sanctum')
         ->getJson('/api/announcements')
         ->assertOk()
         ->assertJsonCount(0, 'data');
});

test('response includes is_read flag set to false when not yet read', function () {
    makeAnnouncement('university');

    $admin = createUserWithRole('admin');

    $this->actingAs($admin, 'sanctum')
         ->getJson('/api/announcements')
         ->assertOk()
         ->assertJsonPath('data.0.is_read', false);
});

test('response includes is_read flag set to true after marking read', function () {
    $announcement = makeAnnouncement('university');
    $admin        = createUserWithRole('admin');

    AnnouncementRead::create([
        'announcement_id' => $announcement->id,
        'user_id'         => $admin->id,
        'read_at'         => now(),
    ]);

    $this->actingAs($admin, 'sanctum')
         ->getJson('/api/announcements')
         ->assertOk()
         ->assertJsonPath('data.0.is_read', true);
});

// ── POST /api/announcements/{id}/read ───────────────────────────────────────

test('user can mark an announcement as read', function () {
    $announcement = makeAnnouncement('university');
    $admin        = createUserWithRole('admin');

    $this->actingAs($admin, 'sanctum')
         ->postJson("/api/announcements/{$announcement->id}/read")
         ->assertOk()
         ->assertJson(['message' => 'Marked as read.']);

    $this->assertDatabaseHas('announcement_reads', [
        'announcement_id' => $announcement->id,
        'user_id'         => $admin->id,
    ]);
});

test('marking the same announcement read twice is idempotent', function () {
    $announcement = makeAnnouncement('university');
    $admin        = createUserWithRole('admin');

    $this->actingAs($admin, 'sanctum')
         ->postJson("/api/announcements/{$announcement->id}/read")
         ->assertOk();

    $this->actingAs($admin, 'sanctum')
         ->postJson("/api/announcements/{$announcement->id}/read")
         ->assertOk();

    $count = AnnouncementRead::where('announcement_id', $announcement->id)
                              ->where('user_id', $admin->id)
                              ->count();

    expect($count)->toBe(1);
});
