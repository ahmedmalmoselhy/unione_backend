<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfessorController extends Controller
{
    /**
     * GET /api/professor/profile
     * Returns the authenticated professor's profile with department and faculty.
     */
    public function profile(Request $request): JsonResponse
    {
        $professor = $request->user()
            ->professor()
            ->with(['department.faculty'])
            ->firstOrFail();

        return response()->json([
            'professor' => [
                'id'              => $professor->id,
                'staff_number'    => $professor->staff_number,
                'specialization'  => $professor->specialization,
                'academic_rank'   => $professor->academic_rank,
                'office_location' => $professor->office_location,
                'hired_at'        => $professor->hired_at?->toDateString(),
                'department' => $professor->department ? [
                    'id'      => $professor->department->id,
                    'name'    => $professor->department->name,
                    'code'    => $professor->department->code,
                    'faculty' => $professor->department->faculty ? [
                        'id'   => $professor->department->faculty->id,
                        'name' => $professor->department->faculty->name,
                        'code' => $professor->department->faculty->code,
                    ] : null,
                ] : null,
            ],
        ]);
    }

    /**
     * GET /api/professor/sections
     * Returns the authenticated professor's sections with course, academic term,
     * and enrolled student count.
     */
    public function sections(Request $request): JsonResponse
    {
        $professor = $request->user()
            ->professor()
            ->firstOrFail();

        $sections = $professor->sections()
            ->with(['course', 'academicTerm'])
            ->withCount('enrollments')
            ->latest()
            ->get()
            ->map(function ($section) {
                return [
                    'id'                => $section->id,
                    'room'              => $section->room,
                    'schedule'          => $section->schedule,
                    'capacity'          => $section->capacity,
                    'enrollments_count' => $section->enrollments_count,
                    'is_active'         => $section->is_active,
                    'course' => $section->course ? [
                        'id'           => $section->course->id,
                        'code'         => $section->course->code,
                        'name'         => $section->course->name,
                        'credit_hours' => $section->course->credit_hours,
                    ] : null,
                    'academic_term' => $section->academicTerm ? [
                        'id'            => $section->academicTerm->id,
                        'name'          => $section->academicTerm->name,
                        'academic_year' => $section->academicTerm->academic_year,
                        'semester'      => $section->academicTerm->semester,
                    ] : null,
                ];
            });

        return response()->json(['sections' => $sections]);
    }
}
