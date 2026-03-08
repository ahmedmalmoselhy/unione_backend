<?php

// ── EnsureApiRole middleware ─────────────────────────────────────────────────

test('non-student is forbidden from student routes', function () {
    $admin = createUserWithRole('admin');

    $this->actingAs($admin, 'sanctum')
         ->getJson('/api/student/profile')
         ->assertForbidden();
});

test('non-professor is forbidden from professor routes', function () {
    $admin = createUserWithRole('admin');

    $this->actingAs($admin, 'sanctum')
         ->getJson('/api/professor/profile')
         ->assertForbidden();
});

test('unauthenticated user cannot access student routes', function () {
    $this->getJson('/api/student/profile')
         ->assertUnauthorized();
});

test('unauthenticated user cannot access professor routes', function () {
    $this->getJson('/api/professor/profile')
         ->assertUnauthorized();
});
