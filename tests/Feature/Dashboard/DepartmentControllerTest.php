<?php

use App\Models\Department;
use App\Models\Faculty;

function makeDeptFixture(string $suffix = ''): array
{
    $faculty = Faculty::create([
        'name'            => "DeptFac{$suffix}",
        'name_ar'         => 'كلية',
        'code'            => "DFC{$suffix}",
        'enrollment_type' => 'immediate',
        'is_active'       => true,
    ]);

    return compact('faculty');
}

// ── GET /dashboard/departments ────────────────────────────────────────────────

test('admin can list departments', function () {
    $admin = createUserWithRole('admin');

    $this->actingAs($admin)
         ->get(route('dashboard.departments.index'))
         ->assertOk();
});

// ── POST /dashboard/departments ───────────────────────────────────────────────

test('admin can create an academic department', function () {
    $admin = createUserWithRole('admin');
    ['faculty' => $faculty] = makeDeptFixture('CA');

    $this->actingAs($admin)
         ->post(route('dashboard.departments.store'), [
             'faculty_id' => $faculty->id,
             'name'       => 'Computer Science',
             'name_ar'    => 'علوم حاسوب',
             'code'       => 'DCSA',
             'type'       => 'academic',
             'is_active'  => '1',
         ])
         ->assertRedirect(route('dashboard.departments.index'))
         ->assertSessionHas('success');

    $this->assertDatabaseHas('departments', ['code' => 'DCSA', 'type' => 'academic']);
});

test('department code must be unique', function () {
    $admin = createUserWithRole('admin');
    ['faculty' => $faculty] = makeDeptFixture('CU');

    Department::create([
        'faculty_id' => $faculty->id,
        'name'       => 'Existing',
        'name_ar'    => 'موجود',
        'code'       => 'DDUPC',
        'type'       => 'academic',
        'is_active'  => true,
    ]);

    $this->actingAs($admin)
         ->post(route('dashboard.departments.store'), [
             'faculty_id' => $faculty->id,
             'name'       => 'Duplicate',
             'name_ar'    => 'مكرر',
             'code'       => 'DDUPC',
             'type'       => 'academic',
         ])
         ->assertSessionHasErrors('code');
});

test('department creation requires faculty_id', function () {
    $admin = createUserWithRole('admin');

    $this->actingAs($admin)
         ->post(route('dashboard.departments.store'), [
             'name'    => 'No Faculty',
             'name_ar' => 'بدون',
             'code'    => 'DNOF',
             'type'    => 'academic',
         ])
         ->assertSessionHasErrors('faculty_id');
});

// ── PUT /dashboard/departments/{department} ───────────────────────────────────

test('admin can update a department', function () {
    $admin = createUserWithRole('admin');
    ['faculty' => $faculty] = makeDeptFixture('UP');

    $dept = Department::create([
        'faculty_id' => $faculty->id,
        'name'       => 'Old Dept',
        'name_ar'    => 'قديم',
        'code'       => 'DUPC',
        'type'       => 'academic',
        'is_active'  => true,
    ]);

    $this->actingAs($admin)
         ->put(route('dashboard.departments.update', $dept), [
             'faculty_id' => $faculty->id,
             'name'       => 'Updated Dept',
             'name_ar'    => 'محدث',
             'code'       => 'DUPC',
             'type'       => 'academic',
             'is_active'  => '1',
         ])
         ->assertRedirect(route('dashboard.departments.index'))
         ->assertSessionHas('success');

    $this->assertDatabaseHas('departments', ['id' => $dept->id, 'name' => 'Updated Dept']);
});

// ── DELETE /dashboard/departments/{department} ────────────────────────────────

test('admin can delete a department with no dependencies', function () {
    $admin = createUserWithRole('admin');
    ['faculty' => $faculty] = makeDeptFixture('DEL');

    $dept = Department::create([
        'faculty_id' => $faculty->id,
        'name'       => 'Delete Me',
        'name_ar'    => 'احذفني',
        'code'       => 'DDEL',
        'type'       => 'academic',
        'is_active'  => true,
    ]);

    $this->actingAs($admin)
         ->delete(route('dashboard.departments.destroy', $dept))
         ->assertRedirect(route('dashboard.departments.index'))
         ->assertSessionHas('success');

    $this->assertDatabaseMissing('departments', ['id' => $dept->id]);
});

test('employee cannot create departments', function () {
    $emp = createUserWithRole('employee');
    ['faculty' => $faculty] = makeDeptFixture('EP');

    $this->actingAs($emp)
         ->post(route('dashboard.departments.store'), [
             'faculty_id' => $faculty->id,
             'name'       => 'No',
             'name_ar'    => 'لا',
             'code'       => 'DNOP',
             'type'       => 'academic',
         ])
         ->assertForbidden();
});
