<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AnnouncementRead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnnouncementController extends Controller
{
    /**
     * GET /api/announcements
     *
     * Returns published, non-expired announcements visible to the authenticated user.
     *
     * Visibility rules:
     *   university  → everyone
     *   faculty     → students in that faculty; professors/employees in departments of that faculty
     *   department  → students in that department; professors/employees in that department
     *   section     → students enrolled in that section
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Resolve the user's context IDs for scoping
        $student   = $user->student()->first();
        $professor = $user->professor()->first();
        $employee  = $user->employee()->first();

        $facultyId    = $student?->faculty_id ?? $professor?->department?->faculty_id ?? $employee?->department?->faculty_id;
        $departmentId = $student?->department_id ?? $professor?->department_id ?? $employee?->department_id;

        // Section IDs the student is enrolled in
        $sectionIds = $student
            ? $student->enrollments()->pluck('section_id')
            : collect();

        $announcements = Announcement::query()
            ->with('author:id,first_name,last_name')
            ->where('published_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->where(function ($q) use ($facultyId, $departmentId, $sectionIds) {
                $q->where('visibility', 'university');

                if ($facultyId) {
                    $q->orWhere(function ($q) use ($facultyId) {
                        $q->where('visibility', 'faculty')->where('target_id', $facultyId);
                    });
                }

                if ($departmentId) {
                    $q->orWhere(function ($q) use ($departmentId) {
                        $q->where('visibility', 'department')->where('target_id', $departmentId);
                    });
                }

                if ($sectionIds->isNotEmpty()) {
                    $q->orWhere(function ($q) use ($sectionIds) {
                        $q->where('visibility', 'section')->whereIn('target_id', $sectionIds);
                    });
                }
            })
            ->orderByDesc('published_at')
            ->paginate(20);

        // Enrich: mark which announcements the user has already read
        $readIds = AnnouncementRead::where('user_id', $user->id)
            ->whereIn('announcement_id', $announcements->pluck('id'))
            ->pluck('announcement_id')
            ->flip();

        $items = $announcements->getCollection()->map(function ($ann) use ($readIds) {
            return [
                'id'          => $ann->id,
                'title'       => $ann->title,
                'body'        => $ann->body,
                'type'        => $ann->type,
                'visibility'  => $ann->visibility,
                'published_at' => $ann->published_at?->toDateTimeString(),
                'expires_at'  => $ann->expires_at?->toDateTimeString(),
                'is_read'     => $readIds->has($ann->id),
                'author'      => $ann->author ? [
                    'first_name' => $ann->author->first_name,
                    'last_name'  => $ann->author->last_name,
                ] : null,
            ];
        });

        return response()->json([
            'data'  => $items,
            'meta'  => [
                'current_page' => $announcements->currentPage(),
                'last_page'    => $announcements->lastPage(),
                'per_page'     => $announcements->perPage(),
                'total'        => $announcements->total(),
            ],
        ]);
    }

    /**
     * POST /api/announcements/{id}/read
     * Mark an announcement as read for the authenticated user.
     */
    public function markRead(Request $request, int $id): JsonResponse
    {
        $announcement = Announcement::where('published_at', '<=', now())
            ->findOrFail($id);

        AnnouncementRead::firstOrCreate([
            'announcement_id' => $announcement->id,
            'user_id'         => $request->user()->id,
        ], [
            'read_at' => now(),
        ]);

        return response()->json(['message' => 'Marked as read.']);
    }
}
