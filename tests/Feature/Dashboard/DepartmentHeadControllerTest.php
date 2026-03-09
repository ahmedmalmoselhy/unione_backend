<?php

use App\Models\Department;
use App\Models\Employee;
use App\Models\Faculty;

function makeDeptHeadFixture(): array
{
    static $n = 0;
    $n++;

    $faculty = Faculty::create([
        'name'            => "HeadFac{$n}",
        'name_ar'         => 'كلية',
        'code'            => "HFC{$n}",
        'enrollment_type' => 'immediate',
        'is_active'       => true,
    ]);

    $dept = Department::create([
        'faculty_id' => $faculty->id,
        'name'       => "HeadDept{$n}",
        'name_ar'    => 'قسم',
        'code'       => "HDC{$n}",
        'type'       => 'academic',
        'is_active'  => true,
    ]);

    // Create a professor user who can be assigned as head
    ['professor' => $prof, 'user' => $profUser] = makeProfessor($dept);

    return compact('faculty', 'dept', 'prof', 'profUser');
}

// ── POST /dashboard/departments/{department}/assign-head ──────────────────────

test('admin can assign a department head', function () {
    $admin = createUserWithRole('admin');
    ['dept' => $dept, 'profUser' => $profUser] = makeDeptHeadFixture();

    $this->actingAs($admin)
         ->post(route('dashboard.departments.assign-head.store', $dept), [
             'user_id' => $profUser->id,
         ])
         ->assertRedirect(route('dashboard.departments.assign-head', $dept))
         ->assertSessionHas('success');

    $this->assertDatabaseHas('departments', [
        'id'      => $dept->id,
        'head_id' => $profUser->id,
    ]);

    $this->assertDatabaseHas('audit_logs', [
        'action'         => 'assigned',
        'auditable_type' => 'DepartmentHead',
        'auditable_id'   => $dept->id,
    ]);
});

test('assigning a new head replaces the old one', function () {
    $admin = createUserWithRole('admin');
    ['dept' => $dept, 'profUser' => $firstUser] = makeDeptHeadFixture();

    // Assign first head
    $this->actingAs($admin)
         ->post(route('dashboard.departments.assign-head.store', $dept), [
             'user_id' => $firstUser->id,
         ]);

    // Create a second professor for the same department
    ['user' => $secondUser] = makeProfessor($dept);

    // Assign second head
    $this->actingAs($admin)
         ->post(route('dashboard.departments.assign-head.store', $dept), [
             'user_id' => $secondUser->id,
         ]);

    $this->assertDatabaseHas('departments', [
        'id'      => $dept->id,
        'head_id' => $secondUser->id,
    ]);
});

// ── DELETE /dashboard/departments/{department}/assign-head ────────────────────

test('admin can revoke a department head', function () {
    $admin = createUserWithRole('admin');
    ['dept' => $dept, 'profUser' => $profUser] = makeDeptHeadFixture();

    // First assign
    $this->actingAs($admin)
         ->post(route('dashboard.departments.assign-head.store', $dept), [
             'user_id' => $profUser->id,
         ]);

    // Then revoke
    $this->actingAs($admin)
         ->delete(route('dashboard.departments.assign-head.revoke', $dept))
         ->assertRedirect(route('dashboard.departments.assign-head', $dept))
         ->assertSessionHas('success');

    $this->assertDatabaseHas('departments', [
        'id'      => $dept->id,
        'head_id' => null,
    ]);

    $this->assertDatabaseHas('audit_logs', [
        'action'         => 'revoked',
        'auditable_type' => 'DepartmentHead',
        'auditable_id'   => $dept->id,
    ]);
});

test('revoking when no head is assigned redirects gracefully', function () {
    $admin = createUserWithRole('admin');
    ['dept' => $dept] = makeDeptHeadFixture();

    // No head assigned — should still redirect with a message, not crash
    $this->actingAs($admin)
         ->delete(route('dashboard.departments.assign-head.revoke', $dept))
         ->assertRedirect(route('dashboard.departments.assign-head', $dept))
         ->assertSessionHas('success');
});

test('employee cannot assign department head', function () {
    $emp = createUserWithRole('employee');
    ['dept' => $dept, 'profUser' => $profUser] = makeDeptHeadFixture();

    $this->actingAs($emp)
         ->post(route('dashboard.departments.assign-head.store', $dept), [
             'user_id' => $profUser->id,
         ])
         ->assertForbidden();
});
