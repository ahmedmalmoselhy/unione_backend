<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $user      = $request->user();
        $student   = $user->student()->first();
        $professor = $user->professor()->first();
        $employee  = $user->employee()->first();

        $currentTerm = AcademicTerm::where('is_active', true)->latest('academic_year')->first();

        $scheduleEntries = collect();

        if ($student) {
            $enrollments = $student->enrollments()
                ->with(['section.course', 'section.professor.user', 'section.academicTerm'])
                ->when($currentTerm, fn ($q) => $q->where('academic_term_id', $currentTerm->id))
                ->whereIn('status', ['registered', 'completed'])
                ->get();

            // If no enrollments found in the active term, fall back to all active enrollments
            if ($enrollments->isEmpty()) {
                $enrollments = $student->enrollments()
                    ->with(['section.course', 'section.professor.user', 'section.academicTerm'])
                    ->whereIn('status', ['registered', 'completed'])
                    ->get();
            }

            $scheduleEntries = $enrollments->flatMap(function ($enrollment) {
                $section  = $enrollment->section;
                $schedule = $section?->schedule ?? [];

                if (empty($schedule)) {
                    // Include the course with no time slots so it still appears
                    return collect([[
                        'day'         => 'Unscheduled',
                        'start_time'  => '—',
                        'end_time'    => '—',
                        'course_name' => $section?->course?->name ?? '',
                        'course_code' => $section?->course?->code ?? '',
                        'room'        => $section?->room ?? '',
                        'professor'   => $section?->professor?->user
                            ? $section->professor->user->first_name . ' ' . $section->professor->user->last_name
                            : '',
                        'type'        => 'lecture',
                    ]]);
                }

                return collect($schedule)->map(fn ($slot) => [
                    'day'         => ucfirst(strtolower($slot['day'] ?? '')),
                    'start_time'  => $slot['start_time'] ?? '',
                    'end_time'    => $slot['end_time'] ?? '',
                    'course_name' => $section->course?->name ?? '',
                    'course_code' => $section->course?->code ?? '',
                    'room'        => $section->room ?? '',
                    'professor'   => $section->professor?->user
                        ? $section->professor->user->first_name . ' ' . $section->professor->user->last_name
                        : '',
                    'type'        => $slot['type'] ?? 'lecture',
                ]);
            });
        } elseif ($professor) {
            $sections = $professor->sections()
                ->with(['course', 'academicTerm'])
                ->when($currentTerm, fn ($q) => $q->where('academic_term_id', $currentTerm->id))
                ->where('is_active', true)
                ->get();

            // Fall back to all active sections if none matched the current term
            if ($sections->isEmpty()) {
                $sections = $professor->sections()
                    ->with(['course', 'academicTerm'])
                    ->where('is_active', true)
                    ->get();
            }

            $scheduleEntries = $sections->flatMap(function ($section) {
                $schedule = $section->schedule ?? [];

                if (empty($schedule)) {
                    return collect([[
                        'day'         => 'Unscheduled',
                        'start_time'  => '—',
                        'end_time'    => '—',
                        'course_name' => $section->course?->name ?? '',
                        'course_code' => $section->course?->code ?? '',
                        'room'        => $section->room ?? '',
                        'professor'   => '',
                        'type'        => 'teaching',
                    ]]);
                }

                return collect($schedule)->map(fn ($slot) => [
                    'day'         => ucfirst(strtolower($slot['day'] ?? '')),
                    'start_time'  => $slot['start_time'] ?? '',
                    'end_time'    => $slot['end_time'] ?? '',
                    'course_name' => $section->course?->name ?? '',
                    'course_code' => $section->course?->code ?? '',
                    'room'        => $section->room ?? '',
                    'professor'   => '',
                    'type'        => $slot['type'] ?? 'teaching',
                ]);
            });
        }

        $days  = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $byDay = $scheduleEntries->groupBy('day');

        return view('portal.schedule', compact('byDay', 'days', 'currentTerm', 'student', 'professor', 'employee'));
    }
}
