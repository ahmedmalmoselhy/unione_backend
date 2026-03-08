<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreAnnouncementRequest;
use App\Http\Requests\Dashboard\UpdateAnnouncementRequest;
use App\Models\Announcement;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Section;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    use Concerns\DashboardScopeAware;

    public function index(Request $request)
    {
        $announcements = Announcement::with('author')
            ->withCount('reads')
            ->when($this->scopedFacultyId(), fn ($q, $id) => $q->where(function ($q) use ($id) {
                $q->where('visibility', 'university')
                  ->orWhere(fn ($q) => $q->where('visibility', 'faculty')->where('target_id', $id));
            }))
            ->when($this->scopedDepartmentId(), fn ($q, $id) => $q->where(function ($q) use ($id) {
                $q->where('visibility', 'university')
                  ->orWhere(fn ($q) => $q->where('visibility', 'department')->where('target_id', $id));
            }))
            ->when($request->filled('search'), fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('title', 'ilike', '%' . $request->search . '%')
                  ->orWhere('body', 'ilike', '%' . $request->search . '%');
            }))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->when($request->filled('visibility'), fn ($q) => $q->where('visibility', $request->visibility))
            ->when($request->filled('pub_status'), function ($q) use ($request) {
                if ($request->pub_status === 'draft') {
                    $q->whereNull('published_at');
                } elseif ($request->pub_status === 'published') {
                    $q->whereNotNull('published_at')->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
                } elseif ($request->pub_status === 'expired') {
                    $q->whereNotNull('published_at')->where('expires_at', '<=', now());
                }
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

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
