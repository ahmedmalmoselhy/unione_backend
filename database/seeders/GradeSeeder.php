<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GradeSeeder extends Seeder
{
    public function run(): void
    {
        $now   = now();
        $grader = DB::table('users')
            ->join('role_user', 'users.id', '=', 'role_user.user_id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('roles.name', 'admin')
            ->value('users.id');

        $gradeScale = [
            ['min' => 90, 'letter' => 'A+', 'points' => 4.00],
            ['min' => 85, 'letter' => 'A',  'points' => 3.75],
            ['min' => 80, 'letter' => 'B+', 'points' => 3.50],
            ['min' => 75, 'letter' => 'B',  'points' => 3.00],
            ['min' => 70, 'letter' => 'C+', 'points' => 2.50],
            ['min' => 65, 'letter' => 'C',  'points' => 2.00],
            ['min' => 60, 'letter' => 'D+', 'points' => 1.50],
            ['min' => 50, 'letter' => 'D',  'points' => 1.00],
            ['min' => 0,  'letter' => 'F',  'points' => 0.00],
        ];

        // Grade completed/failed enrollments in chunks to avoid memory issues
        DB::table('enrollments')
            ->whereIn('status', ['completed', 'failed'])
            ->orderBy('id')
            ->chunk(500, function ($enrollments) use ($now, $grader, $gradeScale) {
                $rows = [];
                foreach ($enrollments as $enrollment) {
                    if ($enrollment->status === 'failed') {
                        $midterm    = rand(5, 20);
                        $coursework = rand(5, 15);
                        $final      = rand(5, 15);
                    } else {
                        $midterm    = rand(15, 40);
                        $coursework = rand(10, 30);
                        $final      = rand(20, 40);
                    }

                    $total = min($midterm + $coursework + $final, 100);

                    $grade = null;
                    foreach ($gradeScale as $g) {
                        if ($total >= $g['min']) {
                            $grade = $g;
                            break;
                        }
                    }

                    $rows[] = [
                        'enrollment_id' => $enrollment->id,
                        'midterm'       => $midterm,
                        'final'         => $final,
                        'coursework'    => $coursework,
                        'total'         => $total,
                        'letter_grade'  => $grade['letter'],
                        'grade_points'  => $grade['points'],
                        'graded_by'     => $grader,
                        'graded_at'     => $enrollment->registered_at
                            ? date('Y-m-d', strtotime($enrollment->registered_at . ' +4 months'))
                            : $now->toDateString(),
                        'created_at'    => $now,
                        'updated_at'    => $now,
                    ];
                }

                if (! empty($rows)) {
                    DB::table('grades')->insert($rows);
                }
            });
    }
}
