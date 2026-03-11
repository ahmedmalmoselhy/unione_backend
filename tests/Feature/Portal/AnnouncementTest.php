<?php

use App\Models\Announcement;

// ── GET /announcements ────────────────────────────────────────────────────────

test('authenticated portal user can view announcements', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user] = makeStudent($fac, $dept);

    $author = createUser();
    Announcement::create([
        'author_id'  => $author->id,
        'title'      => 'News',
        'body'       => 'Something happened.',
        'type'       => 'general',
        'visibility' => 'university',
    ]);

    $this->actingAs($user)
         ->get(route('portal.announcements.index'))
         ->assertOk();
});

test('guest is redirected from announcements', function () {
    $this->get(route('portal.announcements.index'))
         ->assertRedirect(route('portal.login'));
});

// ── POST /announcements/{id}/read ─────────────────────────────────────────────

test('student can mark an announcement as read', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user] = makeStudent($fac, $dept);

    $author       = createUser();
    $announcement = Announcement::create([
        'author_id'    => $author->id,
        'title'        => 'Read Me',
        'body'         => 'Body.',
        'type'         => 'general',
        'visibility'   => 'university',
        'published_at' => now()->subMinute(),
    ]);

    $this->actingAs($user)
         ->post(route('portal.announcements.read', $announcement->id))
         ->assertRedirect();

    $this->assertDatabaseHas('announcement_reads', [
        'user_id'         => $user->id,
        'announcement_id' => $announcement->id,
    ]);
});
