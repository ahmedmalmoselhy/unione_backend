<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Enrolls each active student in sections that match their department's courses
 * at their current academic year level (in the active term).
 *
 * For each past term, a subset of students also receives retrospective
 * completed/failed enrollments so GradeSeeder has data to grade.
 */
class EnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $activeTerm = DB::table('academic_terms')->where('is_active', true)->first();
        if (! $activeTerm) {
            return;
        }

        // Past terms ordered old â†’ new (exclude active)
        $pastTerms = DB::table('academic_terms')
            ->where('is_active', false)
            ->orderBy('starts_at')
            ->get();

        // Active students with a department
        $students = DB::table('students')
            ->where('enrollment_status', 'active')
            ->whereNotNull('department_id')
            ->get();

        if ($students->isEmpty()) {
            return;
        }

        // Build: dept_id + level â†’ [section_id, ...] for active term
        $activeSectionsByDeptLevel = $this->buildSectionMap($activeTerm->id);

        // â”€â”€ Enroll in active term â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        $rows          = [];
        $seen          = [];   // "student_id-section_id" â†’ true (dedup guard)

        foreach ($students as $student) {
            $sectionIds = $activeSectionsByDeptLevel[$student->department_id][$student->academic_year] ?? [];
            foreach ($sectionIds as $sectionId) {
                $key = $student->id . '-' . $sectionId;
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;
                $rows[] = [
                    'student_id'       => $student->id,
                    'section_id'       => $sectionId,
                    'academic_term_id' => $activeTerm->id,
                    'status'           => 'registered',
                    'registered_at'    => $activeTerm->starts_at,
                    'dropped_at'       => null,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];
            }
        }

        // â”€â”€ Retrospective enrollments for past terms â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        // For years 2, 3, 4 students enrol them in (academic_year - pastOffset) courses
        // pastOffset = 1 for most-recent past term, 2 for next, etc.
        foreach ($pastTerms->reverse()->values()->take(3) as $termOffset => $pastTerm) {
            $pastSections = $this->buildSectionMap($pastTerm->id);
            $statusPool   = ['completed','completed','completed','completed','failed','dropped'];

            foreach ($students as $student) {
                $pastYear = $student->academic_year - ($termOffset + 1);
                if ($pastYear < 1) {
                    continue;
                }

                $sectionIds = $pastSections[$student->department_id][$pastYear] ?? [];
                foreach ($sectionIds as $sectionId) {
                    $key = $student->id . '-' . $sectionId;
                    if (isset($seen[$key])) {
                        continue;
                    }
                    $seen[$key] = true;
                    $status     = $statusPool[array_rand($statusPool)];
                    $rows[]     = [
                        'student_id'       => $student->id,
                        'section_id'       => $sectionId,
                        'academic_term_id' => $pastTerm->id,
                        'status'           => $status,
                        'registered_at'    => $pastTerm->starts_at,
                        'dropped_at'       => $status === 'dropped'
                            ? date('Y-m-d', strtotime($pastTerm->starts_at . ' +20 days'))
                            : null,
                        'created_at'       => $now,
                        'updated_at'       => $now,
                    ];
                }
            }
        }

        // Bulk insert in chunks
        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('enrollments')->insert($chunk);
        }
    }

    /**
     * Build map: dept_id â†’ level â†’ [section_id, ...] for a given term.
     * Returns ONE section per course (to avoid enrolling a student in 2 sections
     * of the same course).
     */
    private function buildSectionMap(int $termId): array
    {
        // All sections in this term with their course level
        $sections = DB::table('sections')
            ->join('courses', 'courses.id', '=', 'sections.course_id')
            ->where('sections.academic_term_id', $termId)
            ->select(
                'sections.id as section_id',
                'sections.course_id',
                'courses.level'
            )
            ->get();

        // For each section, find ALL depts (owner + shared) linked to the course
        $deptLinks = DB::table('department_course')
            ->join('courses', 'courses.id', '=', 'department_course.course_id')
            ->select('department_course.department_id', 'department_course.course_id', 'courses.level')
            ->get()
            ->groupBy('course_id');   // course_id â†’ [dept rows]

        // Build: dept_id â†’ level â†’ course_id â†’ first section_id
        // (one section per course per dept)
        $map = [];
        foreach ($sections as $sec) {
            $depts = $deptLinks[$sec->course_id] ?? collect();
            foreach ($depts as $dl) {
                if (! isset($map[$dl->department_id][$sec->level][$sec->course_id])) {
                    $map[$dl->department_id][$sec->level][$sec->course_id] = $sec->section_id;
                }
            }
        }

        // Flatten: dept_id â†’ level â†’ [section_id, ...]
        $flat = [];
        foreach ($map as $deptId => $levels) {
            foreach ($levels as $level => $courseSections) {
                $flat[$deptId][$level] = array_values($courseSections);
            }
        }

        return $flat;
    }
}
