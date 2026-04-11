<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Notifications\ExamSchedulePublished;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SectionExamScheduleController extends Controller
{
    /**
     * GET /api/admin/sections/{section}/exam-schedule
     */
    public function show(Section $section): JsonResponse
    {
        $examSchedule = $section->examSchedule;

        if (! $examSchedule) {
            return response()->json(['message' => 'Exam schedule not found.'], 404);
        }

        return response()->json(['exam_schedule' => $this->format($examSchedule)]);
    }

    /**
     * POST /api/admin/sections/{section}/exam-schedule
     */
    public function store(Request $request, Section $section): JsonResponse
    {
        if ($section->examSchedule()->exists()) {
            return response()->json(['message' => 'Exam schedule already exists for this section.'], 409);
        }

        $data = $request->validate([
            'exam_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'location' => ['nullable', 'string', 'max:255'],
        ]);

        $examSchedule = $section->examSchedule()->create([
            'exam_date' => $data['exam_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'location' => $data['location'] ?? null,
            'is_published' => false,
            'published_at' => null,
        ]);

        return response()->json([
            'message' => 'Exam schedule created successfully.',
            'exam_schedule' => $this->format($examSchedule->fresh()),
        ], 201);
    }

    /**
     * PATCH /api/admin/sections/{section}/exam-schedule
     */
    public function update(Request $request, Section $section): JsonResponse
    {
        $examSchedule = $section->examSchedule;

        if (! $examSchedule) {
            return response()->json(['message' => 'Exam schedule not found.'], 404);
        }

        $data = $request->validate([
            'exam_date' => ['sometimes', 'date'],
            'start_time' => ['sometimes', 'date_format:H:i'],
            'end_time' => ['sometimes', 'date_format:H:i'],
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        if ($data !== []) {
            $examSchedule->fill($data);

            if ($examSchedule->is_published) {
                $examSchedule->is_published = false;
                $examSchedule->published_at = null;
            }

            $examSchedule->save();
        }

        return response()->json([
            'message' => 'Exam schedule updated successfully.',
            'exam_schedule' => $this->format($examSchedule->fresh()),
        ]);
    }

    /**
     * POST /api/admin/sections/{section}/exam-schedule/publish
     */
    public function publish(Section $section): JsonResponse
    {
        $examSchedule = $section->examSchedule;

        if (! $examSchedule) {
            return response()->json(['message' => 'Exam schedule not found.'], 404);
        }

        $examSchedule->forceFill([
            'is_published' => true,
            'published_at' => now(),
        ])->save();

        $examSchedule->load('section.course');

        $section->enrollments()
            ->whereIn('status', ['registered', 'completed'])
            ->with('student.user')
            ->get()
            ->each(function ($enrollment) use ($examSchedule) {
                $enrollment->student?->user?->notify(new ExamSchedulePublished($examSchedule));
            });

        return response()->json([
            'message' => 'Exam schedule published successfully.',
            'exam_schedule' => $this->format($examSchedule->fresh()),
        ]);
    }

    private function format($examSchedule): array
    {
        return [
            'id' => $examSchedule->id,
            'section_id' => $examSchedule->section_id,
            'exam_date' => optional($examSchedule->exam_date)->toDateString(),
            'start_time' => $examSchedule->start_time,
            'end_time' => $examSchedule->end_time,
            'location' => $examSchedule->location,
            'is_published' => (bool) $examSchedule->is_published,
            'published_at' => $examSchedule->published_at?->toDateTimeString(),
            'created_at' => $examSchedule->created_at?->toDateTimeString(),
            'updated_at' => $examSchedule->updated_at?->toDateTimeString(),
        ];
    }
}
