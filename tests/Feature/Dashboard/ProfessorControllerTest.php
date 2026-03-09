<?php

use App\Models\Department;
use App\Models\Faculty;
use App\Models\Professor;
use App\Models\User;

function makeProfessorFixture(string $suffix = ''): array
{
    static $n = 0;
    $n++;

    $faculty = Faculty::create([
        'name'            => "ProfFac{$n}{$suffix}",
        'name_ar'         => 'كلية',
        'code'            => "PFAC{$n}{$suffix}",
        'enrollment_type' => 'immediate',
        'is_active'       => true,
    ]);

    $dept = Department::create([
        'faculty_id' => $faculty->id,
        'name'       => "ProfDept{$n}{$suffix}",
        'name_ar'    => 'قسم',
        'code'       => "PDEP{$n}{$suffix}",
        'type'       => 'academic',
        'is_active'  => true,
    ]);

    return compact('faculty', 'dept');
}

function makeProfessorPayload(int $deptId, string $suffix = ''): array
{
    return [
        'national_id'       => "PROF_NID_{$suffix}",
        'first_name'        => 'John',
        'last_name'         => 'Doe',
        'email'             => "prof{$suffix}@example.com",
        'password'          => 'Password1!',
        'password_confirmation' => 'Password1!',
        'gender'            => 'male',
        'staff_number'      => "PSTF{$suffix}",
        'department_id'     => $deptId,
        'specialization'    => 'Computer Science',
        'academic_rank'     => 'assistant_professor',
        'hired_at'          => '2020-09-01',
    ];
}

// ── GET /dashboard/professors ─────────────────────────────────────────────────

test('admin can list professors', function () {
    $admin = createUserWithRole('admin');

    $this->actingAs($admin)
         ->get(route('dashboard.professors.index'))
         ->assertOk();
});

// ── POST /dashboard/professors ────────────────────────────────────────────────

test('admin can create a professor', function () {
    $admin = createUserWithRole('admin');
    createRole('professor'); // controller looks up role by name at runtime
    ['dept' => $dept] = makeProfessorFixture('CR');

    $this->actingAs($admin)
         ->post(route('dashboard.professors.store'), makeProfessorPayload($dept->id, 'CR1'))
         ->assertRedirect(route('dashboard.professors.index'))
         ->assertSessionHas('success');

    $this->assertDatabaseHas('professors', ['staff_number' => 'PSTFCR1']);
    $user = User::whereHas('professor', fn ($q) => $q->where('staff_number', 'PSTFCR1'))->first();
    $this->assertDatabaseHas('role_user', ['user_id' => $user->id]);
});

test('professor creation requires an academic department', function () {
    $admin = createUserWithRole('admin');

    $faculty = Faculty::create([
        'name'            => 'ManFacPR',
        'name_ar'         => 'كلية إدارية',
        'code'            => 'MFPR',
        'enrollment_type' => 'immediate',
        'is_active'       => true,
    ]);
    $mgrDept = Department::create([
        'faculty_id' => $faculty->id,
        'name'       => 'MgrDeptPR',
        'name_ar'    => 'قسم إداري',
        'code'       => 'MDPR',
        'type'       => 'managerial',
        'is_active'  => true,
    ]);

    $this->actingAs($admin)
         ->post(route('dashboard.professors.store'), makeProfessorPayload($mgrDept->id, 'MG1'))
         ->assertSessionHasErrors('department_id');
});

test('professor staff number must be unique', function () {
    $admin = createUserWithRole('admin');
    ['dept' => $dept] = makeProfessorFixture('DU');

    $existingUser = createUser(['email' => 'existing_prof@example.com', 'national_id' => 'PROF_NID_EX']);
    Professor::create([
        'user_id'        => $existingUser->id,
        'staff_number'   => 'PSTFDUP',
        'department_id'  => $dept->id,
        'specialization' => 'Physics',
        'academic_rank'  => 'lecturer',
        'hired_at'       => '2020-01-01',
    ]);

    $payload = makeProfessorPayload($dept->id, 'DU2');
    $payload['staff_number'] = 'PSTFDUP';

    $this->actingAs($admin)
         ->post(route('dashboard.professors.store'), $payload)
         ->assertSessionHasErrors('staff_number');
});

// ── PUT /dashboard/professors/{professor} ─────────────────────────────────────

test('admin can update a professor', function () {
    $admin = createUserWithRole('admin');
    ['dept' => $dept] = makeProfessorFixture('UP');

    $profUser = createUser(['national_id' => 'PROF_NID_UP', 'email' => 'prof_up@example.com']);
    $professor = Professor::create([
        'user_id'        => $profUser->id,
        'staff_number'   => 'PSTFUP1',
        'department_id'  => $dept->id,
        'specialization' => 'Old Spec',
        'academic_rank'  => 'lecturer',
        'hired_at'       => '2020-01-01',
    ]);

    $this->actingAs($admin)
         ->put(route('dashboard.professors.update', $professor), [
             'national_id'    => 'PROF_NID_UP',
             'first_name'     => $profUser->first_name,
             'last_name'      => $profUser->last_name,
             'email'          => 'prof_up@example.com',
             'gender'         => $profUser->gender,
             'staff_number'   => 'PSTFUP1',
             'department_id'  => $dept->id,
             'specialization' => 'New Spec',
             'academic_rank'  => 'associate_professor',
             'hired_at'       => '2020-01-01',
             'is_active'      => '1',
         ])
         ->assertRedirect(route('dashboard.professors.index'))
         ->assertSessionHas('success');

    $this->assertDatabaseHas('professors', ['id' => $professor->id, 'academic_rank' => 'associate_professor']);
});

// ── DELETE /dashboard/professors/{professor} ──────────────────────────────────

test('admin can delete a professor', function () {
    $admin = createUserWithRole('admin');
    ['dept' => $dept] = makeProfessorFixture('DL');

    $profUser = createUser(['national_id' => 'PROF_NID_DL', 'email' => 'prof_dl@example.com']);
    $professor = Professor::create([
        'user_id'        => $profUser->id,
        'staff_number'   => 'PSTFDL1',
        'department_id'  => $dept->id,
        'specialization' => 'Chemistry',
        'academic_rank'  => 'professor',
        'hired_at'       => '2015-01-01',
    ]);

    $this->actingAs($admin)
         ->delete(route('dashboard.professors.destroy', $professor))
         ->assertRedirect(route('dashboard.professors.index'))
         ->assertSessionHas('success');

    $this->assertSoftDeleted('users', ['id' => $profUser->id]);
});

// ── Forbidden ─────────────────────────────────────────────────────────────────

test('employee cannot create professors', function () {
    $emp = createUserWithRole('employee');
    ['dept' => $dept] = makeProfessorFixture('EP');

    $this->actingAs($emp)
         ->post(route('dashboard.professors.store'), makeProfessorPayload($dept->id, 'EP1'))
         ->assertForbidden();
});
