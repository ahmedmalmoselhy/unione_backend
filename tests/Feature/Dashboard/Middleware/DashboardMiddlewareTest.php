<?php

/**
 * DashboardMiddleware tests.
 *
 * Probe route: GET /dashboard/change-password
 * (requires `dashboard` + `force.password` middleware, no complex DB queries)
 */

test('unauthenticated user is redirected to login', function () {
    $this->get(route('dashboard.password.change'))
         ->assertRedirect(route('dashboard.login'));
});

test('admin can access dashboard routes', function () {
    $admin = createUserWithRole('admin');

    $this->actingAs($admin)
         ->get(route('dashboard.password.change'))
         ->assertOk();
});

test('employee can access dashboard routes', function () {
    $employee = createUserWithRole('employee');

    $this->actingAs($employee)
         ->get(route('dashboard.password.change'))
         ->assertOk();
});

test('faculty admin can access dashboard routes', function () {
    $facultyAdmin = createUserWithRole('faculty_admin');

    $this->actingAs($facultyAdmin)
         ->get(route('dashboard.password.change'))
         ->assertOk();
});

test('user with student role only is kicked out of the dashboard', function () {
    $student = createUserWithRole('student');

    $this->actingAs($student)
         ->get(route('dashboard.password.change'))
         ->assertRedirect(route('dashboard.login'));
});
