<?php

/**
 * ScopedAdminMiddleware tests.
 *
 * Probe route: GET /dashboard/departments
 * (accessible to admin, faculty_admin, department_admin; blocked for plain employees)
 */

test('system admin can access scoped-admin routes', function () {
    $admin = createUserWithRole('admin');

    $this->actingAs($admin)
         ->get(route('dashboard.departments.index'))
         ->assertOk();
});

test('faculty admin can access scoped-admin routes', function () {
    $facultyAdmin = createUserWithRole('faculty_admin');

    $this->actingAs($facultyAdmin)
         ->get(route('dashboard.departments.index'))
         ->assertOk();
});

test('department admin can access scoped-admin routes', function () {
    $deptAdmin = createUserWithRole('department_admin');

    $this->actingAs($deptAdmin)
         ->get(route('dashboard.departments.index'))
         ->assertOk();
});

test('plain employee is forbidden from scoped-admin routes', function () {
    $employee = createUserWithRole('employee');

    $this->actingAs($employee)
         ->get(route('dashboard.departments.index'))
         ->assertForbidden();
});

test('unauthenticated user is redirected to login', function () {
    $this->get(route('dashboard.departments.index'))
         ->assertRedirect(route('dashboard.login'));
});
