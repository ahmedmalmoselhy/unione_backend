<?php

test('authenticated user can logout', function () {
    $user  = createUser();
    $token = $user->createToken('api')->plainTextToken;

    $this->withToken($token)
         ->postJson('/api/auth/logout')
         ->assertOk()
         ->assertJson(['message' => __('auth.logout')]);
});

test('token is removed from the database after logout', function () {
    $user        = createUser();
    $tokenResult = $user->createToken('api');
    $tokenId     = $tokenResult->accessToken->id;

    $this->withToken($tokenResult->plainTextToken)
         ->postJson('/api/auth/logout')
         ->assertOk();

    // The personal_access_token row must be gone
    $this->assertDatabaseMissing('personal_access_tokens', ['id' => $tokenId]);
});

test('logout requires authentication', function () {
    $this->postJson('/api/auth/logout')
         ->assertUnauthorized();
});
