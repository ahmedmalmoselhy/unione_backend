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
        $query = Announcement::with('author')->withCount('reads');

        if ($this->isFacultyAdmin()) {
            $facultyId  = $this->scopedFacultyId();
            $deptIds    = Department::where('faculty_id', $facultyId)->pluck('id');
            $sectionIds = Section::whereHas('course.departments', fn ($q) => $q->whereIn('departments.id', $deptIds))->pluck('id');

            $query->where(function ($q) use ($facultyId, $deptIds, $sectionIds) {
                $q->where('visibility', 'university')
                  ->orWhere(fn ($q) => $q->where('visibility', 'faculty')->where('target_id', $facultyId))
                  ->orWhere(fn ($q) => $q->where('visibility', 'department')->whereIn('target_id', $deptIds))
                  ->orWhere(fn ($q) => $q->where('visibility', 'section')->whereIn('target_id', $sectionIds));
            });
        } elseif ($this->isDepartmentAdmin()) {
            $deptId     = $this->scopedDepartmentId();
            $facultyId  = Department::where('id', $deptId)->value('faculty_id');
            $sectionIds = Section::whereHas('course.departments', fn ($q) => $q->where('departments.id', $deptId))->pluck('id');

            $query->where(function ($q) use ($facultyId, $deptId, $sectionIds) {
                $q->where('visibility', 'university')
                  ->when($facultyId, fn ($q) => $q->orWhere(fn ($q) => $q->where('visibility', 'faculty')->where('target_id', $facultyId)))
                  ->orWhere(fn ($q) => $q->where('visibility', 'department')->where('target_id', $deptId))
                  ->orWhere(fn ($q) => $q->where('visibility', 'section')->whereIn('target_id', $sectionIds));
            });
        }
        // System admin: no scope filter — sees everything

        $announcements = $query
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

        // Preload target names to avoid N+1 in the view
        $col        = $announcements->getCollection();
        $fIds       = $col->where('visibility', 'faculty')->pluck('target_id')->filter();
        $dIds       = $col->where('visibility', 'department')->pluck('target_id')->filter();
        $sIds       = $col->where('visibility', 'section')->pluck('target_id')->filter();
        $targetLabels = [
            'faculty'    => Faculty::whereIn('id', $fIds)->pluck('name', 'id'),
            'department' => Department::whereIn('id', $dIds)->pluck('name', 'id'),
            'section'    => $sIds->isNotEmpty()
                ? Section::with('course')->whereIn('id', $sIds)->get()
                    ->mapWithKeys(fn ($s) => [$s->id => ($s->course->code ?? '') . ' — ' . ($s->course->name ?? '')])
                : collect(),
        ];

        return view('dashboard.announcements.index', compact('announcements', 'targetLabels'));
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

        $this->enforceTargetScope($data);

        Announcement::create($data);

        return redirect()
            ->route('dashboard.announcements.index')
            ->with('success', 'Announcement created successfully.');
    }

    public function edit(Announcement $announcement)
    {
        abort_unless($this->canManageAnnouncement($announcement), 403);

        return view('dashboard.announcements.edit', array_merge(
            ['announcement' => $announcement],
            $this->formData(),
        ));
    }

    public function update(UpdateAnnouncementRequest $request, Announcement $announcement)
    {
        abort_unless($this->canManageAnnouncement($announcement), 403);

        $data = $request->validated();
        $this->enforceTargetScope($data);

        $announcement->update($data);

        return redirect()
            ->route('dashboard.announcements.index')
            ->with('success', 'Announcement updated successfully.');
    }

    public function destroy(Announcement $announcement)
    {
        abort_unless($this->canManageAnnouncement($announcement), 403);

        $announcement->delete();

        return redirect()
            ->route('dashboard.announcements.index')
            ->with('success', 'Announcement deleted successfully.');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function allowedVisibilities(): array
    {
        if ($this->isSystemAdmin()) {
            return [
                'university' => 'University-wide',
                'faculty'    => 'Faculty',
                'department' => 'Department',
                'section'    => 'Section',
            ];
        }
        if ($this->isFacultyAdmin()) {
            return [
                'faculty'    => 'Faculty',
                'department' => 'Department',
                'section'    => 'Section',
            ];
        }
        // Department admin
        return [
            'department' => 'Department',
            'section'    => 'Section',
        ];
    }

    private function canManageAnnouncement(Announcement $announcement): bool
    {
        if ($this->isSystemAdmin()) return true;
        if ($announcement->author_id === auth()->id()) return true;

        if ($this->isFacultyAdmin()) {
            $facultyId = $this->scopedFacultyId();
            return match ($announcement->visibility) {
                'faculty'    => (int) $announcement->target_id === $facultyId,
                'department' => Department::where('id', $announcement->target_id)
                                    ->where('faculty_id', $facultyId)->exists(),
                default      => false,
            };
        }

        if ($this->isDepartmentAdmin()) {
            $deptId = $this->scopedDepartmentId();
            return $announcement->visibility === 'department'
                && (int) $announcement->target_id === $deptId;
        }

        return false;
    }

    /**
     * Server-side enforce target_id based on the admin's scope.
     * Mutates $data in place.
     */
    private function enforceTargetScope(array &$data): void
    {
        if ($data['visibility'] === 'university') {
            $data['target_id'] = null;
            return;
        }

        if ($this->isFacultyAdmin() && $data['visibility'] === 'faculty') {
            $data['target_id'] = $this->scopedFacultyId();
        } elseif ($this->isDepartmentAdmin() && $data['visibility'] === 'department') {
            $data['target_id'] = $this->scopedDepartmentId();
        }
    }

    private function formData(): array
    {
        if ($this->isFacultyAdmin()) {
            $facultyId   = $this->scopedFacultyId();
            $faculties   = Faculty::where('id', $facultyId)->get();
            $deptIds     = Department::where('faculty_id', $facultyId)->where('is_active', true)->pluck('id');
            $departments = Department::whereIn('id', $deptIds)->orderBy('name')->get();
            $sections    = Section::with(['course', 'academicTerm'])
                ->where('is_active', true)
                ->whereHas('course.departments', fn ($q) => $q->whereIn('departments.id', $deptIds))
                ->get()
                ->sortBy(fn ($s) => $s->course->code);
        } elseif ($this->isDepartmentAdmin()) {
            $deptId      = $this->scopedDepartmentId();
            $faculties   = collect();
            $departments = Department::where('id', $deptId)->get();
            $sections    = Section::with(['course', 'academicTerm'])
                ->where('is_active', true)
                ->whereHas('course.departments', fn ($q) => $q->where('departments.id', $deptId))
                ->get()
                ->sortBy(fn ($s) => $s->course->code);
        } else {
            // System admin — full access
            $faculties   = Faculty::where('is_active', true)->orderBy('name')->get();
            $departments = Department::where('is_active', true)->orderBy('name')->get();
            $sections    = Section::with(['course', 'academicTerm'])
                ->where('is_active', true)
                ->get()
                ->sortBy(fn ($s) => $s->course->code);
        }

        return [
            'faculties'           => $faculties,
            'departments'         => $departments,
            'sections'            => $sections,
            'allowedVisibilities' => $this->allowedVisibilities(),
        ];
    }
}
