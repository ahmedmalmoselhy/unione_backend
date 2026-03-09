<?php

use App\Models\AcademicTerm;

function makeTermData(int $year, string $semester = 'first'): array
{
    return [
        'name'                    => "Term {$year} {$semester}",
        'name_ar'                 => "فصل {$year}",
        'academic_year'           => $year,
        'semester'                => $semester,
        'starts_at'               => '2025-09-01',
        'ends_at'                 => '2026-01-31',
        'registration_starts_at'  => '2025-08-01',
        'registration_ends_at'    => '2025-08-31',
    ];
}

// ── GET /dashboard/academic-terms ────────────────────────────────────────────

test('admin can list academic terms', function () {
    $admin = createUserWithRole('admin');

    $this->actingAs($admin)
         ->get(route('dashboard.academic-terms.index'))
         ->assertOk();
});

test('employee cannot access academic terms', function () {
    $emp = createUserWithRole('employee');

    $this->actingAs($emp)
         ->get(route('dashboard.academic-terms.index'))
         ->assertForbidden();
});

// ── POST /dashboard/academic-terms ───────────────────────────────────────────

test('admin can create an academic term', function () {
    $admin = createUserWithRole('admin');

    $this->actingAs($admin)
         ->post(route('dashboard.academic-terms.store'), makeTermData(2040))
         ->assertRedirect(route('dashboard.academic-terms.index'))
         ->assertSessionHas('success');

    $this->assertDatabaseHas('academic_terms', ['academic_year' => 2040, 'semester' => 'first']);
});

test('academic term creation validates required fields', function () {
    $admin = createUserWithRole('admin');

    $this->actingAs($admin)
         ->post(route('dashboard.academic-terms.store'), [])
         ->assertSessionHasErrors(['name', 'name_ar', 'academic_year', 'semester', 'starts_at', 'ends_at']);
});

test('semester must be one of first second summer', function () {
    $admin = createUserWithRole('admin');

    $data = makeTermData(2041);
    $data['semester'] = 'spring'; // invalid

    $this->actingAs($admin)
         ->post(route('dashboard.academic-terms.store'), $data)
         ->assertSessionHasErrors('semester');
});

// ── PUT /dashboard/academic-terms/{academicTerm} ──────────────────────────────

test('admin can update an academic term', function () {
    $admin = createUserWithRole('admin');

    $term = AcademicTerm::create(makeTermData(2042));

    $this->actingAs($admin)
         ->put(route('dashboard.academic-terms.update', $term), array_merge(makeTermData(2042), [
             'name'      => 'Updated Term',
             'name_ar'   => 'فصل محدث',
             'is_active' => '0',
         ]))
         ->assertRedirect(route('dashboard.academic-terms.index'))
         ->assertSessionHas('success');

    $this->assertDatabaseHas('academic_terms', ['id' => $term->id, 'name' => 'Updated Term']);
});

test('activating a term deactivates all other active terms', function () {
    $admin = createUserWithRole('admin');

    $termA = AcademicTerm::create(array_merge(makeTermData(2043), ['is_active' => true]));
    $termB = AcademicTerm::create(makeTermData(2044, 'second'));

    $this->actingAs($admin)
         ->put(route('dashboard.academic-terms.update', $termB), array_merge(makeTermData(2044, 'second'), [
             'is_active' => '1',
         ]));

    $this->assertDatabaseHas('academic_terms', ['id' => $termA->id, 'is_active' => false]);
    $this->assertDatabaseHas('academic_terms', ['id' => $termB->id, 'is_active' => true]);
});

// ── DELETE /dashboard/academic-terms/{academicTerm} ───────────────────────────

test('admin can delete an academic term with no sections', function () {
    $admin = createUserWithRole('admin');

    $term = AcademicTerm::create(makeTermData(2045, 'summer'));

    $this->actingAs($admin)
         ->delete(route('dashboard.academic-terms.destroy', $term))
         ->assertRedirect(route('dashboard.academic-terms.index'))
         ->assertSessionHas('success');

    $this->assertDatabaseMissing('academic_terms', ['id' => $term->id]);
});
