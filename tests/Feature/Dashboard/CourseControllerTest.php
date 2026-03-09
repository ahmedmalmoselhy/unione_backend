<?php

use App\Models\Course;
use App\Models\Department;
use App\Models\Faculty;

function makeCourseFixture(): array
{
    static $n = 0;
    $n++;

    $faculty = Faculty::create([
        'name'            => "CourseFac{$n}",
        'name_ar'         => 'كلية',
        'code'            => "CFC{$n}",
        'enrollment_type' => 'immediate',
        'is_active'       => true,
    ]);

    $dept = Department::create([
        'faculty_id' => $faculty->id,
        'name'       => "CourseDept{$n}",
        'name_ar'    => 'قسم',
        'code'       => "CDC{$n}",
        'type'       => 'academic',
        'is_active'  => true,
    ]);

    return compact('faculty', 'dept');
}

function makeCoursePayload(string $code, int $deptId): array
{
    return [
        'code'          => $code,
        'name'          => "Course {$code}",
        'name_ar'       => 'مادة',
        'credit_hours'  => 3,
        'lecture_hours' => 2,
        'lab_hours'     => 0,
        'level'         => 1,
        'is_elective'   => '0',
        'departments'   => [['id' => $deptId, 'is_owner' => true]],
    ];
}

// ── GET /dashboard/courses ────────────────────────────────────────────────────

test('admin can list courses', function () {
    $admin = createUserWithRole('admin');

    $this->actingAs($admin)
         ->get(route('dashboard.courses.index'))
         ->assertOk();
});

// ── POST /dashboard/courses ───────────────────────────────────────────────────

test('admin can create a course with department assignment', function () {
    $admin = createUserWithRole('admin');
    ['dept' => $dept] = makeCourseFixture();

    $this->actingAs($admin)
         ->post(route('dashboard.courses.store'), makeCoursePayload('CRSCR1', $dept->id))
         ->assertRedirect(route('dashboard.courses.index'))
         ->assertSessionHas('success');

    $course = Course::where('code', 'CRSCR1')->first();
    expect($course)->not->toBeNull();
    $this->assertDatabaseHas('department_course', [
        'course_id'     => $course->id,
        'department_id' => $dept->id,
        'is_owner'      => true,
    ]);
});

test('course code must be unique', function () {
    $admin = createUserWithRole('admin');
    ['dept' => $dept] = makeCourseFixture();

    Course::create([
        'code'          => 'CRDUP',
        'name'          => 'Existing',
        'name_ar'       => 'موجود',
        'credit_hours'  => 3,
        'lecture_hours' => 2,
        'level'         => 1,
        'is_elective'   => false,
        'is_active'     => true,
    ]);

    $this->actingAs($admin)
         ->post(route('dashboard.courses.store'), makeCoursePayload('CRDUP', $dept->id))
         ->assertSessionHasErrors('code');
});

test('course creation requires at least one department', function () {
    $admin = createUserWithRole('admin');

    $this->actingAs($admin)
         ->post(route('dashboard.courses.store'), [
             'code'          => 'CRNOD',
             'name'          => 'No Dept',
             'name_ar'       => 'بدون',
             'credit_hours'  => 3,
             'lecture_hours' => 2,
             'lab_hours'     => 0,
             'level'         => 1,
         ])
         ->assertSessionHasErrors('departments');
});

// ── PUT /dashboard/courses/{course} ───────────────────────────────────────────

test('admin can update a course', function () {
    $admin = createUserWithRole('admin');
    ['dept' => $dept] = makeCourseFixture();

    $course = Course::create([
        'code'          => 'CRUPC',
        'name'          => 'Old Name',
        'name_ar'       => 'قديم',
        'credit_hours'  => 3,
        'lecture_hours' => 2,
        'level'         => 1,
        'is_elective'   => false,
        'is_active'     => true,
    ]);
    $course->departments()->attach($dept->id, ['is_owner' => true]);

    $this->actingAs($admin)
         ->put(route('dashboard.courses.update', $course), [
             'code'          => 'CRUPC',
             'name'          => 'New Name',
             'name_ar'       => 'جديد',
             'credit_hours'  => 4,
             'lecture_hours' => 3,
             'lab_hours'     => 0,
             'level'         => 2,
             'is_active'     => '1',
             'departments'   => [['id' => $dept->id, 'is_owner' => true]],
         ])
         ->assertRedirect(route('dashboard.courses.index'))
         ->assertSessionHas('success');

    $this->assertDatabaseHas('courses', ['id' => $course->id, 'name' => 'New Name', 'credit_hours' => 4]);
});

// ── DELETE /dashboard/courses/{course} ────────────────────────────────────────

test('admin can delete a course with no sections', function () {
    $admin = createUserWithRole('admin');
    ['dept' => $dept] = makeCourseFixture();

    $course = Course::create([
        'code'          => 'CRDEL',
        'name'          => 'Delete Me',
        'name_ar'       => 'احذفني',
        'credit_hours'  => 3,
        'lecture_hours' => 2,
        'level'         => 1,
        'is_elective'   => false,
        'is_active'     => true,
    ]);
    $course->departments()->attach($dept->id, ['is_owner' => true]);

    $this->actingAs($admin)
         ->delete(route('dashboard.courses.destroy', $course))
         ->assertRedirect(route('dashboard.courses.index'))
         ->assertSessionHas('success');

    $this->assertDatabaseMissing('courses', ['id' => $course->id]);
});

test('employee cannot create courses', function () {
    $emp = createUserWithRole('employee');
    ['dept' => $dept] = makeCourseFixture();

    $this->actingAs($emp)
         ->post(route('dashboard.courses.store'), makeCoursePayload('CRSNP', $dept->id))
         ->assertForbidden();
});
