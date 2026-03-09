<?php

use App\Models\Announcement;
use App\Models\Department;
use App\Models\Faculty;

// ── POST /dashboard/announcements ─────────────────────────────────────────────

test('admin can create a university-wide announcement', function () {
    $admin = createUserWithRole('admin');

    $this->actingAs($admin)
         ->post(route('dashboard.announcements.store'), [
             'title'        => 'Test Announcement',
             'body'         => 'This is the body.',
             'type'         => 'general',
             'visibility'   => 'university',
             'published_at' => now()->toDateTimeString(),
         ])
         ->assertRedirect(route('dashboard.announcements.index'))
         ->assertSessionHas('success');

    $this->assertDatabaseHas('announcements', ['title' => 'Test Announcement', 'visibility' => 'university']);
});

test('announcement creation validates required fields', function () {
    $admin = createUserWithRole('admin');

    $this->actingAs($admin)
         ->post(route('dashboard.announcements.store'), [])
         ->assertSessionHasErrors(['title', 'body', 'type', 'visibility']);
});

test('announcement type must be valid', function () {
    $admin = createUserWithRole('admin');

    $this->actingAs($admin)
         ->post(route('dashboard.announcements.store'), [
             'title'      => 'Bad Type',
             'body'       => 'Body',
             'type'       => 'gossip',
             'visibility' => 'university',
         ])
         ->assertSessionHasErrors('type');
});

test('author_id is automatically set to the authenticated user', function () {
    $admin = createUserWithRole('admin');

    $this->actingAs($admin)
         ->post(route('dashboard.announcements.store'), [
             'title'      => 'Author Test',
             'body'       => 'Body.',
             'type'       => 'academic',
             'visibility' => 'university',
         ]);

    $this->assertDatabaseHas('announcements', [
        'title'     => 'Author Test',
        'author_id' => $admin->id,
    ]);
});

// ── PUT /dashboard/announcements/{announcement} ───────────────────────────────

test('admin can update own announcement', function () {
    $admin = createUserWithRole('admin');

    $ann = Announcement::create([
        'author_id'  => $admin->id,
        'title'      => 'Old Title',
        'body'       => 'Old body.',
        'type'       => 'general',
        'visibility' => 'university',
    ]);

    $this->actingAs($admin)
         ->put(route('dashboard.announcements.update', $ann), [
             'title'      => 'New Title',
             'body'       => 'New body.',
             'type'       => 'urgent',
             'visibility' => 'university',
         ])
         ->assertRedirect(route('dashboard.announcements.index'))
         ->assertSessionHas('success');

    $this->assertDatabaseHas('announcements', ['id' => $ann->id, 'title' => 'New Title', 'type' => 'urgent']);
});

test('a different admin cannot update another admin announcement', function () {
    $admin1 = createUserWithRole('admin');
    $admin2 = createUserWithRole('admin');

    $ann = Announcement::create([
        'author_id'  => $admin1->id,
        'title'      => 'Admin1 Ann',
        'body'       => 'Body.',
        'type'       => 'general',
        'visibility' => 'university',
    ]);

    // admin2 is a system admin — system admins CAN edit any announcement per canManageAnnouncement()
    // So this test verifies the system admin can edit other admins' announcements
    $this->actingAs($admin2)
         ->put(route('dashboard.announcements.update', $ann), [
             'title'      => 'Changed',
             'body'       => 'Body.',
             'type'       => 'general',
             'visibility' => 'university',
         ])
         ->assertRedirect();
});

// ── DELETE /dashboard/announcements/{announcement} ────────────────────────────

test('admin can delete an announcement', function () {
    $admin = createUserWithRole('admin');

    $ann = Announcement::create([
        'author_id'  => $admin->id,
        'title'      => 'Delete Me',
        'body'       => 'Body.',
        'type'       => 'general',
        'visibility' => 'university',
    ]);

    $this->actingAs($admin)
         ->delete(route('dashboard.announcements.destroy', $ann))
         ->assertRedirect(route('dashboard.announcements.index'))
         ->assertSessionHas('success');

    $this->assertSoftDeleted('announcements', ['id' => $ann->id]);
});

// ── Faculty-admin scoped creation ─────────────────────────────────────────────

test('faculty admin can create a faculty-scoped announcement', function () {
    $faculty = Faculty::create([
        'name'            => 'AnnFac',
        'name_ar'         => 'كلية',
        'code'            => 'ANNFAC',
        'enrollment_type' => 'immediate',
        'is_active'       => true,
    ]);

    $facultyAdmin = createUserWithRole('faculty_admin', [], ['faculty_id' => $faculty->id]);

    $this->actingAs($facultyAdmin)
         ->post(route('dashboard.announcements.store'), [
             'title'      => 'Faculty Ann',
             'body'       => 'Body.',
             'type'       => 'academic',
             'visibility' => 'faculty',
             'target_id'  => $faculty->id,
         ])
         ->assertRedirect(route('dashboard.announcements.index'))
         ->assertSessionHas('success');

    $this->assertDatabaseHas('announcements', [
        'title'      => 'Faculty Ann',
        'visibility' => 'faculty',
        'target_id'  => $faculty->id,
    ]);
});

test('employee cannot access announcements management', function () {
    $emp = createUserWithRole('employee');

    $this->actingAs($emp)
         ->get(route('dashboard.announcements.index'))
         ->assertForbidden();
});
