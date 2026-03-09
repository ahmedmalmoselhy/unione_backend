<?php

use App\Models\Faculty;

// ── GET /dashboard/faculties ──────────────────────────────────────────────────

test('admin can list faculties', function () {
    $admin = createUserWithRole('admin');

    Faculty::create([
        'name'            => 'Engineering',
        'name_ar'         => 'هندسة',
        'code'            => 'FACENG',
        'enrollment_type' => 'immediate',
        'is_active'       => true,
    ]);

    $this->actingAs($admin)
         ->get(route('dashboard.faculties.index'))
         ->assertOk();
});

test('employee cannot access faculties list', function () {
    $emp = createUserWithRole('employee');

    $this->actingAs($emp)
         ->get(route('dashboard.faculties.index'))
         ->assertForbidden();
});

// ── POST /dashboard/faculties ─────────────────────────────────────────────────

test('admin can create a faculty', function () {
    $admin = createUserWithRole('admin');

    $this->actingAs($admin)
         ->post(route('dashboard.faculties.store'), [
             'name'            => 'Science',
             'name_ar'         => 'علوم',
             'code'            => 'FACSCI',
             'enrollment_type' => 'immediate',
             'is_active'       => '1',
         ])
         ->assertRedirect(route('dashboard.faculties.index'))
         ->assertSessionHas('success');

    $this->assertDatabaseHas('faculties', ['code' => 'FACSCI']);
});

test('faculty creation validates required fields', function () {
    $admin = createUserWithRole('admin');

    $this->actingAs($admin)
         ->post(route('dashboard.faculties.store'), [])
         ->assertSessionHasErrors(['name', 'name_ar', 'code', 'enrollment_type']);
});

test('faculty code must be unique', function () {
    $admin = createUserWithRole('admin');

    Faculty::create([
        'name'            => 'First',
        'name_ar'         => 'أولى',
        'code'            => 'DUPFC',
        'enrollment_type' => 'immediate',
        'is_active'       => true,
    ]);

    $this->actingAs($admin)
         ->post(route('dashboard.faculties.store'), [
             'name'            => 'Second',
             'name_ar'         => 'ثانية',
             'code'            => 'DUPFC',
             'enrollment_type' => 'immediate',
         ])
         ->assertSessionHasErrors('code');
});

// ── PUT /dashboard/faculties/{faculty} ────────────────────────────────────────

test('admin can update a faculty', function () {
    $admin = createUserWithRole('admin');

    $faculty = Faculty::create([
        'name'            => 'Old Name',
        'name_ar'         => 'قديم',
        'code'            => 'UPDFC',
        'enrollment_type' => 'immediate',
        'is_active'       => true,
    ]);

    $this->actingAs($admin)
         ->put(route('dashboard.faculties.update', $faculty), [
             'name'            => 'New Name',
             'name_ar'         => 'جديد',
             'code'            => 'UPDFC',
             'enrollment_type' => 'deferred',
             'is_active'       => '1',
         ])
         ->assertRedirect(route('dashboard.faculties.index'))
         ->assertSessionHas('success');

    $this->assertDatabaseHas('faculties', ['id' => $faculty->id, 'name' => 'New Name', 'enrollment_type' => 'deferred']);
});

// ── DELETE /dashboard/faculties/{faculty} ─────────────────────────────────────

test('admin can delete a faculty with no dependencies', function () {
    $admin = createUserWithRole('admin');

    $faculty = Faculty::create([
        'name'            => 'Delete Me',
        'name_ar'         => 'احذفني',
        'code'            => 'DELFC',
        'enrollment_type' => 'immediate',
        'is_active'       => true,
    ]);

    $this->actingAs($admin)
         ->delete(route('dashboard.faculties.destroy', $faculty))
         ->assertRedirect(route('dashboard.faculties.index'))
         ->assertSessionHas('success');

    $this->assertDatabaseMissing('faculties', ['id' => $faculty->id]);
});
