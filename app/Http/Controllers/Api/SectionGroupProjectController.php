<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\GroupProject;
use App\Models\GroupProjectMember;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SectionGroupProjectController extends Controller
{
    /**
     * GET /api/admin/sections/{section}/group-projects
     */
    public function index(Section $section): JsonResponse
    {
        $projects = $section->groupProjects()
            ->with(['members.student.user'])
            ->orderBy('id')
            ->get()
            ->map(fn (GroupProject $project) => $this->formatProject($project));

        return response()->json(['group_projects' => $projects]);
    }

    /**
     * POST /api/admin/sections/{section}/group-projects
     */
    public function store(Request $request, Section $section): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date'],
            'max_members' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $project = $section->groupProjects()->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'due_at' => $data['due_at'] ?? null,
            'max_members' => $data['max_members'] ?? 5,
            'is_active' => $data['is_active'] ?? true,
            'created_by_user_id' => $request->user()?->id,
        ]);

        return response()->json([
            'message' => 'Group project created successfully.',
            'group_project' => $this->formatProject($project->fresh('members.student.user')),
        ], 201);
    }

    /**
     * PATCH /api/admin/sections/{section}/group-projects/{groupProject}
     */
    public function update(Request $request, Section $section, GroupProject $groupProject): JsonResponse
    {
        if ((int) $groupProject->section_id !== (int) $section->id) {
            return response()->json(['message' => 'Group project not found.'], 404);
        }

        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'due_at' => ['sometimes', 'nullable', 'date'],
            'max_members' => ['sometimes', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (array_key_exists('max_members', $data)) {
            $membersCount = $groupProject->members()->count();
            if ($membersCount > (int) $data['max_members']) {
                return response()->json(['message' => 'max_members cannot be less than current member count.'], 409);
            }
        }

        $groupProject->update($data);

        return response()->json([
            'message' => 'Group project updated successfully.',
            'group_project' => $this->formatProject($groupProject->fresh('members.student.user')),
        ]);
    }

    /**
     * DELETE /api/admin/sections/{section}/group-projects/{groupProject}
     */
    public function destroy(Section $section, GroupProject $groupProject): JsonResponse
    {
        if ((int) $groupProject->section_id !== (int) $section->id) {
            return response()->json(['message' => 'Group project not found.'], 404);
        }

        $groupProject->delete();

        return response()->json(['message' => 'Group project deleted successfully.']);
    }

    /**
     * POST /api/admin/sections/{section}/group-projects/{groupProject}/members
     */
    public function storeMember(Request $request, Section $section, GroupProject $groupProject): JsonResponse
    {
        if ((int) $groupProject->section_id !== (int) $section->id) {
            return response()->json(['message' => 'Group project not found.'], 404);
        }

        $data = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
        ]);

        $student = Student::findOrFail($data['student_id']);

        $isEnrolled = Enrollment::query()
            ->where('section_id', $section->id)
            ->where('student_id', $student->id)
            ->whereIn('status', ['registered', 'completed'])
            ->exists();

        if (! $isEnrolled) {
            return response()->json(['message' => 'Student must be enrolled in this section.'], 400);
        }

        $existing = $groupProject->members()->where('student_id', $student->id)->first();
        if ($existing) {
            return response()->json([
                'message' => 'Student already assigned to this group project.',
                'member' => $this->formatMember($existing->load('student.user')),
            ]);
        }

        if ($groupProject->members()->count() >= (int) $groupProject->max_members) {
            return response()->json(['message' => 'Group project is at maximum capacity.'], 409);
        }

        $member = $groupProject->members()->create([
            'student_id' => $student->id,
            'joined_at' => now(),
        ]);

        return response()->json([
            'message' => 'Group project member added successfully.',
            'member' => $this->formatMember($member->load('student.user')),
        ], 201);
    }

    /**
     * DELETE /api/admin/sections/{section}/group-projects/{groupProject}/members/{groupProjectMember}
     */
    public function destroyMember(
        Section $section,
        GroupProject $groupProject,
        GroupProjectMember $groupProjectMember
    ): JsonResponse {
        if ((int) $groupProject->section_id !== (int) $section->id) {
            return response()->json(['message' => 'Group project not found.'], 404);
        }

        if ((int) $groupProjectMember->group_project_id !== (int) $groupProject->id) {
            return response()->json(['message' => 'Group project member not found.'], 404);
        }

        $groupProjectMember->delete();

        return response()->json(['message' => 'Group project member removed successfully.']);
    }

    private function formatProject(GroupProject $project): array
    {
        return [
            'id' => $project->id,
            'section_id' => $project->section_id,
            'title' => $project->title,
            'description' => $project->description,
            'due_at' => $project->due_at?->toDateTimeString(),
            'max_members' => $project->max_members,
            'is_active' => (bool) $project->is_active,
            'created_by_user_id' => $project->created_by_user_id,
            'created_at' => $project->created_at?->toDateTimeString(),
            'updated_at' => $project->updated_at?->toDateTimeString(),
            'members' => $project->members->map(fn (GroupProjectMember $member) => $this->formatMember($member))->values(),
        ];
    }

    private function formatMember(GroupProjectMember $member): array
    {
        return [
            'id' => $member->id,
            'group_project_id' => $member->group_project_id,
            'student_id' => $member->student_id,
            'student_number' => $member->student->student_number,
            'student_name' => trim(($member->student->user->first_name ?? '') . ' ' . ($member->student->user->last_name ?? '')),
            'joined_at' => $member->joined_at?->toDateTimeString(),
            'created_at' => $member->created_at?->toDateTimeString(),
            'updated_at' => $member->updated_at?->toDateTimeString(),
        ];
    }
}
