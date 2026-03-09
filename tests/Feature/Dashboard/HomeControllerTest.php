<?php

use App\Models\Department;
use App\Models\Faculty;

// ── GET /dashboard ────────────────────────────────────────────────────────────

test('system admin sees the home dashboard', function () {
    $admin = createUserWithRole('admin');

    $this->actingAs($admin)
         ->get(route('dashboard.home'))
         ->assertOk();
});

test('faculty admin sees the home dashboard', function () {
    $faculty = Faculty::create([
        'name'            => 'Home Test Faculty',
        'name_ar'         => 'كلية اختبار',
        'code'            => 'HTFAC',
        'enrollment_type' => 'immediate',
        'is_active'       => true,
    ]);

    // faculty_id pivot is required so scopedFacultyId() returns it
    $admin = createUserWithRole('faculty_admin', [], ['faculty_id' => $faculty->id]);

    $this->actingAs($admin)
         ->get(route('dashboard.home'))
         ->assertOk();
});

test('department admin sees the home dashboard', function () {
    $faculty = Faculty::create([
        'name'            => 'Home Dept Faculty',
        'name_ar'         => 'كلية',
        'code'            => 'HDFAC',
        'enrollment_type' => 'immediate',
        'is_active'       => true,
    ]);

    $dept = Department::create([
        'faculty_id' => $faculty->id,
        'name'       => 'Home Test Dept',
        'name_ar'    => 'قسم اختبار',
        'code'       => 'HTDEP',
        'type'       => 'academic',
        'is_active'  => true,
    ]);

    // department_id pivot is required so scopedDepartmentId() returns it
    $admin = createUserWithRole('department_admin', [], ['department_id' => $dept->id]);

    $this->actingAs($admin)
         ->get(route('dashboard.home'))
         ->assertOk();
});
