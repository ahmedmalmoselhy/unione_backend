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
            FacultySeeder::class,
            DepartmentSeeder::class,
            CourseSeeder::class,
            ProfessorSeeder::class,
            EmployeeSeeder::class,
            StudentSeeder::class,
            AcademicTermSeeder::class,
            SectionSeeder::class,
            EnrollmentSeeder::class,
        ]);
    }
}
