<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AnnouncementRead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $student   = $user->student()->first();
        $professor = $user->professor()->with('department')->first();
        $employee  = $user->employee()->with('department')->first();

        $facultyId    = $student?->faculty_id ?? $professor?->department?->faculty_id ?? $employee?->department?->faculty_id;
        $departmentId = $student?->department_id ?? $professor?->department_id ?? $employee?->department_id;
        $sectionIds   = $student
            ? $student->enrollments()->whereIn('status', ['registered', 'completed'])->pluck('section_id')
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
                    $q->orWhere(fn ($q) => $q->where('visibility', 'faculty')->where('target_id', $facultyId));
                }
                if ($departmentId) {
                    $q->orWhere(fn ($q) => $q->where('visibility', 'department')->where('target_id', $departmentId));
                }
                if ($sectionIds->isNotEmpty()) {
                    $q->orWhere(fn ($q) => $q->where('visibility', 'section')->whereIn('target_id', $sectionIds));
                }
            })
            ->orderByDesc('published_at')
            ->paginate(20);

        $readIds = AnnouncementRead::where('user_id', $user->id)
            ->whereIn('announcement_id', $announcements->pluck('id'))
            ->pluck('announcement_id')
            ->flip();

        return view('portal.announcements.index', compact('announcements', 'readIds'));
    }

    public function markRead(Request $request, int $id): RedirectResponse
    {
        AnnouncementRead::firstOrCreate([
            'user_id'         => $request->user()->id,
            'announcement_id' => $id,
        ]);

        return back();
    }
}
