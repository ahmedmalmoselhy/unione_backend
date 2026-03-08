<?php

test('returns token and user on valid credentials', function () {
    $user = createUser();

    $response = $this->postJson('/api/auth/login', [
        'email'    => $user->email,
        'password' => 'password',
    ]);

    $response->assertOk()
             ->assertJsonStructure(['token', 'user']);
});

test('rejects wrong password', function () {
    $user = createUser();

    $response = $this->postJson('/api/auth/login', [
        'email'    => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertUnauthorized()
             ->assertJson(['message' => __('auth.credentials')]);
});

test('rejects inactive user', function () {
    $user = createUser(['is_active' => false]);

    $response = $this->postJson('/api/auth/login', [
        'email'    => $user->email,
        'password' => 'password',
    ]);

    $response->assertUnauthorized();
});

test('rejects non-existent email', function () {
    $response = $this->postJson('/api/auth/login', [
        'email'    => 'nobody@example.com',
        'password' => 'password',
    ]);

    $response->assertUnauthorized();
});

test('validates email is required', function () {
    $response = $this->postJson('/api/auth/login', ['password' => 'password']);

    $response->assertUnprocessable()
             ->assertJsonValidationErrors('email');
});

test('validates password is required', function () {
    $response = $this->postJson('/api/auth/login', ['email' => 'a@b.com']);

    $response->assertUnprocessable()
             ->assertJsonValidationErrors('password');
});
