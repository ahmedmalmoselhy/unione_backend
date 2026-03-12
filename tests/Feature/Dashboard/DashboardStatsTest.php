<?php

use App\Models\Department;
use App\Models\Enrollment;
use App\Models\Faculty;
use App\Models\Grade;
use App\Models\Student;

// ── GET /dashboard/stats ──────────────────────────────────────────────────────

test('system admin can access stats endpoint', function () {
    $admin = createUserWithRole('admin');

    $this->actingAs($admin)
         ->getJson(route('dashboard.stats.index'))
         ->assertOk()
         ->assertJsonStructure([
             'overview'           => ['students', 'professors', 'courses', 'sections'],
             'enrollment_status',
             'grade_distribution',
             'gpa_distribution'   => ['0.0-1.99', '2.0-2.49', '2.5-2.99', '3.0-3.49', '3.5-4.0'],
             'enrollment_rates',
         ]);
});

test('non-admin user cannot access stats endpoint', function () {
    $user = createUserWithRole('student');

    $this->actingAs($user)
         ->getJson(route('dashboard.stats.index'))
         ->assertRedirect();
});

test('stats overview counts reflect database state', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture('STATS');
    makeStudent($fac, $dept);
    makeStudent($fac, $dept);
    makeProfessor($dept);

    $admin = createUserWithRole('admin');

    $response = $this->actingAs($admin)
         ->getJson(route('dashboard.stats.index'))
         ->assertOk();

    expect($response->json('overview.students'))->toBeGreaterThanOrEqual(2);
    expect($response->json('overview.professors'))->toBeGreaterThanOrEqual(1);
});

test('stats enrollment_status distribution lists active students', function () {
    ['faculty' => $f, 'department' => $d] = makeFacultyDeptFixture('ENRST');
    makeStudent($f, $d); // default status = active

    $admin = createUserWithRole('admin');

    $response = $this->actingAs($admin)
         ->getJson(route('dashboard.stats.index'))
         ->assertOk();

    expect($response->json('enrollment_status'))->toHaveKey('active');
    expect($response->json('enrollment_status.active'))->toBeGreaterThanOrEqual(1);
});

test('stats grade_distribution shows letter grades', function () {
    ['faculty' => $f, 'department' => $d] = makeFacultyDeptFixture('GRD');
    ['user' => $u, 'student' => $s] = makeStudent($f, $d);
    $term    = makeOpenTerm();
    $section = makeSection($term);

    $enrollment = Enrollment::create([
        'student_id'       => $s->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'completed',
        'registered_at'    => now(),
    ]);

    Grade::create([
        'enrollment_id' => $enrollment->id,
        'midterm'       => 30,
        'final'         => 40,
        'coursework'    => 20,
        'total'         => 90,
        'letter_grade'  => 'A',
        'grade_points'  => 4.0,
        'graded_by'     => $u->id,
        'graded_at'     => now(),
    ]);

    $admin = createUserWithRole('admin');

    $response = $this->actingAs($admin)
         ->getJson(route('dashboard.stats.index'))
         ->assertOk();

    expect($response->json('grade_distribution'))->toHaveKey('A');
    expect($response->json('grade_distribution.A'))->toBeGreaterThanOrEqual(1);
});

test('stats enrollment_rates returns fill info for active-term sections', function () {
    $term    = makeOpenTerm(); // is_active = true
    $section = makeSection($term, capacity: 5);
    ['faculty' => $f, 'department' => $d] = makeFacultyDeptFixture('RATE');
    ['student' => $s] = makeStudent($f, $d);

    Enrollment::create([
        'student_id'       => $s->id,
        'section_id'       => $section->id,
        'academic_term_id' => $term->id,
        'status'           => 'registered',
        'registered_at'    => now(),
    ]);

    $admin = createUserWithRole('admin');

    $response = $this->actingAs($admin)
         ->getJson(route('dashboard.stats.index'))
         ->assertOk();

    $rates = collect($response->json('enrollment_rates'));
    $rate  = $rates->firstWhere('section_id', $section->id);

    expect($rate)->not->toBeNull();
    expect($rate['filled'])->toBe(1);
    expect($rate['capacity'])->toBe(5);
    expect((float) $rate['fill_pct'])->toBe(20.0);
});

test('faculty admin stats are scoped to their faculty', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture('FSCOPE');
    makeStudent($fac, $dept);

    ['faculty' => $otherFac, 'department' => $otherDept] = makeFacultyDeptFixture('FSCOPE2');
    makeStudent($otherFac, $otherDept);

    $admin = createUserWithRole('faculty_admin', [], ['faculty_id' => $fac->id]);

    $response = $this->actingAs($admin)
         ->getJson(route('dashboard.stats.index'))
         ->assertOk();

    // Faculty admin should only see their own faculty's 1 student
    expect($response->json('overview.students'))->toBe(1);
});

test('gpa distribution brackets cover all students', function () {
    ['faculty' => $f, 'department' => $d] = makeFacultyDeptFixture('GPABKT');

    // Create students with various GPAs
    foreach ([1.5, 2.2, 2.7, 3.2, 3.8] as $gpa) {
        $s = makeStudent($f, $d)['student'];
        $s->update(['gpa' => $gpa]);
    }

    $admin = createUserWithRole('admin');

    $response = $this->actingAs($admin)
         ->getJson(route('dashboard.stats.index'))
         ->assertOk();

    $dist = $response->json('gpa_distribution');
    expect($dist['0.0-1.99'])->toBeGreaterThanOrEqual(1);
    expect($dist['2.0-2.49'])->toBeGreaterThanOrEqual(1);
    expect($dist['2.5-2.99'])->toBeGreaterThanOrEqual(1);
    expect($dist['3.0-3.49'])->toBeGreaterThanOrEqual(1);
    expect($dist['3.5-4.0'])->toBeGreaterThanOrEqual(1);
});
