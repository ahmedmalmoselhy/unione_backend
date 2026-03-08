<?php

test('returns authenticated user data', function () {
    $user = createUser();

    $this->actingAs($user, 'sanctum')
         ->getJson('/api/auth/me')
         ->assertOk()
         ->assertJsonFragment(['email' => $user->email]);
});

test('requires authentication', function () {
    $this->getJson('/api/auth/me')
         ->assertUnauthorized();
});
