<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UniversitySeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            UniversityAdminSeeder::class,
            FacultySeeder::class,
            DepartmentSeeder::class,
            CourseSeeder::class,
            ProfessorSeeder::class,
            UniversityVicePresidentSeeder::class,
            EmployeeSeeder::class,
            FacultyAdminSeeder::class,
            DepartmentAdminSeeder::class,
            StudentSeeder::class,
            AcademicTermSeeder::class,
            SectionSeeder::class,
            EnrollmentSeeder::class,
            GradeSeeder::class,
            AnnouncementSeeder::class,
        ]);
    }
}
