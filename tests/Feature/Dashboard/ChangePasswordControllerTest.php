<?php

use Illuminate\Support\Facades\Hash;

// ── GET /dashboard/change-password ────────────────────────────────────────────

test('authenticated user can view the change password page', function () {
    $user = createUserWithRole('admin');

    $this->actingAs($user)
         ->get(route('dashboard.password.change'))
         ->assertOk();
});

// ── PUT /dashboard/change-password ────────────────────────────────────────────

test('user can change their password', function () {
    $user = createUserWithRole('admin', ['must_change_password' => true]);

    $this->actingAs($user)
         ->put(route('dashboard.password.update'), [
             'password'              => 'NewPassword1!',
             'password_confirmation' => 'NewPassword1!',
         ])
         ->assertRedirect(route('dashboard.home'))
         ->assertSessionHas('success');

    $user->refresh();
    expect($user->must_change_password)->toBeFalse();
    expect(Hash::check('NewPassword1!', $user->password))->toBeTrue();
});

test('password change requires at least 8 characters', function () {
    $user = createUserWithRole('admin');

    $this->actingAs($user)
         ->put(route('dashboard.password.update'), [
             'password'              => 'Short1!',
             'password_confirmation' => 'Short1!',
         ])
         ->assertSessionHasErrors('password');
});

test('password change requires confirmation to match', function () {
    $user = createUserWithRole('admin');

    $this->actingAs($user)
         ->put(route('dashboard.password.update'), [
             'password'              => 'NewPassword1!',
             'password_confirmation' => 'DifferentPassword1!',
         ])
         ->assertSessionHasErrors('password');
});
