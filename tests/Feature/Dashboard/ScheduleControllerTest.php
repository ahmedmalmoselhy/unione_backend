<?php

use App\Models\Section;

// ── GET /dashboard/schedule ────────────────────────────────────────────────────

test('admin can view the schedule page', function () {
    $admin = createUserWithRole('admin');

    $this->actingAs($admin)
         ->get(route('dashboard.schedule.index'))
         ->assertOk();
});

test('employee cannot access the schedule page', function () {
    $emp = createUserWithRole('employee');

    $this->actingAs($emp)
         ->get(route('dashboard.schedule.index'))
         ->assertForbidden();
});

test('schedule page without department_id sets hasQueried to false', function () {
    $admin = createUserWithRole('admin');

    $response = $this->actingAs($admin)
                     ->get(route('dashboard.schedule.index'))
                     ->assertOk();

    $response->assertViewHas('hasQueried', false);
    $response->assertViewHas('sectionCount', 0);
});

test('schedule page with department_id shows grid data', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();

    $admin = createUserWithRole('admin');

    $this->actingAs($admin)
         ->get(route('dashboard.schedule.index', ['department_id' => $dept->id]))
         ->assertOk()
         ->assertViewHas('hasQueried', true);
});
