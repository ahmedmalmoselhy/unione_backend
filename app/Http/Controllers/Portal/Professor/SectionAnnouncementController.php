<?php

namespace App\Http\Controllers\Portal\Professor;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\SectionAnnouncement;
use App\Notifications\SectionAnnouncementPosted;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SectionAnnouncementController extends Controller
{
    private function authorizeSection(Request $request, Section $section): void
    {
        $professor = $request->user()->professor()->firstOrFail();
        if ((int) $section->professor_id !== $professor->id) {
            abort(403);
        }
    }

    public function index(Request $request, Section $section): View
    {
        $this->authorizeSection($request, $section);

        $section->load(['course', 'academicTerm']);

        $announcements = $section->sectionAnnouncements()
            ->with('author')
            ->latest()
            ->get();

        return view('portal.professor.sections.announcements', compact('section', 'announcements'));
    }

    public function store(Request $request, Section $section): RedirectResponse
    {
        $this->authorizeSection($request, $section);

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

        $section->enrollments()
            ->whereIn('status', ['registered', 'completed'])
            ->with('student.user')
            ->get()
            ->each(function ($enrollment) use ($announcement) {
                $enrollment->student?->user?->notify(new SectionAnnouncementPosted($announcement));
            });

        return back()->with('success', 'Announcement posted.');
    }

    public function destroy(Request $request, Section $section, SectionAnnouncement $announcement): RedirectResponse
    {
        $this->authorizeSection($request, $section);

        if ((int) $announcement->section_id !== $section->id) {
            abort(404);
        }

        $announcement->delete();

        return back()->with('success', 'Announcement deleted.');
    }
}
