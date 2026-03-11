<?php

use Illuminate\Support\Facades\Hash;

// ── POST /api/auth/change-password ────────────────────────────────────────────

test('authenticated user can change their password', function () {
    $user = createUser();

    $this->actingAs($user, 'sanctum')
         ->postJson('/api/auth/change-password', [
             'current_password'      => 'password',
             'password'              => 'newpassword123',
             'password_confirmation' => 'newpassword123',
         ])
         ->assertOk()
         ->assertJsonFragment(['message' => 'Password updated successfully.']);

    expect(Hash::check('newpassword123', $user->fresh()->password))->toBeTrue();
});

test('change password rejects wrong current password', function () {
    $user = createUser();

    $this->actingAs($user, 'sanctum')
         ->postJson('/api/auth/change-password', [
             'current_password'      => 'wrong-password',
             'password'              => 'newpassword123',
             'password_confirmation' => 'newpassword123',
         ])
         ->assertUnprocessable()
         ->assertJsonFragment(['message' => __('auth.password')]);
});

test('change password requires confirmation to match', function () {
    $user = createUser();

    $this->actingAs($user, 'sanctum')
         ->postJson('/api/auth/change-password', [
             'current_password'      => 'password',
             'password'              => 'newpassword123',
             'password_confirmation' => 'different',
         ])
         ->assertUnprocessable()
         ->assertJsonValidationErrors('password');
});

test('change password requires minimum 8 characters', function () {
    $user = createUser();

    $this->actingAs($user, 'sanctum')
         ->postJson('/api/auth/change-password', [
             'current_password'      => 'password',
             'password'              => 'short',
             'password_confirmation' => 'short',
         ])
         ->assertUnprocessable()
         ->assertJsonValidationErrors('password');
});

test('change password requires authentication', function () {
    $this->postJson('/api/auth/change-password', [
        'current_password'      => 'password',
        'password'              => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ])->assertUnauthorized();
});

test('change password clears must_change_password flag', function () {
    $user = createUser(['must_change_password' => true]);

    $this->actingAs($user, 'sanctum')
         ->postJson('/api/auth/change-password', [
             'current_password'      => 'password',
             'password'              => 'newpassword123',
             'password_confirmation' => 'newpassword123',
         ])
         ->assertOk();

    expect($user->fresh()->must_change_password)->toBeFalse();
});
