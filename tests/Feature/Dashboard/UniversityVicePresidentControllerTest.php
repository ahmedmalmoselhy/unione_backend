<?php

use App\Models\Professor;
use App\Models\University;
use App\Models\UniversityVicePresident;

function makeVpFixture(string $suffix = ''): array
{
    static $n = 0;
    $n++;

    $university = University::first() ?? University::create([
        'name'    => 'VP Test University',
        'name_ar' => 'جامعة اختبار',
        'address' => '1 VP Street',
    ]);

    ['department' => $dept] = makeFacultyDeptFixture("VP{$n}{$suffix}");
    ['professor' => $prof] = makeProfessor($dept);

    return compact('university', 'prof');
}

function makeVpPayload(int $professorId): array
{
    return [
        'professor_id' => $professorId,
        'title'        => 'Vice President for Academic Affairs',
        'title_ar'     => 'نائب الرئيس للشؤون الأكاديمية',
        'order'        => 1,
        'is_active'    => '1',
        'appointed_at' => '2024-01-01',
    ];
}

// ── POST /dashboard/university/vice-presidents ────────────────────────────────

test('admin can create a vice president', function () {
    $admin = createUserWithRole('admin');
    ['university' => $uni, 'prof' => $prof] = makeVpFixture('CR');

    $this->actingAs($admin)
         ->post(route('dashboard.university.vice-presidents.store'), makeVpPayload($prof->id))
         ->assertRedirect(route('dashboard.university.show'))
         ->assertSessionHas('success');

    $this->assertDatabaseHas('university_vice_presidents', [
        'professor_id'  => $prof->id,
        'university_id' => $uni->id,
    ]);
});

test('same professor cannot be assigned as VP twice', function () {
    $admin = createUserWithRole('admin');
    ['university' => $uni, 'prof' => $prof] = makeVpFixture('DB');

    UniversityVicePresident::create([
        'university_id' => $uni->id,
        'professor_id'  => $prof->id,
        'title'         => 'Existing VP',
        'title_ar'      => 'نائب موجود',
        'order'         => 0,
        'appointed_at'  => '2024-01-01',
    ]);

    $this->actingAs($admin)
         ->post(route('dashboard.university.vice-presidents.store'), makeVpPayload($prof->id))
         ->assertSessionHasErrors('professor_id');
});

// ── PUT /dashboard/university/vice-presidents/{vice_president} ────────────────

test('admin can update a vice president', function () {
    $admin = createUserWithRole('admin');
    ['university' => $uni, 'prof' => $prof] = makeVpFixture('UP');

    $vp = UniversityVicePresident::create([
        'university_id' => $uni->id,
        'professor_id'  => $prof->id,
        'title'         => 'Old Title',
        'title_ar'      => 'عنوان قديم',
        'order'         => 0,
        'appointed_at'  => '2024-01-01',
    ]);

    $this->actingAs($admin)
         ->put(route('dashboard.university.vice-presidents.update', $vp), [
             'professor_id' => $prof->id,
             'title'        => 'New Title',
             'title_ar'     => 'عنوان جديد',
             'order'        => 2,
             'is_active'    => '1',
             'appointed_at' => '2024-01-01',
         ])
         ->assertRedirect(route('dashboard.university.show'))
         ->assertSessionHas('success');

    $this->assertDatabaseHas('university_vice_presidents', ['id' => $vp->id, 'title' => 'New Title']);
});

// ── DELETE /dashboard/university/vice-presidents/{vice_president} ─────────────

test('admin can delete a vice president', function () {
    $admin = createUserWithRole('admin');
    ['university' => $uni, 'prof' => $prof] = makeVpFixture('DL');

    $vp = UniversityVicePresident::create([
        'university_id' => $uni->id,
        'professor_id'  => $prof->id,
        'title'         => 'Delete Me',
        'title_ar'      => 'احذفني',
        'order'         => 0,
        'appointed_at'  => '2024-01-01',
    ]);

    $this->actingAs($admin)
         ->delete(route('dashboard.university.vice-presidents.destroy', $vp))
         ->assertRedirect(route('dashboard.university.show'))
         ->assertSessionHas('success');

    $this->assertDatabaseMissing('university_vice_presidents', ['id' => $vp->id]);
});

// ── Forbidden ─────────────────────────────────────────────────────────────────

test('employee cannot manage vice presidents', function () {
    $emp = createUserWithRole('employee');
    ['prof' => $prof] = makeVpFixture('EP');

    $this->actingAs($emp)
         ->post(route('dashboard.university.vice-presidents.store'), makeVpPayload($prof->id))
         ->assertForbidden();
});
