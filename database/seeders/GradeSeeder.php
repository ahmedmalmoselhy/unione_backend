<?php

namespace Database\Seeders;

use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\User;
use Illuminate\Database\Seeder;

class GradeSeeder extends Seeder
{
    public function run(): void
    {
        // Grade only completed/failed enrollments
        $enrollments = Enrollment::whereIn('status', ['completed', 'failed'])->get();

        // Pick an admin user as the grader
        $grader = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->first();

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

            $grade = collect($gradeScale)->first(fn ($g) => $total >= $g['min']);

            Grade::create([
                'enrollment_id' => $enrollment->id,
                'midterm'       => $midterm,
                'final'         => $final,
                'coursework'    => $coursework,
                'total'         => $total,
                'letter_grade'  => $grade['letter'],
                'grade_points'  => $grade['points'],
                'graded_by'     => $grader?->id,
                'graded_at'     => $enrollment->registered_at?->copy()->addMonths(4),
            ]);
        }
    }
}
