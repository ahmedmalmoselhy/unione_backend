<?php

namespace App\Services;

use App\Models\AcademicTerm;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Section;
use App\Models\Student;
use App\Services\GpaService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BulkOperationService
{
    protected GpaService $gpaService;

    public function __construct(GpaService $gpaService)
    {
        $this->gpaService = $gpaService;
    }

    /**
     * Bulk enroll students in multiple sections.
     *
     * @param array $studentIds Array of student IDs
     * @param array $sectionIds Array of section IDs
     * @param int $academicTermId Academic term ID
     * @return array Results with success/failed counts
     */
    public function bulkEnrollStudents(array $studentIds, array $sectionIds, int $academicTermId): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        $term = AcademicTerm::find($academicTermId);

        if (!$term) {
            throw new \InvalidArgumentException("Academic term not found: {$academicTermId}");
        }

        DB::beginTransaction();

        try {
            foreach ($studentIds as $studentId) {
                $student = Student::find($studentId);

                if (!$student) {
                    $results['failed']++;
                    $results['errors'][] = "Student not found: {$studentId}";
                    continue;
                }

                foreach ($sectionIds as $sectionId) {
                    try {
                        $section = Section::with('course.prerequisites')->find($sectionId);

                        if (!$section || !$section->is_active) {
                            continue;
                        }

                        // Check if already enrolled
                        $exists = Enrollment::where('student_id', $studentId)
                            ->where('section_id', $sectionId)
                            ->whereIn('status', ['registered', 'completed'])
                            ->exists();

                        if ($exists) {
                            continue;
                        }

                        // Check capacity
                        $currentEnrollments = Enrollment::where('section_id', $sectionId)
                            ->whereIn('status', ['registered', 'completed'])
                            ->count();

                        if ($currentEnrollments >= $section->capacity) {
                            $results['failed']++;
                            $results['errors'][] = "Section {$sectionId} is full";
                            continue;
                        }

                        Enrollment::create([
                            'student_id' => $studentId,
                            'section_id' => $sectionId,
                            'academic_term_id' => $academicTermId,
                            'status' => 'registered',
                            'registered_at' => now(),
                        ]);

                        $results['success']++;
                    } catch (\Exception $e) {
                        $results['failed']++;
                        $results['errors'][] = "Failed to enroll student {$studentId} in section {$sectionId}: " . $e->getMessage();
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $results;
    }

    /**
     * Bulk update grades for multiple enrollments.
     *
     * @param array $gradeData Array of ['enrollment_id' => ..., 'midterm' => ..., 'final' => ..., 'coursework' => ...]
     * @return array Results
     */
    public function bulkUpdateGrades(array $gradeData): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        DB::beginTransaction();

        try {
            $affectedStudents = [];

            foreach ($gradeData as $data) {
                try {
                    $enrollment = Enrollment::find($data['enrollment_id']);

                    if (!$enrollment) {
                        $results['failed']++;
                        $results['errors'][] = "Enrollment not found: {$data['enrollment_id']}";
                        continue;
                    }

                    $grade = Grade::updateOrCreate(
                        ['enrollment_id' => $data['enrollment_id']],
                        [
                            'midterm' => $data['midterm'] ?? null,
                            'final' => $data['final'] ?? null,
                            'coursework' => $data['coursework'] ?? null,
                            'total' => ($data['midterm'] ?? 0) + ($data['final'] ?? 0) + ($data['coursework'] ?? 0),
                            'graded_at' => now(),
                        ]
                    );

                    $grade->calculateLetterGrade();
                    $grade->save();

                    $affectedStudents[] = $enrollment->student_id;
                    $results['success']++;
                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = "Failed to update grade for enrollment {$data['enrollment_id']}: " . $e->getMessage();
                }
            }

            // Recalculate GPA for affected students
            foreach (collect($affectedStudents)->unique() as $studentId) {
                $this->gpaService->recalculateStudentGpa($studentId);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $results;
    }

    /**
     * Bulk transfer students to a new department.
     *
     * @param array $studentIds Array of student IDs
     * @param int $newDepartmentId New department ID
     * @param string $note Transfer note
     * @return array Results
     */
    public function bulkTransferStudents(array $studentIds, int $newDepartmentId, string $note = ''): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        DB::beginTransaction();

        try {
            foreach ($studentIds as $studentId) {
                try {
                    $student = Student::find($studentId);

                    if (!$student) {
                        $results['failed']++;
                        $results['errors'][] = "Student not found: {$studentId}";
                        continue;
                    }

                    $oldDepartmentId = $student->department_id;

                    $student->update([
                        'department_id' => $newDepartmentId,
                    ]);

                    // Record transfer history
                    $student->departmentHistory()->create([
                        'old_department_id' => $oldDepartmentId,
                        'new_department_id' => $newDepartmentId,
                        'switched_by' => auth()->id(),
                        'switched_at' => now(),
                        'note' => $note,
                    ]);

                    $results['success']++;
                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = "Failed to transfer student {$studentId}: " . $e->getMessage();
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $results;
    }
}
