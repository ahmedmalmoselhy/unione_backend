<?php

/**
 * ForcePasswordChange middleware tests.
 *
 * Users with must_change_password = true must be redirected to the change-password
 * route for every protected dashboard request except the change-password and
 * logout routes themselves.
 */

test('user with must_change_password is redirected when accessing protected routes', function () {
    $admin = createUserWithRole('admin', ['must_change_password' => true]);

    $this->actingAs($admin)
         ->get(route('dashboard.home'))
         ->assertRedirect(route('dashboard.password.change'));
});

test('user with must_change_password can access the change-password route', function () {
    $admin = createUserWithRole('admin', ['must_change_password' => true]);

    $this->actingAs($admin)
         ->get(route('dashboard.password.change'))
         ->assertOk();
});

test('user with must_change_password can log out', function () {
    $admin = createUserWithRole('admin', ['must_change_password' => true]);

    $this->actingAs($admin)
         ->post(route('dashboard.logout'))
         ->assertRedirect(route('dashboard.login'));
});

test('user without must_change_password is not redirected', function () {
    $admin = createUserWithRole('admin', ['must_change_password' => false]);

    $this->actingAs($admin)
         ->get(route('dashboard.password.change'))
         ->assertOk();
});
