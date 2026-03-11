<?php

namespace App\Http\Controllers\Portal\Student;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $student = $request->user()->student()->firstOrFail();

        // Load all attendance records grouped by section/course
        $records = AttendanceRecord::where('student_id', $student->id)
            ->with([
                'attendanceSession.section.course',
                'attendanceSession.section.academicTerm',
            ])
            ->orderByDesc('id')
            ->get();

        // Group by section for summary stats
        $byCourse = $records->groupBy(fn ($r) => $r->attendanceSession?->section_id);

        $summary = $byCourse->map(function ($courseRecords) {
            $first = $courseRecords->first()->attendanceSession?->section;
            $total   = $courseRecords->count();
            $present = $courseRecords->where('status', 'present')->count();
            $late    = $courseRecords->where('status', 'late')->count();
            $excused = $courseRecords->where('status', 'excused')->count();
            $absent  = $courseRecords->where('status', 'absent')->count();

            return [
                'section'    => $first,
                'total'      => $total,
                'present'    => $present,
                'late'       => $late,
                'excused'    => $excused,
                'absent'     => $absent,
                'percentage' => $total > 0 ? round(($present + $late) / $total * 100) : 0,
                'records'    => $courseRecords->sortByDesc(fn ($r) => $r->attendanceSession?->session_date),
            ];
        })->values();

        return view('portal.student.attendance', compact('summary'));
    }
}
