<?php

// ── GET /api/auth/tokens ──────────────────────────────────────────────────────

test('authenticated user can list their tokens', function () {
    $user  = createUserWithRole('student');
    $auth  = $user->createToken('web-browser')->plainTextToken;

    // Create a second token
    $user->createToken('mobile-app');

    $response = $this->withToken($auth)
        ->getJson('/api/auth/tokens')
        ->assertOk();

    $response->assertJsonStructure(['tokens' => [['id', 'name', 'last_used_at', 'created_at', 'is_current']]]);
    expect($response->json('tokens'))->toHaveCount(2);
});

test('token list marks the current token correctly', function () {
    $user  = createUserWithRole('student');
    $token = $user->createToken('web-client')->plainTextToken;

    $response = $this->withToken($token)
        ->getJson('/api/auth/tokens')
        ->assertOk();

    $current = collect($response->json('tokens'))->firstWhere('is_current', true);
    expect($current)->not()->toBeNull();
    expect($current['name'])->toBe('web-client');
});

test('unauthenticated user cannot list tokens', function () {
    $this->getJson('/api/auth/tokens')->assertUnauthorized();
});

// ── DELETE /api/auth/tokens/{id} ─────────────────────────────────────────────

test('user can revoke a specific token', function () {
    $user  = createUserWithRole('student');
    $token = $user->createToken('old-device');

    // Keep auth token separate so we stay logged in
    $authToken = $user->createToken('auth')->plainTextToken;

    $this->withToken($authToken)
        ->deleteJson("/api/auth/tokens/{$token->accessToken->id}")
        ->assertOk()
        ->assertJson(['message' => 'Token revoked.']);

    expect($user->tokens()->count())->toBe(1);
});

test('user cannot revoke another users token', function () {
    $user1  = createUserWithRole('student');
    $user2  = createUserWithRole('student');
    $token2 = $user2->createToken('device');

    $this->actingAs($user1, 'sanctum')
        ->deleteJson("/api/auth/tokens/{$token2->accessToken->id}")
        ->assertNotFound();
});

test('revoking non-existent token returns 404', function () {
    $user = createUserWithRole('student');

    $this->actingAs($user, 'sanctum')
        ->deleteJson('/api/auth/tokens/99999')
        ->assertNotFound();
});

// ── DELETE /api/auth/tokens ───────────────────────────────────────────────────

test('user can revoke all tokens (logout everywhere)', function () {
    $user = createUserWithRole('student');
    $user->createToken('phone');
    $user->createToken('tablet');
    $user->createToken('laptop');

    $this->actingAs($user, 'sanctum')
        ->deleteJson('/api/auth/tokens')
        ->assertOk()
        ->assertJson(['message' => 'All tokens revoked.']);

    expect($user->tokens()->count())->toBe(0);
});
