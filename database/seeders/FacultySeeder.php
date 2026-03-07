<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FacultySeeder extends Seeder
{
    public function run(): void
    {
        $faculties = [
            [
                'name'            => 'Faculty of Computer Science & Information Technology',
                'name_ar'         => 'كلية علوم الحاسب وتكنولوجيا المعلومات',
                'code'            => 'CSIT',
                'enrollment_type' => 'immediate',   // Students pick a dept from day 1
            ],
            [
                'name'            => 'Faculty of Engineering',
                'name_ar'         => 'كلية الهندسة',
                'code'            => 'ENG',
                'enrollment_type' => 'deferred',    // General year 1, pick in year 2
            ],
            [
                'name'            => 'Faculty of Medicine',
                'name_ar'         => 'كلية الطب',
                'code'            => 'MED',
                'enrollment_type' => 'none',         // No departments for students
            ],
            [
                'name'            => 'Faculty of Business Administration',
                'name_ar'         => 'كلية إدارة الأعمال',
                'code'            => 'BUS',
                'enrollment_type' => 'deferred',    // General year 1, pick in year 2
            ],
            [
                'name'            => 'Faculty of Law',
                'name_ar'         => 'كلية الحقوق',
                'code'            => 'LAW',
                'enrollment_type' => 'none',         // No departments for students
            ],
        ];

        $now = now();
        foreach ($faculties as $faculty) {
            DB::table('faculties')->updateOrInsert(
                ['code' => $faculty['code']],
                array_merge($faculty, ['is_active' => true, 'created_at' => $now, 'updated_at' => $now])
            );
        }
    }
}
