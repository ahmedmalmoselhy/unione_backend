<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreAnnouncementRequest;
use App\Http\Requests\Dashboard\UpdateAnnouncementRequest;
use App\Models\Announcement;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Section;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::with('author')
            ->withCount('reads')
            ->latest()
            ->paginate(15);

        return view('dashboard.announcements.index', compact('announcements'));
    }

    public function show(Announcement $announcement)
    {
        $announcement->load(['author', 'reads.user']);

        return view('dashboard.announcements.show', compact('announcement'));
    }

    public function create()
    {
        return view('dashboard.announcements.create', $this->formData());
    }

    public function store(StoreAnnouncementRequest $request)
    {
        $data = $request->validated();
        $data['author_id'] = auth()->id();

        if ($data['visibility'] === 'university') {
            $data['target_id'] = null;
        }

        Announcement::create($data);

        return redirect()
            ->route('dashboard.announcements.index')
            ->with('success', 'Announcement created successfully.');
    }

    public function edit(Announcement $announcement)
    {
        return view('dashboard.announcements.edit', array_merge(
            ['announcement' => $announcement],
            $this->formData(),
        ));
    }

    public function update(UpdateAnnouncementRequest $request, Announcement $announcement)
    {
        $data = $request->validated();

        if ($data['visibility'] === 'university') {
            $data['target_id'] = null;
        }

        $announcement->update($data);

        return redirect()
            ->route('dashboard.announcements.index')
            ->with('success', 'Announcement updated successfully.');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return redirect()
            ->route('dashboard.announcements.index')
            ->with('success', 'Announcement deleted successfully.');
    }

    private function formData(): array
    {
        $faculties   = Faculty::where('is_active', true)->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $sections    = Section::with(['course', 'academicTerm'])
            ->where('is_active', true)
            ->get()
            ->sortBy(fn ($s) => $s->course->code);

        return compact('faculties', 'departments', 'sections');
    }
}
