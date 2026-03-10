<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\Announcement;
use App\Models\AnnouncementRead;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $user      = $request->user();
        $student   = $user->student()->with(['faculty', 'department'])->first();
        $professor = $user->professor()->with(['department.faculty'])->first();
        $employee  = $user->employee()->with(['department.faculty'])->first();

        // Unread notifications count
        $unreadNotifications = $user->unreadNotifications()->count();

        // Recent announcements (visible to this user)
        $facultyId    = $student?->faculty_id ?? $professor?->department?->faculty_id ?? $employee?->department?->faculty_id;
        $departmentId = $student?->department_id ?? $professor?->department_id ?? $employee?->department_id;
        $sectionIds   = $student
            ? $student->enrollments()->whereIn('status', ['registered', 'completed'])->pluck('section_id')
            : collect();

        $announcements = Announcement::query()
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
            ->limit(5)
            ->get();

        $readIds = AnnouncementRead::where('user_id', $user->id)
            ->whereIn('announcement_id', $announcements->pluck('id'))
            ->pluck('announcement_id')
            ->flip();

        $announcements = $announcements->map(fn ($a) => array_merge($a->toArray(), [
            'is_read' => $readIds->has($a->id),
        ]));

        // Role-specific data
        $roleData = [];

        if ($student) {
            $currentTerm = AcademicTerm::where('is_active', true)->latest('academic_year')->first();

            $enrollments = $student->enrollments()
                ->with(['section.course', 'section.academicTerm', 'grade'])
                ->when($currentTerm, fn ($q) => $q->where('academic_term_id', $currentTerm->id))
                ->whereIn('status', ['registered', 'completed'])
                ->get();

            $roleData = [
                'student'      => $student,
                'enrollments'  => $enrollments,
                'current_term' => $currentTerm,
            ];
        } elseif ($professor) {
            $currentTerm = AcademicTerm::where('is_active', true)->latest('academic_year')->first();

            $sections = $professor->sections()
                ->with(['course', 'academicTerm'])
                ->withCount('enrollments')
                ->when($currentTerm, fn ($q) => $q->where('academic_term_id', $currentTerm->id))
                ->where('is_active', true)
                ->get();

            $roleData = [
                'professor'    => $professor,
                'sections'     => $sections,
                'current_term' => $currentTerm,
            ];
        } elseif ($employee) {
            $colleagues = \App\Models\Employee::with('user')
                ->where('department_id', $employee->department_id)
                ->where('id', '!=', $employee->id)
                ->whereNull('terminated_at')
                ->limit(8)
                ->get();

            $roleData = [
                'employee'   => $employee,
                'colleagues' => $colleagues,
            ];
        }

        return view('portal.home', array_merge([
            'user'                => $user,
            'student'             => $student,
            'professor'           => $professor,
            'employee'            => $employee,
            'announcements'       => $announcements,
            'unreadNotifications' => $unreadNotifications,
        ], $roleData));
    }
}
