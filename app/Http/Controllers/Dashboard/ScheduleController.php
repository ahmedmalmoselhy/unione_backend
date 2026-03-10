<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    use Concerns\DashboardScopeAware;

    private const DAY_ORDER = [
        'saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday',
    ];

    public function index(Request $request): View
    {
        $locale = app()->getLocale();

        // Faculties visible to this admin
        $faculties = Faculty::query()
            ->when($this->scopedFacultyId(), fn ($q, $id) => $q->where('id', $id))
            ->orderBy('name')
            ->get(['id', 'name', 'name_ar'])
            ->map(fn ($f) => [
                'id'   => $f->id,
                'name' => $locale === 'ar' ? ($f->name_ar ?: $f->name) : $f->name,
            ]);

        // All departments this admin can see (used for client-side cascade)
        $allDepartments = Department::query()
            ->when($this->scopedFacultyId(), fn ($q, $id) => $q->where('faculty_id', $id))
            ->when($this->scopedDepartmentId(), fn ($q, $id) => $q->where('id', $id))
            ->orderBy('name')
            ->get(['id', 'faculty_id', 'name', 'name_ar'])
            ->map(fn ($d) => [
                'id'         => $d->id,
                'faculty_id' => $d->faculty_id,
                'name'       => $locale === 'ar' ? ($d->name_ar ?: $d->name) : $d->name,
            ]);

        // Academic terms (newest first)
        $terms = AcademicTerm::orderByDesc('academic_year')
            ->orderByDesc('id')
            ->get(['id', 'name', 'name_ar', 'is_active'])
            ->map(fn ($t) => [
                'id'        => $t->id,
                'name'      => ($locale === 'ar' ? ($t->name_ar ?: $t->name) : $t->name)
                               . ($t->is_active ? ' ★' : ''),
                'is_active' => $t->is_active,
            ]);

        $defaultTermId = collect($terms)->where('is_active', true)->first()['id'] ?? null;

        $levels = [1, 2, 3, 4];

        // --- Build the grid only when a department is selected ---
        $grid         = [];
        $orderedDays  = [];
        $timeSlots    = [];
        $sectionCount = 0;
        $hasQueried   = $request->filled('department_id');

        if ($hasQueried) {
            $departmentId = (int) $request->department_id;
            $termId       = $request->filled('term_id') ? (int) $request->term_id : $defaultTermId;
            $level        = $request->filled('level') ? (int) $request->level : null;

            // Authorize scope
            if ($this->scopedDepartmentId() && $this->scopedDepartmentId() !== $departmentId) {
                abort(403);
            }
            if ($this->scopedFacultyId()) {
                $dept = Department::find($departmentId);
                if (! $dept || $dept->faculty_id !== $this->scopedFacultyId()) {
                    abort(403);
                }
            }

            $sections = Section::with(['course', 'professor.user'])
                ->where('is_active', true)
                ->whereNotNull('schedule')
                ->when($termId, fn ($q) => $q->where('academic_term_id', $termId))
                ->whereHas('course', function ($q) use ($departmentId, $level) {
                    $q->whereHas('departments', fn ($d) => $d->where('departments.id', $departmentId));
                    if ($level) {
                        $q->where('level', $level);
                    }
                })
                ->get();

            $sectionCount  = $sections->count();
            $daysPresent   = [];
            $timeSlotsMap  = []; // timeKey => start_time (for sorting)

            foreach ($sections as $section) {
                $slots = is_array($section->schedule) ? $section->schedule : [];

                foreach ($slots as $slot) {
                    $day     = strtolower($slot['day'] ?? '');
                    $start   = $slot['start_time'] ?? '';
                    $end     = $slot['end_time'] ?? '';
                    $type    = $slot['type'] ?? 'lecture';
                    $timeKey = $start . '–' . $end;

                    if (! $day || ! $start || ! $end) {
                        continue;
                    }

                    $grid[$day][$timeKey][] = [
                        'section' => $section,
                        'type'    => $type,
                    ];

                    $daysPresent[$day]      = true;
                    $timeSlotsMap[$timeKey] = $start;
                }
            }

            // Sort time slots chronologically
            asort($timeSlotsMap);
            $timeSlots = array_keys($timeSlotsMap);

            // Order days by standard week order
            $orderedDays = array_values(
                array_filter(self::DAY_ORDER, fn ($d) => isset($daysPresent[$d]))
            );
        }

        return view('dashboard.schedule.index', compact(
            'faculties', 'allDepartments', 'terms', 'levels',
            'grid', 'orderedDays', 'timeSlots', 'sectionCount',
            'hasQueried', 'defaultTermId'
        ));
    }
}
