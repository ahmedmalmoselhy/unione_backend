<?php

use App\Models\Department;
use App\Models\Employee;
use App\Models\Faculty;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

// ── fixture helper ───────────────────────────────────────────────────────────

function makeAdminAssignmentFixture(): array
{
    static $n = 0;
    $n++;

    // Ensure the roles the controller looks up by name exist in the DB
    createRole('faculty_admin');
    createRole('department_admin');

    $faculty = Faculty::create([
        'name'            => "AdminFac {$n}",
        'name_ar'         => "كلية {$n}",
        'code'            => "AF{$n}",
        'enrollment_type' => 'immediate',
        'is_active'       => true,
    ]);

    $dept = Department::create([
        'faculty_id' => $faculty->id,
        'name'       => "AdminDept {$n}",
        'name_ar'    => "قسم {$n}",
        'code'       => "AD{$n}",
        'type'       => 'academic',
        'is_active'  => true,
    ]);

    $empUser = createUser();
    Employee::create([
        'user_id'          => $empUser->id,
        'staff_number'     => "EMP{$n}",
        'department_id'    => $dept->id,
        'employment_type'  => 'full_time',
        'job_title'        => 'Staff',
        'hired_at'         => today()->toDateString(),
    ]);

    return compact('faculty', 'dept', 'empUser');
}

// ── Faculty admin assignment ──────────────────────────────────────────────────

test('system admin can assign a faculty admin', function () {
    Mail::fake();

    $admin = createUserWithRole('admin');
    ['faculty' => $faculty, 'empUser' => $empUser] = makeAdminAssignmentFixture();

    $this->actingAs($admin)
         ->post(route('dashboard.faculties.assign-admin.store', $faculty), [
             'employee_user_id' => $empUser->id,
         ])
         ->assertRedirect(route('dashboard.faculties.assign-admin', $faculty));

    $this->assertDatabaseHas('role_user', [
        'user_id'    => $empUser->id,
        'faculty_id' => $faculty->id,
    ]);

    // Assigned user must be forced to reset password
    $this->assertDatabaseHas('users', [
        'id'                  => $empUser->id,
        'must_change_password' => true,
    ]);
});

test('assigning a new faculty admin revokes the old one', function () {
    Mail::fake();

    $admin = createUserWithRole('admin');
    ['faculty' => $faculty, 'empUser' => $firstEmp] = makeAdminAssignmentFixture();

    // Assign first admin
    $this->actingAs($admin)
         ->post(route('dashboard.faculties.assign-admin.store', $faculty), [
             'employee_user_id' => $firstEmp->id,
         ]);

    // Create a second employee in the same faculty
    ['faculty' => $_, 'dept' => $dept2] = makeAdminAssignmentFixture();
    $dept2->update(['faculty_id' => $faculty->id]);
    $secondEmp = createUser();
    Employee::create([
        'user_id'         => $secondEmp->id,
        'staff_number'    => 'EMP_SECOND',
        'department_id'   => $dept2->id,
        'employment_type' => 'full_time',
        'job_title'       => 'Staff',
        'hired_at'        => today()->toDateString(),
    ]);

    // Assign second admin (should revoke first)
    $this->actingAs($admin)
         ->post(route('dashboard.faculties.assign-admin.store', $faculty), [
             'employee_user_id' => $secondEmp->id,
         ]);

    $roleId = DB::table('roles')->where('name', 'faculty_admin')->value('id');

    // First admin's row should be revoked
    $firstRow = DB::table('role_user')
        ->where('user_id', $firstEmp->id)
        ->where('role_id', $roleId)
        ->where('faculty_id', $faculty->id)
        ->first();
    expect($firstRow->revoked_at)->not->toBeNull();

    // Second admin's row should be active
    $secondRow = DB::table('role_user')
        ->where('user_id', $secondEmp->id)
        ->where('role_id', $roleId)
        ->where('faculty_id', $faculty->id)
        ->whereNull('revoked_at')
        ->first();
    expect($secondRow)->not->toBeNull();
});

test('system admin can revoke a faculty admin', function () {
    Mail::fake();

    $admin = createUserWithRole('admin');
    ['faculty' => $faculty, 'empUser' => $empUser] = makeAdminAssignmentFixture();

    // Assign first
    $this->actingAs($admin)
         ->post(route('dashboard.faculties.assign-admin.store', $faculty), [
             'employee_user_id' => $empUser->id,
         ]);

    // Revoke
    $this->actingAs($admin)
         ->delete(route('dashboard.faculties.assign-admin.revoke', $faculty))
         ->assertRedirect(route('dashboard.faculties.assign-admin', $faculty));

    $roleId = DB::table('roles')->where('name', 'faculty_admin')->value('id');

    $row = DB::table('role_user')
        ->where('user_id', $empUser->id)
        ->where('role_id', $roleId)
        ->where('faculty_id', $faculty->id)
        ->first();

    expect($row->revoked_at)->not->toBeNull();
});

// ── Department admin assignment ───────────────────────────────────────────────

test('system admin can assign a department admin', function () {
    Mail::fake();

    $admin = createUserWithRole('admin');
    ['dept' => $dept, 'empUser' => $empUser] = makeAdminAssignmentFixture();

    $this->actingAs($admin)
         ->post(route('dashboard.departments.assign-admin.store', $dept), [
             'employee_user_id' => $empUser->id,
         ])
         ->assertRedirect(route('dashboard.departments.assign-admin', $dept));

    $this->assertDatabaseHas('role_user', [
        'user_id'       => $empUser->id,
        'department_id' => $dept->id,
    ]);
});

test('system admin can revoke a department admin', function () {
    Mail::fake();

    $admin = createUserWithRole('admin');
    ['dept' => $dept, 'empUser' => $empUser] = makeAdminAssignmentFixture();

    $this->actingAs($admin)
         ->post(route('dashboard.departments.assign-admin.store', $dept), [
             'employee_user_id' => $empUser->id,
         ]);

    $this->actingAs($admin)
         ->delete(route('dashboard.departments.assign-admin.revoke', $dept))
         ->assertRedirect(route('dashboard.departments.assign-admin', $dept));

    $roleId = DB::table('roles')->where('name', 'department_admin')->value('id');

    $row = DB::table('role_user')
        ->where('user_id', $empUser->id)
        ->where('role_id', $roleId)
        ->where('department_id', $dept->id)
        ->first();

    expect($row->revoked_at)->not->toBeNull();
});

test('non-system-admin cannot assign a faculty admin', function () {
    Mail::fake();

    $emp = createUserWithRole('employee');
    ['faculty' => $faculty, 'empUser' => $empUser] = makeAdminAssignmentFixture();

    $this->actingAs($emp)
         ->post(route('dashboard.faculties.assign-admin.store', $faculty), [
             'employee_user_id' => $empUser->id,
         ])
         ->assertForbidden();
});
