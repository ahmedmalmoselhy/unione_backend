<?php

use App\Models\SectionTeachingAssistant;

use function Pest\Laravel\actingAs;

test('admin can assign list and remove section teaching assistants', function () {
    $admin = createUserWithRole('admin');

    $term = makeOpenTerm('TA');
    $section = makeSection($term, 30);

    ['department' => $department] = makeFacultyDeptFixture('TA');
    ['professor' => $teachingAssistant] = makeProfessor($department);

    $create = actingAs($admin, 'sanctum')
        ->postJson("/api/admin/sections/{$section->id}/teaching-assistants", [
            'professor_id' => $teachingAssistant->id,
        ])
        ->assertCreated()
        ->assertJsonPath('assignment.section_id', $section->id)
        ->assertJsonPath('assignment.professor.id', $teachingAssistant->id);

    $assignmentId = $create->json('assignment.id');

    actingAs($admin, 'sanctum')
        ->postJson("/api/admin/sections/{$section->id}/teaching-assistants", [
            'professor_id' => $teachingAssistant->id,
        ])
        ->assertOk()
        ->assertJsonPath('assignment.id', $assignmentId);

    actingAs($admin, 'sanctum')
        ->getJson("/api/admin/sections/{$section->id}/teaching-assistants")
        ->assertOk()
        ->assertJsonCount(1, 'teaching_assistants')
        ->assertJsonPath('teaching_assistants.0.id', $assignmentId);

    actingAs($admin, 'sanctum')
        ->deleteJson("/api/admin/sections/{$section->id}/teaching-assistants/999999")
        ->assertNotFound();

    actingAs($admin, 'sanctum')
        ->deleteJson("/api/admin/sections/{$section->id}/teaching-assistants/{$assignmentId}")
        ->assertOk();

    expect(SectionTeachingAssistant::query()->find($assignmentId))->toBeNull();
});

test('non-admin user is forbidden from section teaching assistant routes', function () {
    $student = createUserWithRole('student');

    $term = makeOpenTerm('TAF');
    $section = makeSection($term, 30);

    ['department' => $department] = makeFacultyDeptFixture('TAF');
    ['professor' => $teachingAssistant] = makeProfessor($department);

    actingAs($student, 'sanctum')
        ->postJson("/api/admin/sections/{$section->id}/teaching-assistants", [
            'professor_id' => $teachingAssistant->id,
        ])
        ->assertForbidden();
});
