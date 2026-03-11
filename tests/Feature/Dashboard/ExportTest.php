<?php

use Maatwebsite\Excel\Facades\Excel;

// ── Students export ───────────────────────────────────────────────────────────

test('admin can export students', function () {
    Excel::fake();

    $admin = createUserWithRole('admin');

    $this->actingAs($admin)
         ->get(route('dashboard.students.export'))
         ->assertOk();

    Excel::assertDownloaded('students_' . now()->format('Y-m-d') . '.xlsx');
});

test('employee cannot export students', function () {
    $emp = createUserWithRole('employee');

    $this->actingAs($emp)
         ->get(route('dashboard.students.export'))
         ->assertForbidden();
});

// ── Professors export ─────────────────────────────────────────────────────────

test('admin can export professors', function () {
    Excel::fake();

    $admin = createUserWithRole('admin');

    $this->actingAs($admin)
         ->get(route('dashboard.professors.export'))
         ->assertOk();

    Excel::assertDownloaded('professors_' . now()->format('Y-m-d') . '.xlsx');
});

test('employee cannot export professors', function () {
    $emp = createUserWithRole('employee');

    $this->actingAs($emp)
         ->get(route('dashboard.professors.export'))
         ->assertForbidden();
});

// ── Employees export ──────────────────────────────────────────────────────────

test('admin can export employees', function () {
    Excel::fake();

    $admin = createUserWithRole('admin');

    $this->actingAs($admin)
         ->get(route('dashboard.employees.export'))
         ->assertOk();

    Excel::assertDownloaded('employees_' . now()->format('Y-m-d') . '.xlsx');
});

test('employee cannot export employees', function () {
    $emp = createUserWithRole('employee');

    $this->actingAs($emp)
         ->get(route('dashboard.employees.export'))
         ->assertForbidden();
});

// ── Enrollments export ────────────────────────────────────────────────────────

test('admin can export enrollments', function () {
    Excel::fake();

    $admin = createUserWithRole('admin');

    $this->actingAs($admin)
         ->get(route('dashboard.enrollments.export'))
         ->assertOk();

    Excel::assertDownloaded('enrollments_' . now()->format('Y-m-d') . '.xlsx');
});

test('employee cannot export enrollments', function () {
    $emp = createUserWithRole('employee');

    $this->actingAs($emp)
         ->get(route('dashboard.enrollments.export'))
         ->assertForbidden();
});

// ── Grades export ─────────────────────────────────────────────────────────────

test('admin can export grades', function () {
    Excel::fake();

    $admin = createUserWithRole('admin');

    $this->actingAs($admin)
         ->get(route('dashboard.grades.export'))
         ->assertOk();

    Excel::assertDownloaded('grades_' . now()->format('Y-m-d') . '.xlsx');
});

test('employee cannot export grades', function () {
    $emp = createUserWithRole('employee');

    $this->actingAs($emp)
         ->get(route('dashboard.grades.export'))
         ->assertForbidden();
});
