<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ResetPasswordNotification;

// ── Forgot Password ──────────────────────────────────────────────────────────

test('forgot password returns a validation error for unknown email', function () {
    $this->postJson('/api/auth/forgot-password', ['email' => 'nobody@example.com'])
         ->assertUnprocessable()
         ->assertJsonValidationErrors('email');
});

test('forgot password inserts a reset token for a known email', function () {
    Notification::fake();

    $user = createUser();

    $this->postJson('/api/auth/forgot-password', ['email' => $user->email])
         ->assertOk()
         ->assertJson(['message' => __('passwords.sent')]);

    $this->assertDatabaseHas('password_reset_tokens', ['email' => $user->email]);
    Notification::assertSentTo($user, ResetPasswordNotification::class);
});

// ── Reset Password ───────────────────────────────────────────────────────────

test('reset rejects an invalid token', function () {
    $user = createUser();

    DB::table('password_reset_tokens')->insert([
        'email'      => $user->email,
        'token'      => Hash::make('correct-token'),
        'created_at' => now(),
        'expires_at' => now()->addMinutes(60),
    ]);

    $this->postJson('/api/auth/reset-password', [
        'email'                 => $user->email,
        'token'                 => 'wrong-token',
        'password'              => 'newpassword',
        'password_confirmation' => 'newpassword',
    ])->assertUnprocessable()
      ->assertJson(['message' => __('passwords.token')]);
});

test('reset rejects an expired token', function () {
    $user = createUser();

    DB::table('password_reset_tokens')->insert([
        'email'      => $user->email,
        'token'      => Hash::make('valid-token'),
        'created_at' => now()->subHours(2),
        'expires_at' => now()->subHour(), // already expired
    ]);

    $this->postJson('/api/auth/reset-password', [
        'email'                 => $user->email,
        'token'                 => 'valid-token',
        'password'              => 'newpassword',
        'password_confirmation' => 'newpassword',
    ])->assertUnprocessable()
      ->assertJson(['message' => __('passwords.token_expired')]);

    // Expired row should be deleted
    $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
});

test('reset updates password with a valid token', function () {
    $user = createUser();

    DB::table('password_reset_tokens')->insert([
        'email'      => $user->email,
        'token'      => Hash::make('valid-token'),
        'created_at' => now(),
        'expires_at' => now()->addMinutes(60),
    ]);

    $this->postJson('/api/auth/reset-password', [
        'email'                 => $user->email,
        'token'                 => 'valid-token',
        'password'              => 'brandnewpassword',
        'password_confirmation' => 'brandnewpassword',
    ])->assertOk()
      ->assertJson(['message' => __('passwords.reset')]);

    // New password should work
    $user->refresh();
    expect(Hash::check('brandnewpassword', $user->password))->toBeTrue();
});

test('reset token is deleted after successful reset', function () {
    $user = createUser();

    DB::table('password_reset_tokens')->insert([
        'email'      => $user->email,
        'token'      => Hash::make('valid-token'),
        'created_at' => now(),
        'expires_at' => now()->addMinutes(60),
    ]);

    $this->postJson('/api/auth/reset-password', [
        'email'                 => $user->email,
        'token'                 => 'valid-token',
        'password'              => 'brandnewpassword',
        'password_confirmation' => 'brandnewpassword',
    ])->assertOk();

    $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
});
