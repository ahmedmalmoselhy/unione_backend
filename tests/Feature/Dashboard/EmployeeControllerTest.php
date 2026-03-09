<?php

use App\Models\Department;
use App\Models\Employee;
use App\Models\Faculty;
use App\Models\User;

function makeManagerialDept(string $suffix = ''): array
{
    $faculty = Faculty::create([
        'name'            => "EmpFac{$suffix}",
        'name_ar'         => 'كلية',
        'code'            => "EFC{$suffix}",
        'enrollment_type' => 'immediate',
        'is_active'       => true,
    ]);

    $dept = Department::create([
        'faculty_id' => $faculty->id,
        'name'       => "MgrDept{$suffix}",
        'name_ar'    => 'قسم إداري',
        'code'       => "MGD{$suffix}",
        'type'       => 'managerial',
        'is_active'  => true,
    ]);

    return compact('faculty', 'dept');
}

function makeEmployeePayload(int $deptId, string $suffix = ''): array
{
    return [
        'national_id'      => "EMP_NID_{$suffix}",
        'first_name'       => 'Jane',
        'last_name'        => 'Smith',
        'email'            => "emp{$suffix}@example.com",
        'password'         => 'Password1!',
        'password_confirmation' => 'Password1!',
        'gender'           => 'female',
        'staff_number'     => "ESTF{$suffix}",
        'department_id'    => $deptId,
        'job_title'        => 'Secretary',
        'employment_type'  => 'full_time',
        'hired_at'         => '2024-01-01',
    ];
}

// ── GET /dashboard/employees ──────────────────────────────────────────────────

test('admin can list employees', function () {
    $admin = createUserWithRole('admin');

    $this->actingAs($admin)
         ->get(route('dashboard.employees.index'))
         ->assertOk();
});

// ── POST /dashboard/employees ─────────────────────────────────────────────────

test('admin can create an employee', function () {
    $admin = createUserWithRole('admin');
    createRole('employee'); // EmployeeController::store looks up this role by name at runtime
    ['dept' => $dept] = makeManagerialDept('CR');

    $this->actingAs($admin)
         ->post(route('dashboard.employees.store'), makeEmployeePayload($dept->id, 'CR1'))
         ->assertRedirect(route('dashboard.employees.index'))
         ->assertSessionHas('success');

    $this->assertDatabaseHas('employees', ['staff_number' => 'ESTFCR1']);
    // Employee must also have the 'employee' role assigned
    $user = User::whereHas('employee', fn ($q) => $q->where('staff_number', 'ESTFCR1'))->first();
    expect($user)->not->toBeNull();
    $this->assertDatabaseHas('role_user', ['user_id' => $user->id]);
});

test('employee creation requires a managerial department', function () {
    $admin = createUserWithRole('admin');

    // Create an academic department — should be rejected
    $faculty = Faculty::create([
        'name'            => 'AcadFacEV',
        'name_ar'         => 'كلية',
        'code'            => 'AFCEV',
        'enrollment_type' => 'immediate',
        'is_active'       => true,
    ]);
    $acadDept = Department::create([
        'faculty_id' => $faculty->id,
        'name'       => 'AcadDeptEV',
        'name_ar'    => 'قسم أكاديمي',
        'code'       => 'ADEV',
        'type'       => 'academic',
        'is_active'  => true,
    ]);

    $payload = makeEmployeePayload($acadDept->id, 'EV9');

    $this->actingAs($admin)
         ->post(route('dashboard.employees.store'), $payload)
         ->assertSessionHasErrors('department_id');
});

test('employee staff number must be unique', function () {
    $admin = createUserWithRole('admin');
    ['dept' => $dept] = makeManagerialDept('DU');

    $existingUser = createUser(['email' => 'existing_emp_u@example.com', 'national_id' => 'UNIQNID01']);
    Employee::create([
        'user_id'         => $existingUser->id,
        'staff_number'    => 'ESTFDUP',
        'department_id'   => $dept->id,
        'job_title'       => 'Clerk',
        'employment_type' => 'full_time',
        'hired_at'        => '2024-01-01',
    ]);

    $payload = makeEmployeePayload($dept->id, 'DU2');
    $payload['staff_number'] = 'ESTFDUP';

    $this->actingAs($admin)
         ->post(route('dashboard.employees.store'), $payload)
         ->assertSessionHasErrors('staff_number');
});

// ── PUT /dashboard/employees/{employee} ───────────────────────────────────────

test('admin can update an employee', function () {
    $admin = createUserWithRole('admin');
    ['dept' => $dept] = makeManagerialDept('UP');

    $empUser = createUser([
        'national_id' => 'EMPNID_UP',
        'email'       => 'emp_up@example.com',
    ]);
    $employee = Employee::create([
        'user_id'         => $empUser->id,
        'staff_number'    => 'ESTFUP1',
        'department_id'   => $dept->id,
        'job_title'       => 'Old Title',
        'employment_type' => 'full_time',
        'hired_at'        => '2024-01-01',
    ]);

    $this->actingAs($admin)
         ->put(route('dashboard.employees.update', $employee), [
             'national_id'      => 'EMPNID_UP',
             'first_name'       => $empUser->first_name,
             'last_name'        => $empUser->last_name,
             'email'            => 'emp_up@example.com',
             'gender'           => $empUser->gender,
             'staff_number'     => 'ESTFUP1',
             'department_id'    => $dept->id,
             'job_title'        => 'New Title',
             'employment_type'  => 'part_time',
             'hired_at'         => '2024-01-01',
             'is_active'        => '1',
         ])
         ->assertRedirect(route('dashboard.employees.index'))
         ->assertSessionHas('success');

    $this->assertDatabaseHas('employees', ['id' => $employee->id, 'job_title' => 'New Title', 'employment_type' => 'part_time']);
});

// ── DELETE /dashboard/employees/{employee} ────────────────────────────────────

test('admin can delete an employee', function () {
    $admin = createUserWithRole('admin');
    ['dept' => $dept] = makeManagerialDept('DL');

    $empUser = createUser([
        'national_id' => 'EMPNID_DL',
        'email'       => 'emp_dl@example.com',
    ]);
    $employee = Employee::create([
        'user_id'         => $empUser->id,
        'staff_number'    => 'ESTFDL1',
        'department_id'   => $dept->id,
        'job_title'       => 'Clerk',
        'employment_type' => 'contract',
        'hired_at'        => '2024-01-01',
    ]);

    $this->actingAs($admin)
         ->delete(route('dashboard.employees.destroy', $employee))
         ->assertRedirect(route('dashboard.employees.index'))
         ->assertSessionHas('success');

    $this->assertDatabaseMissing('employees', ['id' => $employee->id]);
    $this->assertSoftDeleted('users', ['id' => $empUser->id]);
});

test('employee cannot create other employees', function () {
    $emp = createUserWithRole('employee');
    ['dept' => $dept] = makeManagerialDept('EP');

    $this->actingAs($emp)
         ->post(route('dashboard.employees.store'), makeEmployeePayload($dept->id, 'EP1'))
         ->assertForbidden();
});
