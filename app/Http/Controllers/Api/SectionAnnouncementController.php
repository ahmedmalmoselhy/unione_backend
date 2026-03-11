<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\SectionAnnouncement;
use App\Notifications\SectionAnnouncementPosted;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SectionAnnouncementController extends Controller
{
    /**
     * GET /api/professor/sections/{section}/announcements
     * List all announcements for the section (professor view).
     */
    public function index(Request $request, Section $section): JsonResponse
    {
        $professor = $request->user()->professor;

        if (! $professor || (int) $section->professor_id !== $professor->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $announcements = $section->sectionAnnouncements()
            ->with('author')
            ->latest()
            ->get()
            ->map(fn ($a) => $this->format($a));

        return response()->json(['announcements' => $announcements]);
    }

    /**
     * POST /api/professor/sections/{section}/announcements
     * Professor posts an announcement to the section; notifies all enrolled students.
     */
    public function store(Request $request, Section $section): JsonResponse
    {
        $professor = $request->user()->professor;

        if (! $professor || (int) $section->professor_id !== $professor->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body'  => ['required', 'string'],
        ]);

        $announcement = SectionAnnouncement::create([
            'section_id'   => $section->id,
            'author_id'    => $request->user()->id,
            'title'        => $data['title'],
            'body'         => $data['body'],
            'published_at' => now(),
        ]);

        $announcement->load('section.course');

        // Notify all enrolled students
        $section->enrollments()
            ->whereIn('status', ['registered', 'completed'])
            ->with('student.user')
            ->get()
            ->each(function ($enrollment) use ($announcement) {
                $enrollment->student?->user?->notify(new SectionAnnouncementPosted($announcement));
            });

        return response()->json(['announcement' => $this->format($announcement)], 201);
    }

    /**
     * DELETE /api/professor/sections/{section}/announcements/{announcement}
     * Professor deletes their own announcement.
     */
    public function destroy(Request $request, Section $section, SectionAnnouncement $announcement): JsonResponse
    {
        $professor = $request->user()->professor;

        if (! $professor || (int) $section->professor_id !== $professor->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ((int) $announcement->section_id !== $section->id) {
            return response()->json(['message' => 'Announcement does not belong to this section.'], 422);
        }

        $announcement->delete();

        return response()->json(['message' => 'Announcement deleted.']);
    }

    /**
     * GET /api/student/sections/{section}/announcements
     * Student views announcements for a section they are enrolled in.
     */
    public function studentIndex(Request $request, Section $section): JsonResponse
    {
        $student = $request->user()->student()->firstOrFail();

        $enrolled = $section->enrollments()
            ->where('student_id', $student->id)
            ->whereIn('status', ['registered', 'completed'])
            ->exists();

        if (! $enrolled) {
            return response()->json(['message' => 'You are not enrolled in this section.'], 403);
        }

        $announcements = $section->sectionAnnouncements()
            ->whereNotNull('published_at')
            ->latest('published_at')
            ->get()
            ->map(fn ($a) => $this->format($a));

        return response()->json(['announcements' => $announcements]);
    }

    private function format(SectionAnnouncement $a): array
    {
        return [
            'id'           => $a->id,
            'title'        => $a->title,
            'body'         => $a->body,
            'published_at' => $a->published_at?->toDateTimeString(),
            'author'       => $a->author ? $a->author->first_name . ' ' . $a->author->last_name : null,
        ];
    }
}
