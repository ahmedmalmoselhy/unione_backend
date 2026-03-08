<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SectionSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $courses    = DB::table('courses')->pluck('id', 'code');
        $professors = DB::table('professors')
            ->join('departments', 'professors.department_id', '=', 'departments.id')
            ->pluck('professors.id', 'departments.code');

        // Map dept_code → array of professor IDs (some depts have >1 professor)
        $profsByDept = DB::table('professors')
            ->join('departments', 'professors.department_id', '=', 'departments.id')
            ->select('professors.id', 'departments.code')
            ->get()
            ->groupBy('code')
            ->map(fn ($group) => $group->pluck('id')->toArray());

        $currentTerm  = DB::table('academic_terms')->where('is_active', true)->value('id');
        $pastTermFirst = DB::table('academic_terms')
            ->where('academic_year', 2024)->where('semester', 'first')->value('id');
        $pastTermSecond = DB::table('academic_terms')
            ->where('academic_year', 2024)->where('semester', 'second')->value('id');

        // Helper to pick a professor from a department
        $pickProf = fn (string $deptCode, int $idx = 0) =>
            $profsByDept[$deptCode][$idx % count($profsByDept[$deptCode])] ?? null;

        $sections = [

            // =====================================================================
            // CURRENT TERM — First Semester 2025/2026
            // =====================================================================

            // ── CSIT Courses ─────────────────────────────────────────────────────
            [
                'course_code' => 'CS101',
                'dept'        => 'CS',
                'prof_idx'    => 0,
                'term_id'     => $currentTerm,
                'capacity'    => 60,
                'room'        => 'CSIT-L1',
                'schedule'    => [
                    ['day' => 'sunday',    'start_time' => '08:00', 'end_time' => '09:30', 'type' => 'lecture'],
                    ['day' => 'tuesday',   'start_time' => '08:00', 'end_time' => '09:30', 'type' => 'lecture'],
                    ['day' => 'wednesday', 'start_time' => '10:00', 'end_time' => '12:00', 'type' => 'lab'],
                ],
            ],
            [
                'course_code' => 'CS101',
                'dept'        => 'CS',
                'prof_idx'    => 1,
                'term_id'     => $currentTerm,
                'capacity'    => 45,
                'room'        => 'CSIT-L2',
                'schedule'    => [
                    ['day' => 'monday',    'start_time' => '10:00', 'end_time' => '11:30', 'type' => 'lecture'],
                    ['day' => 'wednesday', 'start_time' => '10:00', 'end_time' => '11:30', 'type' => 'lecture'],
                    ['day' => 'thursday',  'start_time' => '08:00', 'end_time' => '10:00', 'type' => 'lab'],
                ],
            ],
            [
                'course_code' => 'CS102',
                'dept'        => 'CS',
                'prof_idx'    => 0,
                'term_id'     => $currentTerm,
                'capacity'    => 50,
                'room'        => 'CSIT-L3',
                'schedule'    => [
                    ['day' => 'sunday',    'start_time' => '10:00', 'end_time' => '11:30', 'type' => 'lecture'],
                    ['day' => 'tuesday',   'start_time' => '10:00', 'end_time' => '11:30', 'type' => 'lecture'],
                    ['day' => 'monday',    'start_time' => '12:00', 'end_time' => '14:00', 'type' => 'lab'],
                ],
            ],
            [
                'course_code' => 'MATH101',
                'dept'        => 'CS',
                'prof_idx'    => 1,
                'term_id'     => $currentTerm,
                'capacity'    => 80,
                'room'        => 'CSIT-H1',
                'schedule'    => [
                    ['day' => 'sunday',    'start_time' => '12:00', 'end_time' => '13:30', 'type' => 'lecture'],
                    ['day' => 'wednesday', 'start_time' => '12:00', 'end_time' => '13:30', 'type' => 'lecture'],
                ],
            ],
            [
                'course_code' => 'CS201',
                'dept'        => 'CS',
                'prof_idx'    => 0,
                'term_id'     => $currentTerm,
                'capacity'    => 40,
                'room'        => 'CSIT-L4',
                'schedule'    => [
                    ['day' => 'monday',    'start_time' => '14:00', 'end_time' => '15:30', 'type' => 'lecture'],
                    ['day' => 'thursday',  'start_time' => '14:00', 'end_time' => '15:30', 'type' => 'lecture'],
                ],
            ],
            [
                'course_code' => 'CS301',
                'dept'        => 'CS',
                'prof_idx'    => 0,
                'term_id'     => $currentTerm,
                'capacity'    => 35,
                'room'        => 'CSIT-Lab1',
                'schedule'    => [
                    ['day' => 'tuesday',   'start_time' => '12:00', 'end_time' => '13:30', 'type' => 'lecture'],
                    ['day' => 'thursday',  'start_time' => '10:00', 'end_time' => '12:00', 'type' => 'lab'],
                ],
            ],
            [
                'course_code' => 'IS201',
                'dept'        => 'IS',
                'prof_idx'    => 0,
                'term_id'     => $currentTerm,
                'capacity'    => 45,
                'room'        => 'CSIT-L5',
                'schedule'    => [
                    ['day' => 'sunday',    'start_time' => '14:00', 'end_time' => '15:30', 'type' => 'lecture'],
                    ['day' => 'tuesday',   'start_time' => '14:00', 'end_time' => '15:30', 'type' => 'lecture'],
                    ['day' => 'wednesday', 'start_time' => '14:00', 'end_time' => '16:00', 'type' => 'lab'],
                ],
            ],
            [
                'course_code' => 'IS301',
                'dept'        => 'IS',
                'prof_idx'    => 1,
                'term_id'     => $currentTerm,
                'capacity'    => 40,
                'room'        => 'CSIT-L6',
                'schedule'    => [
                    ['day' => 'monday',    'start_time' => '08:00', 'end_time' => '09:30', 'type' => 'lecture'],
                    ['day' => 'wednesday', 'start_time' => '08:00', 'end_time' => '09:30', 'type' => 'lecture'],
                ],
            ],
            [
                'course_code' => 'CYB201',
                'dept'        => 'CYB',
                'prof_idx'    => 0,
                'term_id'     => $currentTerm,
                'capacity'    => 35,
                'room'        => 'CSIT-Sec1',
                'schedule'    => [
                    ['day' => 'sunday',    'start_time' => '10:00', 'end_time' => '11:30', 'type' => 'lecture'],
                    ['day' => 'thursday',  'start_time' => '10:00', 'end_time' => '12:00', 'type' => 'lab'],
                ],
            ],
            [
                'course_code' => 'CYB301',
                'dept'        => 'CYB',
                'prof_idx'    => 0,
                'term_id'     => $currentTerm,
                'capacity'    => 30,
                'room'        => 'CSIT-Sec2',
                'schedule'    => [
                    ['day' => 'monday',    'start_time' => '10:00', 'end_time' => '11:30', 'type' => 'lecture'],
                    ['day' => 'wednesday', 'start_time' => '10:00', 'end_time' => '12:00', 'type' => 'lab'],
                ],
            ],
            [
                'course_code' => 'AI301',
                'dept'        => 'AI',
                'prof_idx'    => 0,
                'term_id'     => $currentTerm,
                'capacity'    => 40,
                'room'        => 'CSIT-AI1',
                'schedule'    => [
                    ['day' => 'tuesday',   'start_time' => '08:00', 'end_time' => '09:30', 'type' => 'lecture'],
                    ['day' => 'thursday',  'start_time' => '08:00', 'end_time' => '09:30', 'type' => 'lecture'],
                    ['day' => 'sunday',    'start_time' => '16:00', 'end_time' => '18:00', 'type' => 'lab'],
                ],
            ],
            [
                'course_code' => 'AI302',
                'dept'        => 'AI',
                'prof_idx'    => 0,
                'term_id'     => $currentTerm,
                'capacity'    => 35,
                'room'        => 'CSIT-AI2',
                'schedule'    => [
                    ['day' => 'monday',    'start_time' => '12:00', 'end_time' => '13:30', 'type' => 'lecture'],
                    ['day' => 'wednesday', 'start_time' => '14:00', 'end_time' => '16:00', 'type' => 'lab'],
                ],
            ],

            // ── Engineering Courses ──────────────────────────────────────────────
            [
                'course_code' => 'ENG001',
                'dept'        => 'CIVIL',
                'prof_idx'    => 0,
                'term_id'     => $currentTerm,
                'capacity'    => 100,
                'room'        => 'ENG-H1',
                'schedule'    => [
                    ['day' => 'sunday',   'start_time' => '08:00', 'end_time' => '09:30', 'type' => 'lecture'],
                    ['day' => 'tuesday',  'start_time' => '08:00', 'end_time' => '09:30', 'type' => 'lecture'],
                ],
            ],
            [
                'course_code' => 'ENG003',
                'dept'        => 'CIVIL',
                'prof_idx'    => 1,
                'term_id'     => $currentTerm,
                'capacity'    => 40,
                'room'        => 'ENG-Draft1',
                'schedule'    => [
                    ['day' => 'monday',    'start_time' => '10:00', 'end_time' => '11:00', 'type' => 'lecture'],
                    ['day' => 'wednesday', 'start_time' => '10:00', 'end_time' => '12:00', 'type' => 'lab'],
                ],
            ],
            [
                'course_code' => 'CIVIL201',
                'dept'        => 'CIVIL',
                'prof_idx'    => 0,
                'term_id'     => $currentTerm,
                'capacity'    => 40,
                'room'        => 'ENG-A201',
                'schedule'    => [
                    ['day' => 'monday',    'start_time' => '14:00', 'end_time' => '15:30', 'type' => 'lecture'],
                    ['day' => 'thursday',  'start_time' => '14:00', 'end_time' => '15:30', 'type' => 'lecture'],
                ],
            ],
            [
                'course_code' => 'CIVIL301',
                'dept'        => 'CIVIL',
                'prof_idx'    => 0,
                'term_id'     => $currentTerm,
                'capacity'    => 35,
                'room'        => 'ENG-A301',
                'schedule'    => [
                    ['day' => 'sunday',    'start_time' => '12:00', 'end_time' => '13:30', 'type' => 'lecture'],
                    ['day' => 'wednesday', 'start_time' => '12:00', 'end_time' => '13:30', 'type' => 'lecture'],
                ],
            ],
            [
                'course_code' => 'ELEC201',
                'dept'        => 'ELEC',
                'prof_idx'    => 0,
                'term_id'     => $currentTerm,
                'capacity'    => 40,
                'room'        => 'ENG-B101',
                'schedule'    => [
                    ['day' => 'sunday',    'start_time' => '10:00', 'end_time' => '11:30', 'type' => 'lecture'],
                    ['day' => 'tuesday',   'start_time' => '10:00', 'end_time' => '11:30', 'type' => 'lecture'],
                    ['day' => 'thursday',  'start_time' => '10:00', 'end_time' => '12:00', 'type' => 'lab'],
                ],
            ],
            [
                'course_code' => 'ELEC301',
                'dept'        => 'ELEC',
                'prof_idx'    => 0,
                'term_id'     => $currentTerm,
                'capacity'    => 35,
                'room'        => 'ENG-B201',
                'schedule'    => [
                    ['day' => 'monday',    'start_time' => '12:00', 'end_time' => '13:30', 'type' => 'lecture'],
                    ['day' => 'wednesday', 'start_time' => '12:00', 'end_time' => '13:30', 'type' => 'lecture'],
                ],
            ],
            [
                'course_code' => 'MECH201',
                'dept'        => 'MECH',
                'prof_idx'    => 0,
                'term_id'     => $currentTerm,
                'capacity'    => 40,
                'room'        => 'ENG-B301',
                'schedule'    => [
                    ['day' => 'tuesday',   'start_time' => '14:00', 'end_time' => '15:30', 'type' => 'lecture'],
                    ['day' => 'thursday',  'start_time' => '14:00', 'end_time' => '15:30', 'type' => 'lecture'],
                ],
            ],
            [
                'course_code' => 'MECH301',
                'dept'        => 'MECH',
                'prof_idx'    => 0,
                'term_id'     => $currentTerm,
                'capacity'    => 30,
                'room'        => 'ENG-Workshop1',
                'schedule'    => [
                    ['day' => 'monday',    'start_time' => '08:00', 'end_time' => '09:30', 'type' => 'lecture'],
                    ['day' => 'wednesday', 'start_time' => '08:00', 'end_time' => '10:00', 'type' => 'lab'],
                ],
            ],
            [
                'course_code' => 'ARCH201',
                'dept'        => 'ARCH',
                'prof_idx'    => 0,
                'term_id'     => $currentTerm,
                'capacity'    => 35,
                'room'        => 'ARCH-Studio1',
                'schedule'    => [
                    ['day' => 'sunday',    'start_time' => '10:00', 'end_time' => '12:00', 'type' => 'lecture'],
                    ['day' => 'tuesday',   'start_time' => '10:00', 'end_time' => '14:00', 'type' => 'lab'],
                ],
            ],
            [
                'course_code' => 'ARCH301',
                'dept'        => 'ARCH',
                'prof_idx'    => 0,
                'term_id'     => $currentTerm,
                'capacity'    => 30,
                'room'        => 'ARCH-Studio2',
                'schedule'    => [
                    ['day' => 'wednesday', 'start_time' => '10:00', 'end_time' => '12:00', 'type' => 'lecture'],
                    ['day' => 'thursday',  'start_time' => '10:00', 'end_time' => '14:00', 'type' => 'lab'],
                ],
            ],

            // ── Medicine Courses ─────────────────────────────────────────────────
            [
                'course_code' => 'MED101',
                'dept'        => 'MED-INT',
                'prof_idx'    => 0,
                'term_id'     => $currentTerm,
                'capacity'    => 80,
                'room'        => 'MED-H1',
                'schedule'    => [
                    ['day' => 'sunday',  'start_time' => '08:00', 'end_time' => '10:00', 'type' => 'lecture'],
                    ['day' => 'tuesday', 'start_time' => '08:00', 'end_time' => '10:00', 'type' => 'lecture'],
                    ['day' => 'thursday','start_time' => '08:00', 'end_time' => '10:00', 'type' => 'lab'],
                ],
            ],
            [
                'course_code' => 'MED102',
                'dept'        => 'MED-INT',
                'prof_idx'    => 1,
                'term_id'     => $currentTerm,
                'capacity'    => 80,
                'room'        => 'MED-H2',
                'schedule'    => [
                    ['day' => 'monday',    'start_time' => '08:00', 'end_time' => '10:00', 'type' => 'lecture'],
                    ['day' => 'wednesday', 'start_time' => '08:00', 'end_time' => '10:00', 'type' => 'lecture'],
                    ['day' => 'thursday',  'start_time' => '12:00', 'end_time' => '14:00', 'type' => 'lab'],
                ],
            ],
            [
                'course_code' => 'MED-INT301',
                'dept'        => 'MED-INT',
                'prof_idx'    => 0,
                'term_id'     => $currentTerm,
                'capacity'    => 40,
                'room'        => 'MED-301',
                'schedule'    => [
                    ['day' => 'sunday',    'start_time' => '12:00', 'end_time' => '14:00', 'type' => 'lecture'],
                    ['day' => 'wednesday', 'start_time' => '12:00', 'end_time' => '14:00', 'type' => 'lab'],
                ],
            ],
            [
                'course_code' => 'MED-SURG301',
                'dept'        => 'MED-SURG',
                'prof_idx'    => 0,
                'term_id'     => $currentTerm,
                'capacity'    => 40,
                'room'        => 'MED-OR1',
                'schedule'    => [
                    ['day' => 'monday',   'start_time' => '12:00', 'end_time' => '14:00', 'type' => 'lecture'],
                    ['day' => 'thursday', 'start_time' => '12:00', 'end_time' => '14:00', 'type' => 'lab'],
                ],
            ],
            [
                'course_code' => 'MED-PHAR301',
                'dept'        => 'MED-PHAR',
                'prof_idx'    => 0,
                'term_id'     => $currentTerm,
                'capacity'    => 45,
                'room'        => 'MED-Pharm1',
                'schedule'    => [
                    ['day' => 'tuesday',   'start_time' => '12:00', 'end_time' => '13:30', 'type' => 'lecture'],
                    ['day' => 'thursday',  'start_time' => '14:00', 'end_time' => '16:00', 'type' => 'lab'],
                ],
            ],

            // ── Business Courses ─────────────────────────────────────────────────
            [
                'course_code' => 'BUS001',
                'dept'        => 'MKT',
                'prof_idx'    => 0,
                'term_id'     => $currentTerm,
                'capacity'    => 80,
                'room'        => 'BUS-H1',
                'schedule'    => [
                    ['day' => 'sunday',   'start_time' => '10:00', 'end_time' => '11:30', 'type' => 'lecture'],
                    ['day' => 'tuesday',  'start_time' => '10:00', 'end_time' => '11:30', 'type' => 'lecture'],
                ],
            ],
            [
                'course_code' => 'MKT201',
                'dept'        => 'MKT',
                'prof_idx'    => 0,
                'term_id'     => $currentTerm,
                'capacity'    => 50,
                'room'        => 'BUS-101',
                'schedule'    => [
                    ['day' => 'monday',    'start_time' => '10:00', 'end_time' => '11:30', 'type' => 'lecture'],
                    ['day' => 'wednesday', 'start_time' => '10:00', 'end_time' => '11:30', 'type' => 'lecture'],
                ],
            ],
            [
                'course_code' => 'MKT301',
                'dept'        => 'MKT',
                'prof_idx'    => 0,
                'term_id'     => $currentTerm,
                'capacity'    => 40,
                'room'        => 'BUS-201',
                'schedule'    => [
                    ['day' => 'sunday',   'start_time' => '14:00', 'end_time' => '15:30', 'type' => 'lecture'],
                    ['day' => 'thursday', 'start_time' => '10:00', 'end_time' => '11:30', 'type' => 'lecture'],
                ],
            ],
            [
                'course_code' => 'BUS-FIN201',
                'dept'        => 'BUS-FIN',
                'prof_idx'    => 0,
                'term_id'     => $currentTerm,
                'capacity'    => 50,
                'room'        => 'BUS-202',
                'schedule'    => [
                    ['day' => 'sunday',   'start_time' => '12:00', 'end_time' => '13:30', 'type' => 'lecture'],
                    ['day' => 'tuesday',  'start_time' => '12:00', 'end_time' => '13:30', 'type' => 'lecture'],
                ],
            ],
            [
                'course_code' => 'BUS-FIN301',
                'dept'        => 'BUS-FIN',
                'prof_idx'    => 0,
                'term_id'     => $currentTerm,
                'capacity'    => 40,
                'room'        => 'BUS-301',
                'schedule'    => [
                    ['day' => 'monday',    'start_time' => '14:00', 'end_time' => '15:30', 'type' => 'lecture'],
                    ['day' => 'wednesday', 'start_time' => '14:00', 'end_time' => '15:30', 'type' => 'lecture'],
                ],
            ],
            [
                'course_code' => 'BUS-HR201',
                'dept'        => 'BUS-HR',
                'prof_idx'    => 0,
                'term_id'     => $currentTerm,
                'capacity'    => 45,
                'room'        => 'BUS-302',
                'schedule'    => [
                    ['day' => 'tuesday',  'start_time' => '14:00', 'end_time' => '15:30', 'type' => 'lecture'],
                    ['day' => 'thursday', 'start_time' => '14:00', 'end_time' => '15:30', 'type' => 'lecture'],
                ],
            ],

            // ── Law Courses ──────────────────────────────────────────────────────
            [
                'course_code' => 'LAW101',
                'dept'        => 'LAW-PUB',
                'prof_idx'    => 0,
                'term_id'     => $currentTerm,
                'capacity'    => 70,
                'room'        => 'LAW-H1',
                'schedule'    => [
                    ['day' => 'sunday',   'start_time' => '08:00', 'end_time' => '09:30', 'type' => 'lecture'],
                    ['day' => 'tuesday',  'start_time' => '08:00', 'end_time' => '09:30', 'type' => 'lecture'],
                ],
            ],
            [
                'course_code' => 'LAW-PUB201',
                'dept'        => 'LAW-PUB',
                'prof_idx'    => 0,
                'term_id'     => $currentTerm,
                'capacity'    => 45,
                'room'        => 'LAW-101',
                'schedule'    => [
                    ['day' => 'monday',    'start_time' => '08:00', 'end_time' => '09:30', 'type' => 'lecture'],
                    ['day' => 'wednesday', 'start_time' => '08:00', 'end_time' => '09:30', 'type' => 'lecture'],
                ],
            ],
            [
                'course_code' => 'LAW-PRI201',
                'dept'        => 'LAW-PRI',
                'prof_idx'    => 0,
                'term_id'     => $currentTerm,
                'capacity'    => 45,
                'room'        => 'LAW-201',
                'schedule'    => [
                    ['day' => 'sunday',   'start_time' => '10:00', 'end_time' => '11:30', 'type' => 'lecture'],
                    ['day' => 'tuesday',  'start_time' => '10:00', 'end_time' => '11:30', 'type' => 'lecture'],
                ],
            ],
            [
                'course_code' => 'LAW-PRI301',
                'dept'        => 'LAW-PRI',
                'prof_idx'    => 0,
                'term_id'     => $currentTerm,
                'capacity'    => 40,
                'room'        => 'LAW-301',
                'schedule'    => [
                    ['day' => 'monday',    'start_time' => '10:00', 'end_time' => '11:30', 'type' => 'lecture'],
                    ['day' => 'wednesday', 'start_time' => '10:00', 'end_time' => '11:30', 'type' => 'lecture'],
                ],
            ],

            // =====================================================================
            // PAST TERM — First Semester 2024/2025 (historical data)
            // =====================================================================
            [
                'course_code' => 'CS101',
                'dept'        => 'CS',
                'prof_idx'    => 0,
                'term_id'     => $pastTermFirst,
                'capacity'    => 55,
                'room'        => 'CSIT-L1',
                'schedule'    => [
                    ['day' => 'sunday',   'start_time' => '08:00', 'end_time' => '09:30', 'type' => 'lecture'],
                    ['day' => 'tuesday',  'start_time' => '08:00', 'end_time' => '09:30', 'type' => 'lecture'],
                    ['day' => 'thursday', 'start_time' => '10:00', 'end_time' => '12:00', 'type' => 'lab'],
                ],
                'is_active' => false,
            ],
            [
                'course_code' => 'CS102',
                'dept'        => 'CS',
                'prof_idx'    => 1,
                'term_id'     => $pastTermFirst,
                'capacity'    => 50,
                'room'        => 'CSIT-L3',
                'schedule'    => [
                    ['day' => 'monday',    'start_time' => '10:00', 'end_time' => '11:30', 'type' => 'lecture'],
                    ['day' => 'wednesday', 'start_time' => '10:00', 'end_time' => '11:30', 'type' => 'lecture'],
                    ['day' => 'sunday',    'start_time' => '14:00', 'end_time' => '16:00', 'type' => 'lab'],
                ],
                'is_active' => false,
            ],
            [
                'course_code' => 'MATH101',
                'dept'        => 'CS',
                'prof_idx'    => 0,
                'term_id'     => $pastTermFirst,
                'capacity'    => 75,
                'room'        => 'CSIT-H1',
                'schedule'    => [
                    ['day' => 'sunday',    'start_time' => '12:00', 'end_time' => '13:30', 'type' => 'lecture'],
                    ['day' => 'wednesday', 'start_time' => '12:00', 'end_time' => '13:30', 'type' => 'lecture'],
                ],
                'is_active' => false,
            ],
            [
                'course_code' => 'ENG001',
                'dept'        => 'CIVIL',
                'prof_idx'    => 0,
                'term_id'     => $pastTermFirst,
                'capacity'    => 90,
                'room'        => 'ENG-H1',
                'schedule'    => [
                    ['day' => 'sunday',  'start_time' => '08:00', 'end_time' => '09:30', 'type' => 'lecture'],
                    ['day' => 'tuesday', 'start_time' => '08:00', 'end_time' => '09:30', 'type' => 'lecture'],
                ],
                'is_active' => false,
            ],
            [
                'course_code' => 'MED101',
                'dept'        => 'MED-INT',
                'prof_idx'    => 0,
                'term_id'     => $pastTermFirst,
                'capacity'    => 75,
                'room'        => 'MED-H1',
                'schedule'    => [
                    ['day' => 'sunday',  'start_time' => '08:00', 'end_time' => '10:00', 'type' => 'lecture'],
                    ['day' => 'tuesday', 'start_time' => '08:00', 'end_time' => '10:00', 'type' => 'lecture'],
                ],
                'is_active' => false,
            ],

            // ── Past Term — Second Semester 2024/2025 ────────────────────────────
            [
                'course_code' => 'MATH102',
                'dept'        => 'CS',
                'prof_idx'    => 1,
                'term_id'     => $pastTermSecond,
                'capacity'    => 70,
                'room'        => 'CSIT-H1',
                'schedule'    => [
                    ['day' => 'sunday',    'start_time' => '10:00', 'end_time' => '11:30', 'type' => 'lecture'],
                    ['day' => 'wednesday', 'start_time' => '10:00', 'end_time' => '11:30', 'type' => 'lecture'],
                ],
                'is_active' => false,
            ],
            [
                'course_code' => 'CS103',
                'dept'        => 'CS',
                'prof_idx'    => 1,
                'term_id'     => $pastTermSecond,
                'capacity'    => 45,
                'room'        => 'CSIT-L2',
                'schedule'    => [
                    ['day' => 'monday',    'start_time' => '12:00', 'end_time' => '13:30', 'type' => 'lecture'],
                    ['day' => 'wednesday', 'start_time' => '12:00', 'end_time' => '13:30', 'type' => 'lecture'],
                    ['day' => 'thursday',  'start_time' => '10:00', 'end_time' => '12:00', 'type' => 'lab'],
                ],
                'is_active' => false,
            ],
            [
                'course_code' => 'AI301',
                'dept'        => 'AI',
                'prof_idx'    => 0,
                'term_id'     => $pastTermSecond,
                'capacity'    => 35,
                'room'        => 'CSIT-AI1',
                'schedule'    => [
                    ['day' => 'tuesday',  'start_time' => '08:00', 'end_time' => '09:30', 'type' => 'lecture'],
                    ['day' => 'thursday', 'start_time' => '08:00', 'end_time' => '09:30', 'type' => 'lecture'],
                    ['day' => 'sunday',   'start_time' => '16:00', 'end_time' => '18:00', 'type' => 'lab'],
                ],
                'is_active' => false,
            ],
            [
                'course_code' => 'CIVIL202',
                'dept'        => 'CIVIL',
                'prof_idx'    => 0,
                'term_id'     => $pastTermSecond,
                'capacity'    => 40,
                'room'        => 'ENG-A201',
                'schedule'    => [
                    ['day' => 'monday',   'start_time' => '14:00', 'end_time' => '15:30', 'type' => 'lecture'],
                    ['day' => 'thursday', 'start_time' => '14:00', 'end_time' => '16:00', 'type' => 'lab'],
                ],
                'is_active' => false,
            ],
            [
                'course_code' => 'BUS002',
                'dept'        => 'BUS-FIN',
                'prof_idx'    => 0,
                'term_id'     => $pastTermSecond,
                'capacity'    => 70,
                'room'        => 'BUS-H1',
                'schedule'    => [
                    ['day' => 'sunday',  'start_time' => '12:00', 'end_time' => '13:30', 'type' => 'lecture'],
                    ['day' => 'tuesday', 'start_time' => '12:00', 'end_time' => '13:30', 'type' => 'lecture'],
                ],
                'is_active' => false,
            ],
        ];

        foreach ($sections as $data) {
            $profId = $pickProf($data['dept'], $data['prof_idx']);
            if (!$profId || !isset($courses[$data['course_code']])) {
                continue;
            }

            DB::table('sections')->insert([
                'course_id'        => $courses[$data['course_code']],
                'professor_id'     => $profId,
                'academic_term_id' => $data['term_id'],
                'capacity'         => $data['capacity'],
                'room'             => $data['room'],
                'schedule'         => json_encode($data['schedule']),
                'is_active'        => $data['is_active'] ?? true,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        }
    }
}
