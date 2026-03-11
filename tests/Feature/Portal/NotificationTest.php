<?php

// ── GET /notifications ────────────────────────────────────────────────────────

test('authenticated portal user can view notifications page', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user] = makeStudent($fac, $dept);

    makeDbNotification($user);

    $this->actingAs($user)
         ->get(route('portal.notifications.index'))
         ->assertOk();
});

test('guest is redirected from notifications', function () {
    $this->get(route('portal.notifications.index'))
         ->assertRedirect(route('portal.login'));
});

// ── POST /notifications/{id}/read ─────────────────────────────────────────────

test('user can mark a notification as read', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user] = makeStudent($fac, $dept);

    $id = makeDbNotification($user);

    $this->actingAs($user)
         ->post(route('portal.notifications.read', $id))
         ->assertRedirect();

    $this->assertDatabaseHas('notifications', [
        'id'      => $id,
        'read_at' => now()->toDateTimeString(),
    ]);
});

test('user cannot mark another user\'s notification as read', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user] = makeStudent($fac, $dept);
    ['faculty' => $fac2, 'department' => $dept2] = makeFacultyDeptFixture();
    ['user' => $other] = makeStudent($fac2, $dept2);

    $id = makeDbNotification($other);

    $this->actingAs($user)
         ->post(route('portal.notifications.read', $id))
         ->assertNotFound();
});

// ── POST /notifications/read-all ──────────────────────────────────────────────

test('user can mark all notifications as read', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user] = makeStudent($fac, $dept);

    makeDbNotification($user);
    makeDbNotification($user);

    $this->actingAs($user)
         ->post(route('portal.notifications.read-all'))
         ->assertRedirect();

    expect($user->unreadNotifications()->count())->toBe(0);
});

// ── DELETE /notifications/{id} ────────────────────────────────────────────────

test('user can delete a notification', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user] = makeStudent($fac, $dept);

    $id = makeDbNotification($user);

    $this->actingAs($user)
         ->delete(route('portal.notifications.destroy', $id))
         ->assertRedirect();

    $this->assertDatabaseMissing('notifications', ['id' => $id]);
});

test('user cannot delete another user\'s notification', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user] = makeStudent($fac, $dept);
    ['faculty' => $fac2, 'department' => $dept2] = makeFacultyDeptFixture();
    ['user' => $other] = makeStudent($fac2, $dept2);

    $id = makeDbNotification($other);

    $this->actingAs($user)
         ->delete(route('portal.notifications.destroy', $id))
         ->assertNotFound();
});
