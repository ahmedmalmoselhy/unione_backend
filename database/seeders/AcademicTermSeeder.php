<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AcademicTermSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $terms = [
            [
                'name'                     => 'First Semester 2024/2025',
                'name_ar'                  => 'الفصل الأول 2024/2025',
                'academic_year'            => 2024,
                'semester'                 => 'first',
                'starts_at'               => '2024-09-15',
                'ends_at'                 => '2025-01-15',
                'registration_starts_at'  => '2024-08-20',
                'registration_ends_at'    => '2024-09-10',
                'withdrawal_deadline'     => '2024-11-01',
                'exam_starts_at'          => '2025-01-02',
                'exam_ends_at'            => '2025-01-15',
                'grade_submission_deadline'=> '2025-01-30',
                'is_active'               => false,
            ],
            [
                'name'                     => 'Second Semester 2024/2025',
                'name_ar'                  => 'الفصل الثاني 2024/2025',
                'academic_year'            => 2024,
                'semester'                 => 'second',
                'starts_at'               => '2025-02-01',
                'ends_at'                 => '2025-06-01',
                'registration_starts_at'  => '2025-01-15',
                'registration_ends_at'    => '2025-01-28',
                'withdrawal_deadline'     => '2025-03-15',
                'exam_starts_at'          => '2025-05-18',
                'exam_ends_at'            => '2025-06-01',
                'grade_submission_deadline'=> '2025-06-15',
                'is_active'               => false,
            ],
            [
                'name'                     => 'Summer Semester 2024/2025',
                'name_ar'                  => 'الفصل الصيفي 2024/2025',
                'academic_year'            => 2024,
                'semester'                 => 'summer',
                'starts_at'               => '2025-06-20',
                'ends_at'                 => '2025-08-15',
                'registration_starts_at'  => '2025-06-05',
                'registration_ends_at'    => '2025-06-18',
                'withdrawal_deadline'     => '2025-07-05',
                'exam_starts_at'          => '2025-08-05',
                'exam_ends_at'            => '2025-08-15',
                'grade_submission_deadline'=> '2025-08-25',
                'is_active'               => false,
            ],
            [
                'name'                     => 'First Semester 2025/2026',
                'name_ar'                  => 'الفصل الأول 2025/2026',
                'academic_year'            => 2025,
                'semester'                 => 'first',
                'starts_at'               => '2025-09-14',
                'ends_at'                 => '2026-01-15',
                'registration_starts_at'  => '2025-08-20',
                'registration_ends_at'    => '2025-09-10',
                'withdrawal_deadline'     => '2025-11-01',
                'exam_starts_at'          => '2026-01-02',
                'exam_ends_at'            => '2026-01-15',
                'grade_submission_deadline'=> '2026-01-30',
                'is_active'               => true,
            ],
        ];

        foreach ($terms as $term) {
            DB::table('academic_terms')->updateOrInsert(
                ['academic_year' => $term['academic_year'], 'semester' => $term['semester']],
                array_merge($term, ['created_at' => $now, 'updated_at' => $now])
            );
        }
    }
}
