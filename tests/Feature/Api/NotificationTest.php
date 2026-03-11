<?php

// ── Unauthenticated ───────────────────────────────────────────────────────────

test('unauthenticated user cannot list notifications', function () {
    $this->getJson('/api/notifications')->assertUnauthorized();
});

// ── GET /api/notifications ────────────────────────────────────────────────────

test('authenticated user can list their notifications', function () {
    $user = createUserWithRole('student');
    makeDbNotification($user);
    makeDbNotification($user);

    $this->actingAs($user, 'sanctum')
         ->getJson('/api/notifications')
         ->assertOk()
         ->assertJsonStructure([
             'data',
             'meta' => ['current_page', 'last_page', 'total', 'unread_count'],
         ]);
});

test('user only sees their own notifications', function () {
    $userA = createUserWithRole('student');
    $userB = createUserWithRole('student');

    makeDbNotification($userA);
    makeDbNotification($userB);

    $response = $this->actingAs($userA, 'sanctum')
                     ->getJson('/api/notifications')
                     ->assertOk();

    expect($response->json('meta.total'))->toBe(1);
});

test('unread filter returns only unread notifications', function () {
    $user = createUserWithRole('student');
    makeDbNotification($user);
    $readId = makeDbNotification($user);

    // Mark one as read
    \Illuminate\Support\Facades\DB::table('notifications')
        ->where('id', $readId)
        ->update(['read_at' => now()]);

    $response = $this->actingAs($user, 'sanctum')
                     ->getJson('/api/notifications?unread=1')
                     ->assertOk();

    expect($response->json('meta.total'))->toBe(1);
});

// ── POST /api/notifications/{id}/read ─────────────────────────────────────────

test('user can mark their own notification as read', function () {
    $user = createUserWithRole('student');
    $id   = makeDbNotification($user);

    $this->actingAs($user, 'sanctum')
         ->postJson("/api/notifications/{$id}/read")
         ->assertOk()
         ->assertJsonFragment(['message' => 'Notification marked as read.']);

    $this->assertDatabaseHas('notifications', [
        'id'      => $id,
        'read_at' => now()->toDateTimeString(),
    ]);
});

test('user cannot mark another user\'s notification as read', function () {
    $userA = createUserWithRole('student');
    $userB = createUserWithRole('student');
    $id    = makeDbNotification($userB);

    $this->actingAs($userA, 'sanctum')
         ->postJson("/api/notifications/{$id}/read")
         ->assertNotFound();
});

// ── POST /api/notifications/read-all ─────────────────────────────────────────

test('user can mark all notifications as read', function () {
    $user = createUserWithRole('student');
    makeDbNotification($user);
    makeDbNotification($user);

    $this->actingAs($user, 'sanctum')
         ->postJson('/api/notifications/read-all')
         ->assertOk()
         ->assertJsonFragment(['message' => 'All notifications marked as read.']);

    expect(\Illuminate\Support\Facades\DB::table('notifications')
        ->where('notifiable_id', $user->id)
        ->whereNull('read_at')
        ->count()
    )->toBe(0);
});

// ── DELETE /api/notifications/{id} ────────────────────────────────────────────

test('user can delete their own notification', function () {
    $user = createUserWithRole('student');
    $id   = makeDbNotification($user);

    $this->actingAs($user, 'sanctum')
         ->deleteJson("/api/notifications/{$id}")
         ->assertOk()
         ->assertJsonFragment(['message' => 'Notification deleted.']);

    $this->assertDatabaseMissing('notifications', ['id' => $id]);
});

test('user cannot delete another user\'s notification', function () {
    $userA = createUserWithRole('student');
    $userB = createUserWithRole('student');
    $id    = makeDbNotification($userB);

    $this->actingAs($userA, 'sanctum')
         ->deleteJson("/api/notifications/{$id}")
         ->assertNotFound();
});
