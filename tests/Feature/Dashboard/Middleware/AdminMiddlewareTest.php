<?php

/**
 * AdminMiddleware tests.
 *
 * Probe route: GET /dashboard/audit-logs  (system admin only)
 */

test('system admin can access admin-only routes', function () {
    $admin = createUserWithRole('admin');

    $this->actingAs($admin)
         ->get(route('dashboard.audit-logs.index'))
         ->assertOk();
});

test('faculty admin is forbidden from admin-only routes', function () {
    $facultyAdmin = createUserWithRole('faculty_admin');

    $this->actingAs($facultyAdmin)
         ->get(route('dashboard.audit-logs.index'))
         ->assertForbidden();
});

test('department admin is forbidden from admin-only routes', function () {
    $deptAdmin = createUserWithRole('department_admin');

    $this->actingAs($deptAdmin)
         ->get(route('dashboard.audit-logs.index'))
         ->assertForbidden();
});

test('employee is forbidden from admin-only routes', function () {
    $employee = createUserWithRole('employee');

    $this->actingAs($employee)
         ->get(route('dashboard.audit-logs.index'))
         ->assertForbidden();
});

test('unauthenticated user is redirected to login', function () {
    $this->get(route('dashboard.audit-logs.index'))
         ->assertRedirect(route('dashboard.login'));
});
