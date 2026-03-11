<?php

namespace App\Http\Controllers\Portal\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GradeController extends Controller
{
    public function index(Request $request): View
    {
        $student = $request->user()->student()->firstOrFail();

        $enrollments = $student->enrollments()
            ->with([
                'section.course',
                'section.academicTerm',
                'grade',
            ])
            ->whereHas('grade')
            ->orderByDesc('created_at')
            ->get();

        // Group by academic term
        $byTerm = $enrollments->groupBy(fn ($e) => $e->section?->academicTerm?->name ?? 'Unknown');

        // Index semester GPAs keyed by term name for view lookup
        $termGpas = $student->termGpas()
            ->with('academicTerm')
            ->get()
            ->keyBy(fn ($tg) => $tg->academicTerm?->name ?? '');

        // Calculate cumulative GPA from graded enrollments
        $gpa              = $student->gpa;
        $academicStanding = $student->academic_standing;

        return view('portal.student.grades', compact('byTerm', 'gpa', 'academicStanding', 'termGpas'));
    }
}
