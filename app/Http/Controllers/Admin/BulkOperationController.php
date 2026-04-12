<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BulkOperationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BulkOperationController extends Controller
{
    protected BulkOperationService $bulkService;

    public function __construct(BulkOperationService $bulkService)
    {
        $this->bulkService = $bulkService;
    }

    /**
     * POST /api/v1/admin/bulk/enroll
     * Bulk enroll students in sections.
     */
    public function enrollStudents(Request $request): JsonResponse
    {
        $data = $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'required|integer|exists:students,id',
            'section_ids' => 'required|array|min:1',
            'section_ids.*' => 'required|integer|exists:sections,id',
            'academic_term_id' => 'required|integer|exists:academic_terms,id',
        ]);

        $results = $this->bulkService->bulkEnrollStudents(
            $data['student_ids'],
            $data['section_ids'],
            $data['academic_term_id']
        );

        return response()->json([
            'message' => "Bulk enrollment completed. {$results['success']} successful, {$results['failed']} failed.",
            'results' => $results,
        ], $results['failed'] > 0 ? 207 : 201); // 207 Multi-Status for partial success
    }

    /**
     * POST /api/v1/admin/bulk/grades
     * Bulk update grades.
     */
    public function updateGrades(Request $request): JsonResponse
    {
        $data = $request->validate([
            'grades' => 'required|array|min:1',
            'grades.*.enrollment_id' => 'required|integer|exists:enrollments,id',
            'grades.*.midterm' => 'nullable|numeric|min:0|max:100',
            'grades.*.final' => 'nullable|numeric|min:0|max:100',
            'grades.*.coursework' => 'nullable|numeric|min:0|max:100',
        ]);

        $results = $this->bulkService->bulkUpdateGrades($data['grades']);

        return response()->json([
            'message' => "Bulk grade update completed. {$results['success']} successful, {$results['failed']} failed.",
            'results' => $results,
        ], $results['failed'] > 0 ? 207 : 200);
    }

    /**
     * POST /api/v1/admin/bulk/transfer
     * Bulk transfer students to new department.
     */
    public function transferStudents(Request $request): JsonResponse
    {
        $data = $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'required|integer|exists:students,id',
            'new_department_id' => 'required|integer|exists:departments,id',
            'note' => 'nullable|string|max:500',
        ]);

        $results = $this->bulkService->bulkTransferStudents(
            $data['student_ids'],
            $data['new_department_id'],
            $data['note'] ?? ''
        );

        return response()->json([
            'message' => "Bulk transfer completed. {$results['success']} successful, {$results['failed']} failed.",
            'results' => $results,
        ], $results['failed'] > 0 ? 207 : 200);
    }
}
