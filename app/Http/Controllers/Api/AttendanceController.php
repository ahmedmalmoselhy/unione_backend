<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Section;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * GET /api/professor/sections/{section}/attendance
     * List all attendance sessions for a section (professor only).
     */
    public function index(Request $request, Section $section): JsonResponse
    {
        $professor = $request->user()->professor;

        if (! $professor || (int) $section->professor_id !== $professor->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $sessions = $section->attendanceSessions()
            ->withCount([
                'records as present_count' => fn ($q) => $q->where('status', 'present'),
                'records as absent_count'  => fn ($q) => $q->where('status', 'absent'),
                'records as late_count'    => fn ($q) => $q->where('status', 'late'),
                'records as total_count',
            ])
            ->orderByDesc('session_date')
            ->get()
            ->map(fn ($s) => [
                'id'            => $s->id,
                'session_date'  => $s->session_date->toDateString(),
                'topic'         => $s->topic,
                'present_count' => $s->present_count,
                'absent_count'  => $s->absent_count,
                'late_count'    => $s->late_count,
                'total_count'   => $s->total_count,
            ]);

        return response()->json(['sessions' => $sessions]);
    }

    /**
     * POST /api/professor/sections/{section}/attendance
     * Create a new attendance session and bulk-record student statuses.
     *
     * Body: { session_date, topic?, records: [{ student_id, status, note? }] }
     */
    public function store(Request $request, Section $section): JsonResponse
    {
        $professor = $request->user()->professor;

        if (! $professor || (int) $section->professor_id !== $professor->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $data = $request->validate([
            'session_date'          => ['required', 'date'],
            'topic'                 => ['nullable', 'string', 'max:255'],
            'records'               => ['required', 'array', 'min:1'],
            'records.*.student_id'  => ['required', 'integer', 'exists:students,id'],
            'records.*.status'      => ['required', 'in:present,absent,late,excused'],
            'records.*.note'        => ['nullable', 'string', 'max:255'],
        ]);

        // Verify all student_ids are enrolled in this section
        $enrolledIds = $section->enrollments()
            ->whereIn('status', ['registered', 'completed'])
            ->pluck('student_id')
            ->all();

        foreach ($data['records'] as $rec) {
            if (! in_array($rec['student_id'], $enrolledIds)) {
                return response()->json([
                    'message' => "Student {$rec['student_id']} is not enrolled in this section.",
                ], 422);
            }
        }

        $session = AttendanceSession::create([
            'section_id'   => $section->id,
            'created_by'   => $request->user()->id,
            'session_date' => $data['session_date'],
            'topic'        => $data['topic'] ?? null,
        ]);

        foreach ($data['records'] as $rec) {
            AttendanceRecord::create([
                'attendance_session_id' => $session->id,
                'student_id'            => $rec['student_id'],
                'status'                => $rec['status'],
                'note'                  => $rec['note'] ?? null,
            ]);
        }

        return response()->json(['session' => ['id' => $session->id, 'session_date' => $session->session_date->toDateString()]], 201);
    }

    /**
     * GET /api/professor/sections/{section}/attendance/{session}
     * Show one session with all records.
     */
    public function show(Request $request, Section $section, AttendanceSession $session): JsonResponse
    {
        $professor = $request->user()->professor;

        if (! $professor || (int) $section->professor_id !== $professor->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ((int) $session->section_id !== $section->id) {
            return response()->json(['message' => 'Session does not belong to this section.'], 422);
        }

        $records = $session->records()->with('student.user')->get()->map(fn ($r) => [
            'student_id'     => $r->student_id,
            'student_number' => $r->student?->student_number,
            'name'           => $r->student?->user
                ? $r->student->user->first_name . ' ' . $r->student->user->last_name
                : null,
            'status' => $r->status,
            'note'   => $r->note,
        ]);

        return response()->json([
            'session' => [
                'id'           => $session->id,
                'session_date' => $session->session_date->toDateString(),
                'topic'        => $session->topic,
            ],
            'records' => $records,
        ]);
    }

    /**
     * PUT /api/professor/sections/{section}/attendance/{session}
     * Update the status of individual records in a session.
     *
     * Body: { records: [{ student_id, status, note? }] }
     */
    public function update(Request $request, Section $section, AttendanceSession $session): JsonResponse
    {
        $professor = $request->user()->professor;

        if (! $professor || (int) $section->professor_id !== $professor->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ((int) $session->section_id !== $section->id) {
            return response()->json(['message' => 'Session does not belong to this section.'], 422);
        }

        $data = $request->validate([
            'records'              => ['required', 'array', 'min:1'],
            'records.*.student_id' => ['required', 'integer', 'exists:students,id'],
            'records.*.status'     => ['required', 'in:present,absent,late,excused'],
            'records.*.note'       => ['nullable', 'string', 'max:255'],
        ]);

        foreach ($data['records'] as $rec) {
            AttendanceRecord::updateOrCreate(
                ['attendance_session_id' => $session->id, 'student_id' => $rec['student_id']],
                ['status' => $rec['status'], 'note' => $rec['note'] ?? null],
            );
        }

        return response()->json(['message' => 'Attendance updated.']);
    }

    /**
     * GET /api/student/attendance
     * Student views their own attendance across all enrolled sections.
     */
    public function studentAttendance(Request $request): JsonResponse
    {
        $student = $request->user()->student()->firstOrFail();

        $records = AttendanceRecord::where('student_id', $student->id)
            ->with(['session.section.course', 'session.section.academicTerm'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($r) => [
                'session_date'  => $r->session?->session_date?->toDateString(),
                'topic'         => $r->session?->topic,
                'status'        => $r->status,
                'note'          => $r->note,
                'course' => $r->session?->section?->course ? [
                    'code' => $r->session->section->course->code,
                    'name' => $r->session->section->course->name,
                ] : null,
                'academic_term' => $r->session?->section?->academicTerm ? [
                    'id'   => $r->session->section->academicTerm->id,
                    'name' => $r->session->section->academicTerm->name,
                ] : null,
            ]);

        // Summary per section
        $summary = $records->groupBy(fn ($r) => $r['course']['code'] ?? 'unknown')
            ->map(fn ($group) => [
                'total'   => $group->count(),
                'present' => $group->where('status', 'present')->count(),
                'absent'  => $group->where('status', 'absent')->count(),
                'late'    => $group->where('status', 'late')->count(),
                'excused' => $group->where('status', 'excused')->count(),
            ]);

        return response()->json(compact('records', 'summary'));
    }
}
