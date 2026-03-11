<?php

// ── Show login page ──────────────────────────────────────────────────────────

test('portal login page is accessible to guests', function () {
    $this->get(route('portal.login'))
         ->assertOk();
});

test('authenticated portal user is redirected away from login page', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user] = makeStudent($fac, $dept);

    $this->actingAs($user)
         ->get(route('portal.login'))
         ->assertRedirect(route('portal.home'));
});

// ── POST /login ───────────────────────────────────────────────────────────────

test('student can log in via portal', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user] = makeStudent($fac, $dept);

    $this->post(route('portal.login.post'), [
        'email'    => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('portal.home'));

    $this->assertAuthenticatedAs($user);
});

test('professor can log in via portal', function () {
    ['department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user] = makeProfessor($dept);

    $this->post(route('portal.login.post'), [
        'email'    => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('portal.home'));

    $this->assertAuthenticatedAs($user);
});

test('wrong password is rejected', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user] = makeStudent($fac, $dept);

    $this->post(route('portal.login.post'), [
        'email'    => $user->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('admin-only user cannot log in to portal', function () {
    $admin = createUserWithRole('admin');

    $this->post(route('portal.login.post'), [
        'email'    => $admin->email,
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('inactive student cannot log in to portal', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user] = makeStudent($fac, $dept);
    $user->update(['is_active' => false]);

    $this->post(route('portal.login.post'), [
        'email'    => $user->email,
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

// ── Logout ───────────────────────────────────────────────────────────────────

test('authenticated user can log out', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user] = makeStudent($fac, $dept);

    $this->actingAs($user)
         ->post(route('portal.logout'))
         ->assertRedirect(route('portal.login'));

    $this->assertGuest();
});

// ── Guest redirect ────────────────────────────────────────────────────────────

test('unauthenticated user is redirected from protected portal routes', function () {
    $this->get(route('portal.home'))
         ->assertRedirect(route('portal.login'));
});

test('admin-only user accessing protected portal route is redirected to login', function () {
    $admin = createUserWithRole('admin');

    $this->actingAs($admin)
         ->get(route('portal.home'))
         ->assertRedirect(route('portal.login'));
});
