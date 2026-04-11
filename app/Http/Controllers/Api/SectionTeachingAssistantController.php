<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Professor;
use App\Models\Section;
use App\Models\SectionTeachingAssistant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SectionTeachingAssistantController extends Controller
{
    /**
     * GET /api/admin/sections/{section}/teaching-assistants
     */
    public function index(Section $section): JsonResponse
    {
        $items = $section->teachingAssistants()
            ->with('professor.user')
            ->orderBy('id')
            ->get()
            ->map(function (SectionTeachingAssistant $assignment) {
                return [
                    'id' => $assignment->id,
                    'section_id' => $assignment->section_id,
                    'assigned_by_user_id' => $assignment->assigned_by_user_id,
                    'created_at' => $assignment->created_at?->toDateTimeString(),
                    'professor' => [
                        'id' => $assignment->professor->id,
                        'staff_number' => $assignment->professor->staff_number,
                        'name' => trim(($assignment->professor->user->first_name ?? '') . ' ' . ($assignment->professor->user->last_name ?? '')),
                        'email' => $assignment->professor->user->email ?? null,
                    ],
                ];
            })
            ->values();

        return response()->json(['teaching_assistants' => $items]);
    }

    /**
     * POST /api/admin/sections/{section}/teaching-assistants
     */
    public function store(Request $request, Section $section): JsonResponse
    {
        $data = $request->validate([
            'professor_id' => ['required', 'integer', 'exists:professors,id'],
        ]);

        $professor = Professor::findOrFail($data['professor_id']);

        $assignment = SectionTeachingAssistant::firstOrCreate(
            [
                'section_id' => $section->id,
                'professor_id' => $professor->id,
            ],
            [
                'assigned_by_user_id' => $request->user()?->id,
            ]
        );

        $payload = [
            'id' => $assignment->id,
            'section_id' => $assignment->section_id,
            'professor' => [
                'id' => $professor->id,
                'staff_number' => $professor->staff_number,
            ],
        ];

        if ($assignment->wasRecentlyCreated) {
            return response()->json([
                'message' => 'Teaching assistant assigned successfully.',
                'assignment' => $payload,
            ], 201);
        }

        return response()->json([
            'message' => 'Teaching assistant already assigned.',
            'assignment' => $payload,
        ]);
    }

    /**
     * DELETE /api/admin/sections/{section}/teaching-assistants/{sectionTeachingAssistant}
     */
    public function destroy(Section $section, SectionTeachingAssistant $sectionTeachingAssistant): JsonResponse
    {
        if ((int) $sectionTeachingAssistant->section_id !== (int) $section->id) {
            return response()->json(['message' => 'Teaching assistant assignment not found.'], 404);
        }

        $sectionTeachingAssistant->delete();

        return response()->json(['message' => 'Teaching assistant removed successfully.']);
    }
}
