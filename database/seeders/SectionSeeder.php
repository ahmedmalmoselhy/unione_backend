<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Programmatically creates 2 sections per course per academic term.
 * Each section is assigned to a professor from the course's owner department.
 * Schedules rotate through 8 fixed slot-pairs.
 */
class SectionSeeder extends Seeder
{
    // 8 schedule slot-pairs (each element = array of 2 session slots)
    private array $slots = [
        [['day' => 'sunday',    'start_time' => '08:00', 'end_time' => '09:30', 'type' => 'lecture'],
         ['day' => 'tuesday',   'start_time' => '08:00', 'end_time' => '09:30', 'type' => 'lecture']],

        [['day' => 'monday',    'start_time' => '10:00', 'end_time' => '11:30', 'type' => 'lecture'],
         ['day' => 'wednesday', 'start_time' => '10:00', 'end_time' => '11:30', 'type' => 'lecture']],

        [['day' => 'sunday',    'start_time' => '12:00', 'end_time' => '13:30', 'type' => 'lecture'],
         ['day' => 'wednesday', 'start_time' => '12:00', 'end_time' => '13:30', 'type' => 'lecture']],

        [['day' => 'monday',    'start_time' => '08:00', 'end_time' => '09:30', 'type' => 'lecture'],
         ['day' => 'thursday',  'start_time' => '08:00', 'end_time' => '09:30', 'type' => 'lecture']],

        [['day' => 'tuesday',   'start_time' => '10:00', 'end_time' => '11:30', 'type' => 'lecture'],
         ['day' => 'thursday',  'start_time' => '10:00', 'end_time' => '11:30', 'type' => 'lecture']],

        [['day' => 'sunday',    'start_time' => '14:00', 'end_time' => '15:30', 'type' => 'lecture'],
         ['day' => 'tuesday',   'start_time' => '14:00', 'end_time' => '15:30', 'type' => 'lecture']],

        [['day' => 'monday',    'start_time' => '12:00', 'end_time' => '13:30', 'type' => 'lecture'],
         ['day' => 'wednesday', 'start_time' => '08:00', 'end_time' => '09:30', 'type' => 'lecture']],

        [['day' => 'tuesday',   'start_time' => '12:00', 'end_time' => '13:30', 'type' => 'lecture'],
         ['day' => 'thursday',  'start_time' => '12:00', 'end_time' => '13:30', 'type' => 'lecture']],
    ];

    private array $rooms = ['A101','A102','A103','B201','B202','B203','C301','C302','D101','D102','D201','D202','E101','E102','F201','F202'];

    public function run(): void
    {
        $now = now();

        // Get all terms ordered chronologically
        $terms = DB::table('academic_terms')->orderBy('starts_at')->get();

        // Get courses with their owner department (via department_course where is_owner = true)
        $ownerLinks = DB::table('department_course')
            ->where('is_owner', true)
            ->join('courses', 'courses.id', '=', 'department_course.course_id')
            ->join('departments', 'departments.id', '=', 'department_course.department_id')
            ->select(
                'courses.id as course_id',
                'department_course.department_id as owner_dept_id',
                'departments.faculty_id',
                'courses.level'
            )
            ->get();

        // Professors grouped by department_id â†’ [professor_id, ...]
        $profsByDept = DB::table('professors')
            ->select('id', 'department_id')
            ->get()
            ->groupBy('department_id')
            ->map(fn ($g) => $g->pluck('id')->values()->toArray());

        // Professors grouped by faculty_id â†’ [professor_id, ...]
        $profsByFaculty = DB::table('professors')
            ->join('departments', 'departments.id', '=', 'professors.department_id')
            ->select('professors.id', 'departments.faculty_id')
            ->whereNotNull('departments.faculty_id')
            ->get()
            ->groupBy('faculty_id')
            ->map(fn ($g) => $g->pluck('id')->values()->toArray());

        $slotIdx  = 0;
        $roomIdx  = 0;

        foreach ($ownerLinks as $link) {
            // First try professors from the owner dept, then fall back to faculty-wide
            $profPool = $profsByDept[$link->owner_dept_id]
                ?? ($link->faculty_id ? ($profsByFaculty[$link->faculty_id] ?? []) : []);

            if (empty($profPool)) {
                continue;
            }

            foreach ($terms as $term) {
                // Create 2 sections per course per term
                for ($s = 0; $s < 2; $s++) {
                    $profId   = $profPool[($slotIdx + $s) % count($profPool)];
                    $schedule = $this->slots[($slotIdx + $s) % count($this->slots)];
                    $room     = $this->rooms[$roomIdx % count($this->rooms)];
                    $isActive = (bool) $term->is_active;

                    DB::table('sections')->insert([
                        'course_id'        => $link->course_id,
                        'professor_id'     => $profId,
                        'academic_term_id' => $term->id,
                        'capacity'         => 60,
                        'room'             => $room,
                        'schedule'         => json_encode($schedule),
                        'is_active'        => $isActive,
                        'created_at'       => $now,
                        'updated_at'       => $now,
                    ]);

                    $roomIdx++;
                }

                $slotIdx++;
            }
        }
    }
}
