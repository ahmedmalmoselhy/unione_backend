<?php

// ── PATCH /api/auth/profile ───────────────────────────────────────────────────

test('authenticated user can update their profile', function () {
    $user = createUser();

    $this->actingAs($user, 'sanctum')
         ->patchJson('/api/auth/profile', [
             'phone'         => '+201234567890',
             'date_of_birth' => '1990-01-15',
         ])
         ->assertOk()
         ->assertJsonStructure(['user'])
         ->assertJsonPath('user.phone', '+201234567890');

    expect($user->fresh()->phone)->toBe('+201234567890');
});

test('profile update accepts partial fields', function () {
    $user = createUser();

    $this->actingAs($user, 'sanctum')
         ->patchJson('/api/auth/profile', ['phone' => '+10000000000'])
         ->assertOk()
         ->assertJsonPath('user.phone', '+10000000000');
});

test('profile update rejects future date_of_birth', function () {
    $user = createUser();

    $this->actingAs($user, 'sanctum')
         ->patchJson('/api/auth/profile', ['date_of_birth' => now()->addYear()->toDateString()])
         ->assertUnprocessable()
         ->assertJsonValidationErrors('date_of_birth');
});

test('profile update rejects phone longer than 20 characters', function () {
    $user = createUser();

    $this->actingAs($user, 'sanctum')
         ->patchJson('/api/auth/profile', ['phone' => str_repeat('1', 21)])
         ->assertUnprocessable()
         ->assertJsonValidationErrors('phone');
});

test('profile update requires authentication', function () {
    $this->patchJson('/api/auth/profile', ['phone' => '+1234567890'])
         ->assertUnauthorized();
});

test('profile update returns the updated user', function () {
    $user = createUser();

    $response = $this->actingAs($user, 'sanctum')
                     ->patchJson('/api/auth/profile', [
                         'phone'         => '+209876543210',
                         'date_of_birth' => '1985-06-20',
                     ])
                     ->assertOk();

    $response->assertJsonPath('user.email', $user->email);
    $response->assertJsonPath('user.phone', '+209876543210');
});
