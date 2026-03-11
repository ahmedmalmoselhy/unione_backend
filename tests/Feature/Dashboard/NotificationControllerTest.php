<?php

// ── GET /dashboard/notifications ──────────────────────────────────────────────

test('admin can view notifications page', function () {
    $admin = createUserWithRole('admin');
    makeDbNotification($admin);

    $this->actingAs($admin)
         ->get(route('dashboard.notifications.index'))
         ->assertOk();
});

test('employee can view notifications page', function () {
    $emp = createUserWithRole('employee');
    makeDbNotification($emp);

    $this->actingAs($emp)
         ->get(route('dashboard.notifications.index'))
         ->assertOk();
});

test('guest is redirected from notifications page', function () {
    $this->get(route('dashboard.notifications.index'))
         ->assertRedirect();
});

// ── POST /dashboard/notifications/{id}/read ────────────────────────────────────

test('admin can mark their own notification as read', function () {
    $admin = createUserWithRole('admin');
    $id    = makeDbNotification($admin);

    $this->actingAs($admin)
         ->post(route('dashboard.notifications.read', $id))
         ->assertRedirect()
         ->assertSessionHas('success');

    $this->assertDatabaseHas('notifications', [
        'id'      => $id,
        'read_at' => now()->toDateTimeString(),
    ]);
});

test('user cannot mark another user\'s notification as read', function () {
    $admin  = createUserWithRole('admin');
    $other  = createUserWithRole('admin');
    $id     = makeDbNotification($other);

    $this->actingAs($admin)
         ->post(route('dashboard.notifications.read', $id))
         ->assertNotFound();
});

// ── POST /dashboard/notifications/read-all ─────────────────────────────────────

test('admin can mark all notifications as read', function () {
    $admin = createUserWithRole('admin');
    makeDbNotification($admin);
    makeDbNotification($admin);

    $this->actingAs($admin)
         ->post(route('dashboard.notifications.read-all'))
         ->assertRedirect()
         ->assertSessionHas('success');

    expect(\Illuminate\Support\Facades\DB::table('notifications')
        ->where('notifiable_id', $admin->id)
        ->whereNull('read_at')
        ->count()
    )->toBe(0);
});

// ── DELETE /dashboard/notifications/{id} ──────────────────────────────────────

test('admin can delete their own notification', function () {
    $admin = createUserWithRole('admin');
    $id    = makeDbNotification($admin);

    $this->actingAs($admin)
         ->delete(route('dashboard.notifications.destroy', $id))
         ->assertRedirect()
         ->assertSessionHas('success');

    $this->assertDatabaseMissing('notifications', ['id' => $id]);
});

test('user cannot delete another user\'s notification', function () {
    $admin = createUserWithRole('admin');
    $other = createUserWithRole('admin');
    $id    = makeDbNotification($other);

    $this->actingAs($admin)
         ->delete(route('dashboard.notifications.destroy', $id))
         ->assertNotFound();
});
