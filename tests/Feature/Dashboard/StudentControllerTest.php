<?php

use App\Models\Department;
use App\Models\Faculty;
use App\Models\Student;
use App\Models\User;

function makeStudentCrudFixture(string $suffix = ''): array
{
    static $n = 0;
    $n++;

    $faculty = Faculty::create([
        'name'            => "StuFac{$n}{$suffix}",
        'name_ar'         => 'كلية',
        'code'            => "SFAC{$n}{$suffix}",
        'enrollment_type' => 'immediate',
        'is_active'       => true,
    ]);

    $dept = Department::create([
        'faculty_id' => $faculty->id,
        'name'       => "StuDept{$n}{$suffix}",
        'name_ar'    => 'قسم',
        'code'       => "SDEP{$n}{$suffix}",
        'type'       => 'academic',
        'is_active'  => true,
    ]);

    return compact('faculty', 'dept');
}

function makeStudentPayload(int $facultyId, int $deptId, string $suffix = ''): array
{
    return [
        'national_id'       => "STU_NID_{$suffix}",
        'first_name'        => 'Alice',
        'last_name'         => 'Smith',
        'email'             => "stu{$suffix}@example.com",
        'password'          => 'Password1!',
        'password_confirmation' => 'Password1!',
        'gender'            => 'female',
        'student_number'    => "SNUM{$suffix}",
        'faculty_id'        => $facultyId,
        'department_id'     => $deptId,
        'academic_year'     => 2,
        'semester'          => 'first',
        'enrollment_status' => 'active',
        'enrolled_at'       => '2023-09-01',
    ];
}

// ── GET /dashboard/students ───────────────────────────────────────────────────

test('admin can list students', function () {
    $admin = createUserWithRole('admin');

    $this->actingAs($admin)
         ->get(route('dashboard.students.index'))
         ->assertOk();
});

// ── POST /dashboard/students ──────────────────────────────────────────────────

test('admin can create a student', function () {
    $admin = createUserWithRole('admin');
    createRole('student'); // controller looks up role by name at runtime
    ['faculty' => $fac, 'dept' => $dept] = makeStudentCrudFixture('CR');

    $this->actingAs($admin)
         ->post(route('dashboard.students.store'), makeStudentPayload($fac->id, $dept->id, 'CR1'))
         ->assertRedirect(route('dashboard.students.index'))
         ->assertSessionHas('success');

    $this->assertDatabaseHas('students', ['student_number' => 'SNUMCR1']);
    $user = User::whereHas('student', fn ($q) => $q->where('student_number', 'SNUMCR1'))->first();
    $this->assertDatabaseHas('role_user', ['user_id' => $user->id]);
});

test('student creation validates required fields', function () {
    $admin = createUserWithRole('admin');

    $this->actingAs($admin)
         ->post(route('dashboard.students.store'), [])
         ->assertSessionHasErrors(['national_id', 'first_name', 'last_name', 'email', 'password',
                                   'student_number', 'faculty_id', 'enrollment_status', 'enrolled_at']);
});

test('student number must be unique', function () {
    $admin = createUserWithRole('admin');
    ['faculty' => $fac, 'dept' => $dept] = makeStudentCrudFixture('DU');

    $existingUser = createUser(['email' => 'existing_stu@example.com', 'national_id' => 'STU_NID_EX']);
    Student::create([
        'user_id'           => $existingUser->id,
        'student_number'    => 'SNUMDUP',
        'faculty_id'        => $fac->id,
        'department_id'     => $dept->id,
        'academic_year'     => 1,
        'semester'          => 'first',
        'enrollment_status' => 'active',
        'enrolled_at'       => '2023-09-01',
    ]);

    $payload = makeStudentPayload($fac->id, $dept->id, 'DU2');
    $payload['student_number'] = 'SNUMDUP';

    $this->actingAs($admin)
         ->post(route('dashboard.students.store'), $payload)
         ->assertSessionHasErrors('student_number');
});

// ── PUT /dashboard/students/{student} ────────────────────────────────────────

test('admin can update a student', function () {
    $admin = createUserWithRole('admin');
    ['faculty' => $fac, 'dept' => $dept] = makeStudentCrudFixture('UP');

    $stuUser = createUser(['national_id' => 'STU_NID_UP', 'email' => 'stu_up@example.com']);
    $student = Student::create([
        'user_id'           => $stuUser->id,
        'student_number'    => 'SNUMUP1',
        'faculty_id'        => $fac->id,
        'department_id'     => $dept->id,
        'academic_year'     => 1,
        'semester'          => 'first',
        'enrollment_status' => 'active',
        'enrolled_at'       => '2023-09-01',
    ]);

    $this->actingAs($admin)
         ->put(route('dashboard.students.update', $student), [
             'national_id'       => 'STU_NID_UP',
             'first_name'        => $stuUser->first_name,
             'last_name'         => $stuUser->last_name,
             'email'             => 'stu_up@example.com',
             'gender'            => $stuUser->gender,
             'student_number'    => 'SNUMUP1',
             'faculty_id'        => $fac->id,
             'department_id'     => $dept->id,
             'academic_year'     => 3,
             'semester'          => 'second',
             'enrollment_status' => 'active',
             'enrolled_at'       => '2023-09-01',
             'is_active'         => '1',
         ])
         ->assertRedirect(route('dashboard.students.index'))
         ->assertSessionHas('success');

    $this->assertDatabaseHas('students', ['id' => $student->id, 'academic_year' => 3, 'semester' => 'second']);
});

// ── DELETE /dashboard/students/{student} ─────────────────────────────────────

test('admin can delete a student', function () {
    $admin = createUserWithRole('admin');
    ['faculty' => $fac, 'dept' => $dept] = makeStudentCrudFixture('DL');

    $stuUser = createUser(['national_id' => 'STU_NID_DL', 'email' => 'stu_dl@example.com']);
    $student = Student::create([
        'user_id'           => $stuUser->id,
        'student_number'    => 'SNUMDL1',
        'faculty_id'        => $fac->id,
        'department_id'     => $dept->id,
        'academic_year'     => 1,
        'semester'          => 'first',
        'enrollment_status' => 'active',
        'enrolled_at'       => '2023-09-01',
    ]);

    $this->actingAs($admin)
         ->delete(route('dashboard.students.destroy', $student))
         ->assertRedirect(route('dashboard.students.index'))
         ->assertSessionHas('success');

    $this->assertSoftDeleted('users', ['id' => $stuUser->id]);
});

// ── Forbidden ─────────────────────────────────────────────────────────────────

test('employee cannot create students', function () {
    $emp = createUserWithRole('employee');
    ['faculty' => $fac, 'dept' => $dept] = makeStudentCrudFixture('EP');

    $this->actingAs($emp)
         ->post(route('dashboard.students.store'), makeStudentPayload($fac->id, $dept->id, 'EP1'))
         ->assertForbidden();
});
