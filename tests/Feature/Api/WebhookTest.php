<?php

use App\Models\Webhook;

// ── POST /api/admin/webhooks ──────────────────────────────────────────────────

test('admin can register a webhook', function () {
    $admin = createUserWithRole('admin');

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/admin/webhooks', [
            'url'    => 'https://example.com/hook',
            'events' => ['enrollment.confirmed'],
        ])
        ->assertCreated();

    $response->assertJsonStructure(['webhook' => ['id', 'url', 'events', 'secret'], 'message']);
    expect($response->json('webhook.secret'))->not()->toBeNull();
    expect(Webhook::count())->toBe(1);
});

test('webhook secret is returned only at creation', function () {
    $admin = createUserWithRole('admin');

    $store = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/admin/webhooks', [
            'url'    => 'https://example.com/hook',
            'events' => ['grade.posted'],
        ])
        ->assertCreated();

    $webhookId = $store->json('webhook.id');

    // Listing should NOT expose the secret
    $list = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/admin/webhooks')
        ->assertOk();

    $found = collect($list->json('webhooks'))->firstWhere('id', $webhookId);
    expect($found)->not()->toBeNull();
    expect(array_key_exists('secret', $found))->toBeFalse();
});

test('webhook registration validates url and events', function () {
    $admin = createUserWithRole('admin');

    $this->actingAs($admin, 'sanctum')
        ->postJson('/api/admin/webhooks', [
            'url'    => 'not-a-url',
            'events' => ['invalid.event'],
        ])
        ->assertUnprocessable();
});

test('student cannot register webhooks', function () {
    $user = createUserWithRole('student');

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/admin/webhooks', [
            'url'    => 'https://example.com/hook',
            'events' => ['enrollment.confirmed'],
        ])
        ->assertForbidden();
});

// ── GET /api/admin/webhooks ───────────────────────────────────────────────────

test('admin can list their webhooks', function () {
    $admin = createUserWithRole('admin');

    Webhook::create([
        'user_id'  => $admin->id,
        'url'      => 'https://example.com/hook',
        'secret'   => 'topsecret',
        'events'   => ['enrollment.confirmed'],
        'is_active' => true,
    ]);

    $this->actingAs($admin, 'sanctum')
        ->getJson('/api/admin/webhooks')
        ->assertOk()
        ->assertJsonStructure(['webhooks' => [['id', 'url', 'events', 'is_active', 'failure_count', 'deliveries_count']]]);
});

test('admin only sees their own webhooks', function () {
    $admin1 = createUserWithRole('admin');
    $admin2 = createUserWithRole('admin');

    Webhook::create(['user_id' => $admin1->id, 'url' => 'https://a.com', 'secret' => 'x', 'events' => ['grade.posted'], 'is_active' => true]);
    Webhook::create(['user_id' => $admin2->id, 'url' => 'https://b.com', 'secret' => 'y', 'events' => ['grade.posted'], 'is_active' => true]);

    $response = $this->actingAs($admin1, 'sanctum')
        ->getJson('/api/admin/webhooks')
        ->assertOk();

    expect($response->json('webhooks'))->toHaveCount(1);
    expect($response->json('webhooks.0.url'))->toBe('https://a.com');
});

// ── PATCH /api/admin/webhooks/{webhook} ───────────────────────────────────────

test('admin can update their webhook', function () {
    $admin   = createUserWithRole('admin');
    $webhook = Webhook::create([
        'user_id'  => $admin->id,
        'url'      => 'https://old.com',
        'secret'   => 'sec',
        'events'   => ['grade.posted'],
        'is_active' => true,
    ]);

    $this->actingAs($admin, 'sanctum')
        ->patchJson("/api/admin/webhooks/{$webhook->id}", [
            'url'    => 'https://new.com',
            'events' => ['enrollment.confirmed', 'grade.posted'],
        ])
        ->assertOk()
        ->assertJsonPath('webhook.url', 'https://new.com');

    expect($webhook->fresh()->events)->toContain('enrollment.confirmed');
});

test('admin cannot update another admins webhook', function () {
    $admin1  = createUserWithRole('admin');
    $admin2  = createUserWithRole('admin');
    $webhook = Webhook::create(['user_id' => $admin2->id, 'url' => 'https://b.com', 'secret' => 'y', 'events' => ['grade.posted'], 'is_active' => true]);

    $this->actingAs($admin1, 'sanctum')
        ->patchJson("/api/admin/webhooks/{$webhook->id}", ['url' => 'https://hacked.com'])
        ->assertForbidden();
});

// ── DELETE /api/admin/webhooks/{webhook} ──────────────────────────────────────

test('admin can delete their webhook', function () {
    $admin   = createUserWithRole('admin');
    $webhook = Webhook::create(['user_id' => $admin->id, 'url' => 'https://x.com', 'secret' => 'sec', 'events' => ['grade.posted'], 'is_active' => true]);

    $this->actingAs($admin, 'sanctum')
        ->deleteJson("/api/admin/webhooks/{$webhook->id}")
        ->assertOk()
        ->assertJson(['message' => 'Webhook deleted.']);

    expect(Webhook::count())->toBe(0);
});

test('admin cannot delete another admins webhook', function () {
    $admin1  = createUserWithRole('admin');
    $admin2  = createUserWithRole('admin');
    $webhook = Webhook::create(['user_id' => $admin2->id, 'url' => 'https://b.com', 'secret' => 'y', 'events' => ['grade.posted'], 'is_active' => true]);

    $this->actingAs($admin1, 'sanctum')
        ->deleteJson("/api/admin/webhooks/{$webhook->id}")
        ->assertForbidden();

    expect(Webhook::count())->toBe(1);
});

// ── GET /api/admin/webhooks/{webhook}/deliveries ──────────────────────────────

test('admin can view webhook deliveries', function () {
    $admin   = createUserWithRole('admin');
    $webhook = Webhook::create(['user_id' => $admin->id, 'url' => 'https://x.com', 'secret' => 'sec', 'events' => ['grade.posted'], 'is_active' => true]);

    $webhook->deliveries()->create([
        'event'           => 'grade.posted',
        'payload'         => ['test' => 'data'],
        'response_status' => 200,
        'attempt'         => 1,
        'delivered_at'    => now(),
    ]);

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson("/api/admin/webhooks/{$webhook->id}/deliveries")
        ->assertOk();

    expect($response->json('deliveries'))->toHaveCount(1);
    expect($response->json('deliveries.0.event'))->toBe('grade.posted');
});

test('admin cannot view another admins webhook deliveries', function () {
    $admin1  = createUserWithRole('admin');
    $admin2  = createUserWithRole('admin');
    $webhook = Webhook::create(['user_id' => $admin2->id, 'url' => 'https://b.com', 'secret' => 'y', 'events' => ['grade.posted'], 'is_active' => true]);

    $this->actingAs($admin1, 'sanctum')
        ->getJson("/api/admin/webhooks/{$webhook->id}/deliveries")
        ->assertForbidden();
});
