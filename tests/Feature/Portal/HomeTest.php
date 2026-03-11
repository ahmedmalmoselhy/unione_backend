<?php

// ── GET /home ─────────────────────────────────────────────────────────────────

test('student can view portal home', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user] = makeStudent($fac, $dept);

    $this->actingAs($user)
         ->get(route('portal.home'))
         ->assertOk();
});

test('professor can view portal home', function () {
    ['department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user] = makeProfessor($dept);

    $this->actingAs($user)
         ->get(route('portal.home'))
         ->assertOk();
});

test('guest is redirected from portal home', function () {
    $this->get(route('portal.home'))
         ->assertRedirect(route('portal.login'));
});
