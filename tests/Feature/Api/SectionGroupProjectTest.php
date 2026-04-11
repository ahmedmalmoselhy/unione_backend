<?php

use App\Models\Enrollment;
use App\Models\GroupProject;

use function Pest\Laravel\actingAs;

test('admin can manage section group project lifecycle', function () {
    $admin = createUserWithRole('admin');

    $term = makeOpenTerm('GP');
    $section = makeSection($term, 30);

    ['faculty' => $faculty, 'department' => $department] = makeFacultyDeptFixture('GP');
    ['student' => $enrolledStudent] = makeStudent($faculty, $department);
    ['student' => $outsiderStudent] = makeStudent($faculty, $department);

    Enrollment::create([
        'student_id' => $enrolledStudent->id,
        'section_id' => $section->id,
        'academic_term_id' => $term->id,
        'status' => 'registered',
    ]);

    actingAs($admin, 'sanctum')
        ->postJson("/api/admin/sections/{$section->id}/group-projects", [
            'description' => 'Missing title',
        ])
        ->assertUnprocessable();

    $create = actingAs($admin, 'sanctum')
        ->postJson("/api/admin/sections/{$section->id}/group-projects", [
            'title' => 'Capstone Team Project',
            'description' => 'Build integrated module',
            'max_members' => 2,
        ])
        ->assertCreated()
        ->assertJsonPath('group_project.section_id', $section->id)
        ->assertJsonPath('group_project.title', 'Capstone Team Project');

    $projectId = $create->json('group_project.id');

    actingAs($admin, 'sanctum')
        ->postJson("/api/admin/sections/{$section->id}/group-projects/{$projectId}/members", [
            'student_id' => $outsiderStudent->id,
        ])
        ->assertStatus(400);

    $memberCreate = actingAs($admin, 'sanctum')
        ->postJson("/api/admin/sections/{$section->id}/group-projects/{$projectId}/members", [
            'student_id' => $enrolledStudent->id,
        ])
        ->assertCreated();

    $memberId = $memberCreate->json('member.id');

    actingAs($admin, 'sanctum')
        ->postJson("/api/admin/sections/{$section->id}/group-projects/{$projectId}/members", [
            'student_id' => $enrolledStudent->id,
        ])
        ->assertOk();

    actingAs($admin, 'sanctum')
        ->patchJson("/api/admin/sections/{$section->id}/group-projects/{$projectId}", [
            'title' => 'Updated Capstone Team Project',
            'is_active' => false,
        ])
        ->assertOk()
        ->assertJsonPath('group_project.title', 'Updated Capstone Team Project')
        ->assertJsonPath('group_project.is_active', false);

    actingAs($admin, 'sanctum')
        ->getJson("/api/admin/sections/{$section->id}/group-projects")
        ->assertOk()
        ->assertJsonCount(1, 'group_projects')
        ->assertJsonPath('group_projects.0.id', $projectId);

    actingAs($admin, 'sanctum')
        ->deleteJson("/api/admin/sections/{$section->id}/group-projects/{$projectId}/members/999999")
        ->assertNotFound();

    actingAs($admin, 'sanctum')
        ->deleteJson("/api/admin/sections/{$section->id}/group-projects/{$projectId}/members/{$memberId}")
        ->assertOk();

    actingAs($admin, 'sanctum')
        ->deleteJson("/api/admin/sections/{$section->id}/group-projects/{$projectId}")
        ->assertOk();

    expect(GroupProject::query()->find($projectId))->toBeNull();
});

test('non-admin user is forbidden from section group project routes', function () {
    $studentUser = createUserWithRole('student');

    $term = makeOpenTerm('GPF');
    $section = makeSection($term, 30);

    actingAs($studentUser, 'sanctum')
        ->postJson("/api/admin/sections/{$section->id}/group-projects", [
            'title' => 'Forbidden Project',
            'max_members' => 3,
        ])
        ->assertForbidden();
});
