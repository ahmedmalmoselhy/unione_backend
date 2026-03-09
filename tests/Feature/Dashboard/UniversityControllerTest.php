<?php

use App\Models\University;

function makeUniversity(): University
{
    return University::create([
        'name'    => 'Test University',
        'name_ar' => 'جامعة اختبار',
        'address' => '1 Test Street, Cairo, Egypt',
    ]);
}

// ── GET /dashboard/university ─────────────────────────────────────────────────

test('admin can view university info', function () {
    makeUniversity();
    $admin = createUserWithRole('admin');

    $this->actingAs($admin)
         ->get(route('dashboard.university.show'))
         ->assertOk();
});

test('admin can view university edit form', function () {
    makeUniversity();
    $admin = createUserWithRole('admin');

    $this->actingAs($admin)
         ->get(route('dashboard.university.edit'))
         ->assertOk();
});

// ── PUT /dashboard/university ─────────────────────────────────────────────────

test('admin can update university info', function () {
    makeUniversity();
    $admin = createUserWithRole('admin');

    $this->actingAs($admin)
         ->put(route('dashboard.university.update'), [
             'name'    => 'Updated University',
             'name_ar' => 'جامعة محدثة',
             'address' => '2 Updated Street, Cairo, Egypt',
         ])
         ->assertRedirect(route('dashboard.university.show'))
         ->assertSessionHas('success');

    $this->assertDatabaseHas('university', ['name' => 'Updated University']);
});

test('university update validates required fields', function () {
    makeUniversity();
    $admin = createUserWithRole('admin');

    $this->actingAs($admin)
         ->put(route('dashboard.university.update'), [])
         ->assertSessionHasErrors(['name', 'name_ar', 'address']);
});

// ── Forbidden ─────────────────────────────────────────────────────────────────

test('employee cannot update university info', function () {
    makeUniversity();
    $emp = createUserWithRole('employee');

    $this->actingAs($emp)
         ->put(route('dashboard.university.update'), [
             'name'    => 'Hacked',
             'name_ar' => 'محتل',
             'address' => 'Evil HQ',
         ])
         ->assertForbidden();
});
