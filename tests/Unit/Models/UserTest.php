<?php

use App\Models\Faculty;
use App\Models\Department;
use Illuminate\Support\Facades\DB;

// ── hasActiveRole ────────────────────────────────────────────────────────────

test('hasActiveRole returns true when user has an active role', function () {
    $user = createUserWithRole('admin');

    expect($user->hasActiveRole('admin'))->toBeTrue();
});

test('hasActiveRole returns false when the role is revoked', function () {
    $user = createUserWithRole('admin');

    // Revoke the role
    DB::table('role_user')
        ->where('user_id', $user->id)
        ->update(['revoked_at' => now()]);

    expect($user->hasActiveRole('admin'))->toBeFalse();
});

test('hasActiveRole accepts an array and returns true on the first match', function () {
    $user = createUserWithRole('employee');

    expect($user->hasActiveRole(['admin', 'employee']))->toBeTrue();
});

test('hasActiveRole returns false when user has no roles', function () {
    $user = createUser();

    expect($user->hasActiveRole('admin'))->toBeFalse();
});

// ── Role shorthand helpers ───────────────────────────────────────────────────

test('isSystemAdmin delegates to hasActiveRole', function () {
    $admin = createUserWithRole('admin');
    $other = createUserWithRole('employee');

    expect($admin->isSystemAdmin())->toBeTrue()
        ->and($other->isSystemAdmin())->toBeFalse();
});

test('isFacultyAdmin delegates to hasActiveRole', function () {
    $fa    = createUserWithRole('faculty_admin');
    $other = createUserWithRole('admin');

    expect($fa->isFacultyAdmin())->toBeTrue()
        ->and($other->isFacultyAdmin())->toBeFalse();
});

test('isDepartmentAdmin delegates to hasActiveRole', function () {
    $da    = createUserWithRole('department_admin');
    $other = createUserWithRole('admin');

    expect($da->isDepartmentAdmin())->toBeTrue()
        ->and($other->isDepartmentAdmin())->toBeFalse();
});

// ── scopedFacultyId ──────────────────────────────────────────────────────────

test('scopedFacultyId returns the pivot faculty_id for a faculty admin', function () {
    $faculty = Faculty::create([
        'name'            => 'Science',
        'name_ar'         => 'العلوم',
        'code'            => 'SCI',
        'enrollment_type' => 'immediate',
        'is_active'       => true,
    ]);

    $fa = createUserWithRole('faculty_admin', [], ['faculty_id' => $faculty->id]);

    expect($fa->scopedFacultyId())->toBe($faculty->id);
});

test('scopedFacultyId returns null for a system admin', function () {
    $admin = createUserWithRole('admin');

    expect($admin->scopedFacultyId())->toBeNull();
});

// ── scopedDepartmentId ───────────────────────────────────────────────────────

test('scopedDepartmentId returns the pivot department_id for a department admin', function () {
    $faculty = Faculty::create([
        'name'            => 'Arts',
        'name_ar'         => 'الآداب',
        'code'            => 'ART',
        'enrollment_type' => 'immediate',
        'is_active'       => true,
    ]);

    $dept = Department::create([
        'faculty_id' => $faculty->id,
        'name'       => 'History',
        'name_ar'    => 'التاريخ',
        'code'       => 'HIST',
        'type'       => 'academic',
        'is_active'  => true,
    ]);

    $da = createUserWithRole('department_admin', [], ['department_id' => $dept->id]);

    expect($da->scopedDepartmentId())->toBe($dept->id);
});

test('scopedDepartmentId returns null for a system admin', function () {
    $admin = createUserWithRole('admin');

    expect($admin->scopedDepartmentId())->toBeNull();
});
