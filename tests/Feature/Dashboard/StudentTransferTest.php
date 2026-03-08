<?php

use App\Models\Department;
use App\Models\Faculty;
use App\Models\Student;

/**
 * Shared fixture: create a faculty, two departments, and a student.
 */
function makeStudentFixture(): array
{
    $faculty = Faculty::create([
        'name'            => 'Engineering',
        'name_ar'         => 'الهندسة',
        'code'            => 'ENG',
        'enrollment_type' => 'immediate',
        'is_active'       => true,
    ]);

    $dept1 = Department::create([
        'faculty_id' => $faculty->id,
        'name'       => 'Computer Science',
        'name_ar'    => 'علم الحاسوب',
        'code'       => 'CS',
        'type'       => 'academic',
        'is_active'  => true,
    ]);

    $dept2 = Department::create([
        'faculty_id' => $faculty->id,
        'name'       => 'Information Systems',
        'name_ar'    => 'نظم المعلومات',
        'code'       => 'IS',
        'type'       => 'academic',
        'is_active'  => true,
    ]);

    $studentUser = createUser();

    $student = Student::create([
        'user_id'           => $studentUser->id,
        'student_number'    => 'S-0001',
        'faculty_id'        => $faculty->id,
        'department_id'     => $dept1->id,
        'academic_year'     => 1,
        'semester'          => 'first',
        'enrollment_status' => 'active',
        'enrolled_at'       => now()->toDateString(),
    ]);

    return compact('faculty', 'dept1', 'dept2', 'student');
}

// ── Successful transfer ──────────────────────────────────────────────────────

test('admin can transfer a student to a different department', function () {
    $admin = createUserWithRole('admin');
    ['student' => $student, 'dept1' => $dept1, 'dept2' => $dept2] = makeStudentFixture();

    $this->actingAs($admin)
         ->post(route('dashboard.students.transfer', $student), [
             'to_department_id' => $dept2->id,
         ])
         ->assertRedirect(route('dashboard.students.show', $student));

    $this->assertDatabaseHas('students', [
        'id'            => $student->id,
        'department_id' => $dept2->id,
    ]);
});

test('transfer creates a department history record', function () {
    $admin = createUserWithRole('admin');
    ['student' => $student, 'dept1' => $dept1, 'dept2' => $dept2] = makeStudentFixture();

    $this->actingAs($admin)
         ->post(route('dashboard.students.transfer', $student), [
             'to_department_id' => $dept2->id,
             'note'             => 'Grade improvement transfer',
         ]);

    $this->assertDatabaseHas('student_department_history', [
        'student_id'         => $student->id,
        'from_department_id' => $dept1->id,
        'to_department_id'   => $dept2->id,
        'switched_by'        => $admin->id,
        'note'               => 'Grade improvement transfer',
    ]);
});

// ── Validation ───────────────────────────────────────────────────────────────

test('transfer rejects a missing destination department', function () {
    $admin = createUserWithRole('admin');
    ['student' => $student] = makeStudentFixture();

    $this->actingAs($admin)
         ->post(route('dashboard.students.transfer', $student), [])
         ->assertSessionHasErrors('to_department_id');
});

test('transfer rejects a non-existent destination department id', function () {
    $admin = createUserWithRole('admin');
    ['student' => $student] = makeStudentFixture();

    $this->actingAs($admin)
         ->post(route('dashboard.students.transfer', $student), [
             'to_department_id' => 999999,
         ])
         ->assertSessionHasErrors('to_department_id');
});

// ── Authorization ────────────────────────────────────────────────────────────

test('plain employee cannot transfer students', function () {
    $employee = createUserWithRole('employee');
    ['student' => $student, 'dept2' => $dept2] = makeStudentFixture();

    $this->actingAs($employee)
         ->post(route('dashboard.students.transfer', $student), [
             'to_department_id' => $dept2->id,
         ])
         ->assertForbidden();
});
