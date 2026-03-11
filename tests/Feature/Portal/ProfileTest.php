<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// ── GET /profile ──────────────────────────────────────────────────────────────

test('student can view profile page', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user] = makeStudent($fac, $dept);

    $this->actingAs($user)
         ->get(route('portal.profile'))
         ->assertOk();
});

test('professor can view profile page', function () {
    ['department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user] = makeProfessor($dept);

    $this->actingAs($user)
         ->get(route('portal.profile'))
         ->assertOk();
});

test('guest is redirected from profile page', function () {
    $this->get(route('portal.profile'))
         ->assertRedirect(route('portal.login'));
});

// ── PATCH /profile ────────────────────────────────────────────────────────────

test('user can update phone and date of birth', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user] = makeStudent($fac, $dept);

    $this->actingAs($user)
         ->patch(route('portal.profile.update'), [
             'phone'         => '+20123456789',
             'date_of_birth' => '1998-05-15',
         ])
         ->assertRedirect(route('portal.profile'))
         ->assertSessionHas('success');

    $user->refresh();
    expect($user->phone)->toBe('+20123456789')
        ->and($user->date_of_birth->format('Y-m-d'))->toBe('1998-05-15');
});

test('user can upload an avatar', function () {
    Storage::fake('public');

    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user] = makeStudent($fac, $dept);

    $this->actingAs($user)
         ->patch(route('portal.profile.update'), [
             'avatar' => UploadedFile::fake()->image('avatar.jpg', 100, 100),
         ])
         ->assertRedirect(route('portal.profile'));

    $user->refresh();
    expect($user->avatar_path)->not->toBeNull();
    Storage::disk('public')->assertExists($user->avatar_path);
});

test('user can remove their avatar', function () {
    Storage::fake('public');

    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user] = makeStudent($fac, $dept);

    // Give user an existing avatar
    $path = UploadedFile::fake()->image('old.jpg')->store('avatars/users', 'public');
    $user->update(['avatar_path' => $path]);

    $this->actingAs($user)
         ->patch(route('portal.profile.update'), ['remove_avatar' => '1'])
         ->assertRedirect(route('portal.profile'));

    expect($user->fresh()->avatar_path)->toBeNull();
    Storage::disk('public')->assertMissing($path);
});

test('date of birth must be in the past', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user] = makeStudent($fac, $dept);

    $this->actingAs($user)
         ->patch(route('portal.profile.update'), [
             'date_of_birth' => now()->addDay()->format('Y-m-d'),
         ])
         ->assertSessionHasErrors('date_of_birth');
});

test('avatar must be an image', function () {
    ['faculty' => $fac, 'department' => $dept] = makeFacultyDeptFixture();
    ['user' => $user] = makeStudent($fac, $dept);

    $this->actingAs($user)
         ->patch(route('portal.profile.update'), [
             'avatar' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
         ])
         ->assertSessionHasErrors('avatar');
});

test('guest cannot update profile', function () {
    $this->patch(route('portal.profile.update'), ['phone' => '+1'])
         ->assertRedirect(route('portal.login'));
});
