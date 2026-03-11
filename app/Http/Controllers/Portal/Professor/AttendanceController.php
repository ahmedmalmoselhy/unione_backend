<?php

namespace App\Http\Controllers\Portal\Professor;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Section;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
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

        $sessions = $section->attendanceSessions()
            ->withCount([
                'records as present_count' => fn ($q) => $q->where('status', 'present'),
                'records as absent_count'  => fn ($q) => $q->where('status', 'absent'),
                'records as late_count'    => fn ($q) => $q->where('status', 'late'),
                'records as total_count',
            ])
            ->orderByDesc('session_date')
            ->get();

        $enrolledStudents = $section->enrollments()
            ->whereIn('status', ['registered', 'completed'])
            ->with('student.user')
            ->get()
            ->map(fn ($e) => $e->student)
            ->filter();

        return view('portal.professor.sections.attendance', compact('section', 'sessions', 'enrolledStudents'));
    }

    public function store(Request $request, Section $section): RedirectResponse
    {
        $this->authorizeSection($request, $section);

        $data = $request->validate([
            'session_date'          => ['required', 'date'],
            'topic'                 => ['nullable', 'string', 'max:255'],
            'records'               => ['required', 'array', 'min:1'],
            'records.*.student_id'  => ['required', 'integer', 'exists:students,id'],
            'records.*.status'      => ['required', 'in:present,absent,late,excused'],
            'records.*.note'        => ['nullable', 'string', 'max:255'],
        ]);

        $enrolledIds = $section->enrollments()
            ->whereIn('status', ['registered', 'completed'])
            ->pluck('student_id')
            ->all();

        $session = AttendanceSession::create([
            'section_id'   => $section->id,
            'created_by'   => $request->user()->id,
            'session_date' => $data['session_date'],
            'topic'        => $data['topic'] ?? null,
        ]);

        foreach ($data['records'] as $rec) {
            if (in_array($rec['student_id'], $enrolledIds)) {
                AttendanceRecord::create([
                    'attendance_session_id' => $session->id,
                    'student_id'            => $rec['student_id'],
                    'status'                => $rec['status'],
                    'note'                  => $rec['note'] ?? null,
                ]);
            }
        }

        return redirect()->route('portal.attendance.show', [$section, $session])
            ->with('success', 'Attendance session recorded.');
    }

    public function show(Request $request, Section $section, AttendanceSession $session): View
    {
        $this->authorizeSection($request, $section);

        if ((int) $session->section_id !== $section->id) {
            abort(404);
        }

        $section->load(['course', 'academicTerm']);
        $records = $session->records()->with('student.user')->orderBy('student_id')->get();

        return view('portal.professor.sections.attendance-show', compact('section', 'session', 'records'));
    }

    public function update(Request $request, Section $section, AttendanceSession $session): RedirectResponse
    {
        $this->authorizeSection($request, $section);

        if ((int) $session->section_id !== $section->id) {
            abort(404);
        }

        $data = $request->validate([
            'records'               => ['required', 'array'],
            'records.*.record_id'   => ['required', 'integer', 'exists:attendance_records,id'],
            'records.*.status'      => ['required', 'in:present,absent,late,excused'],
            'records.*.note'        => ['nullable', 'string', 'max:255'],
        ]);

        foreach ($data['records'] as $rec) {
            AttendanceRecord::where('id', $rec['record_id'])
                ->where('attendance_session_id', $session->id)
                ->update(['status' => $rec['status'], 'note' => $rec['note'] ?? null]);
        }

        return back()->with('success', 'Attendance updated.');
    }
}
