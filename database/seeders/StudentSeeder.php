<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $now      = now();
        $password = Hash::make('241996');
        $roleId   = DB::table('roles')->where('name', 'student')->value('id');
        $faculties = DB::table('faculties')->pluck('id', 'code');
        $depts     = DB::table('departments')->pluck('id', 'code');

        $students = [

            // =====================================================================
            // CSIT — immediate enrollment (students have a department from day 1)
            // =====================================================================
            [
                'national_id'    => '40000000000001',
                'first_name'     => 'Ali',
                'last_name'      => 'Mohsen',
                'email'          => 'ali.mohsen@student.unione.com',
                'gender'         => 'male',
                'student_number' => 'STU-2021-0001',
                'faculty_code'   => 'CSIT',
                'dept_code'      => 'CS',
                'academic_year'  => 4,
                'enrolled_at'    => '2021-09-15',
            ],
            [
                'national_id'    => '40000000000002',
                'first_name'     => 'Mariam',
                'last_name'      => 'Adel',
                'email'          => 'mariam.adel@student.unione.com',
                'gender'         => 'female',
                'student_number' => 'STU-2021-0002',
                'faculty_code'   => 'CSIT',
                'dept_code'      => 'CS',
                'academic_year'  => 4,
                'enrolled_at'    => '2021-09-15',
            ],
            [
                'national_id'    => '40000000000003',
                'first_name'     => 'Omar',
                'last_name'      => 'Tarek',
                'email'          => 'omar.tarek@student.unione.com',
                'gender'         => 'male',
                'student_number' => 'STU-2022-0001',
                'faculty_code'   => 'CSIT',
                'dept_code'      => 'AI',
                'academic_year'  => 3,
                'enrolled_at'    => '2022-09-15',
            ],
            [
                'national_id'    => '40000000000004',
                'first_name'     => 'Nour',
                'last_name'      => 'Samy',
                'email'          => 'nour.samy@student.unione.com',
                'gender'         => 'female',
                'student_number' => 'STU-2022-0002',
                'faculty_code'   => 'CSIT',
                'dept_code'      => 'CYB',
                'academic_year'  => 3,
                'enrolled_at'    => '2022-09-15',
            ],
            [
                'national_id'    => '40000000000005',
                'first_name'     => 'Youssef',
                'last_name'      => 'Nabil',
                'email'          => 'youssef.nabil@student.unione.com',
                'gender'         => 'male',
                'student_number' => 'STU-2023-0001',
                'faculty_code'   => 'CSIT',
                'dept_code'      => 'IS',
                'academic_year'  => 2,
                'enrolled_at'    => '2023-09-15',
            ],
            [
                'national_id'    => '40000000000006',
                'first_name'     => 'Lina',
                'last_name'      => 'Magdy',
                'email'          => 'lina.magdy@student.unione.com',
                'gender'         => 'female',
                'student_number' => 'STU-2024-0001',
                'faculty_code'   => 'CSIT',
                'dept_code'      => 'CS',
                'academic_year'  => 1,
                'enrolled_at'    => '2024-09-15',
            ],

            // =====================================================================
            // ENGINEERING — deferred enrollment
            // Year 1 students → preparatory dept (ENG-PREP)
            // Year 2+ students → specific dept
            // =====================================================================
            [
                'national_id'    => '40000000000007',
                'first_name'     => 'Hassan',
                'last_name'      => 'El-Badry',
                'email'          => 'hassan.elbadry@student.unione.com',
                'gender'         => 'male',
                'student_number' => 'STU-2021-0003',
                'faculty_code'   => 'ENG',
                'dept_code'      => 'CIVIL',
                'academic_year'  => 4,
                'enrolled_at'    => '2021-09-15',
            ],
            [
                'national_id'    => '40000000000008',
                'first_name'     => 'Donia',
                'last_name'      => 'Samir',
                'email'          => 'donia.samir@student.unione.com',
                'gender'         => 'female',
                'student_number' => 'STU-2022-0003',
                'faculty_code'   => 'ENG',
                'dept_code'      => 'ELEC',
                'academic_year'  => 3,
                'enrolled_at'    => '2022-09-15',
            ],
            [
                'national_id'    => '40000000000009',
                'first_name'     => 'Mahmoud',
                'last_name'      => 'Fares',
                'email'          => 'mahmoud.fares@student.unione.com',
                'gender'         => 'male',
                'student_number' => 'STU-2023-0002',
                'faculty_code'   => 'ENG',
                'dept_code'      => 'MECH',   // Picked dept in year 2
                'academic_year'  => 2,
                'enrolled_at'    => '2023-09-15',
            ],
            [
                'national_id'    => '40000000000010',
                'first_name'     => 'Aya',
                'last_name'      => 'Khalid',
                'email'          => 'aya.khalid@student.unione.com',
                'gender'         => 'female',
                'student_number' => 'STU-2024-0002',
                'faculty_code'   => 'ENG',
                'dept_code'      => 'ENG-PREP',  // Year 1 → preparatory
                'academic_year'  => 1,
                'enrolled_at'    => '2024-09-15',
            ],

            // =====================================================================
            // MEDICINE — none enrollment (dept_code = null)
            // =====================================================================
            [
                'national_id'    => '40000000000011',
                'first_name'     => 'Kareem',
                'last_name'      => 'Waheed',
                'email'          => 'kareem.waheed@student.unione.com',
                'gender'         => 'male',
                'student_number' => 'STU-2020-0001',
                'faculty_code'   => 'MED',
                'dept_code'      => null,
                'academic_year'  => 5,
                'enrolled_at'    => '2020-09-15',
            ],
            [
                'national_id'    => '40000000000012',
                'first_name'     => 'Salma',
                'last_name'      => 'Nasser',
                'email'          => 'salma.nasser@student.unione.com',
                'gender'         => 'female',
                'student_number' => 'STU-2021-0004',
                'faculty_code'   => 'MED',
                'dept_code'      => null,
                'academic_year'  => 4,
                'enrolled_at'    => '2021-09-15',
            ],
            [
                'national_id'    => '40000000000013',
                'first_name'     => 'Tamer',
                'last_name'      => 'Rizk',
                'email'          => 'tamer.rizk@student.unione.com',
                'gender'         => 'male',
                'student_number' => 'STU-2024-0003',
                'faculty_code'   => 'MED',
                'dept_code'      => null,
                'academic_year'  => 1,
                'enrolled_at'    => '2024-09-15',
            ],

            // =====================================================================
            // BUSINESS — deferred enrollment
            // =====================================================================
            [
                'national_id'    => '40000000000014',
                'first_name'     => 'Farida',
                'last_name'      => 'Hamdy',
                'email'          => 'farida.hamdy@student.unione.com',
                'gender'         => 'female',
                'student_number' => 'STU-2022-0004',
                'faculty_code'   => 'BUS',
                'dept_code'      => 'MKT',
                'academic_year'  => 3,
                'enrolled_at'    => '2022-09-15',
            ],
            [
                'national_id'    => '40000000000015',
                'first_name'     => 'Sami',
                'last_name'      => 'El-Erian',
                'email'          => 'sami.elerian@student.unione.com',
                'gender'         => 'male',
                'student_number' => 'STU-2023-0003',
                'faculty_code'   => 'BUS',
                'dept_code'      => 'BUS-FIN',  // Picked in year 2
                'academic_year'  => 2,
                'enrolled_at'    => '2023-09-15',
            ],
            [
                'national_id'    => '40000000000016',
                'first_name'     => 'Hana',
                'last_name'      => 'Essam',
                'email'          => 'hana.essam@student.unione.com',
                'gender'         => 'female',
                'student_number' => 'STU-2024-0004',
                'faculty_code'   => 'BUS',
                'dept_code'      => 'BUS-PREP',  // Year 1 → preparatory
                'academic_year'  => 1,
                'enrolled_at'    => '2024-09-15',
            ],

            // =====================================================================
            // LAW — none enrollment (dept_code = null)
            // =====================================================================
            [
                'national_id'    => '40000000000017',
                'first_name'     => 'Adam',
                'last_name'      => 'Fouad',
                'email'          => 'adam.fouad@student.unione.com',
                'gender'         => 'male',
                'student_number' => 'STU-2022-0005',
                'faculty_code'   => 'LAW',
                'dept_code'      => null,
                'academic_year'  => 3,
                'enrolled_at'    => '2022-09-15',
            ],
            [
                'national_id'    => '40000000000018',
                'first_name'     => 'Reem',
                'last_name'      => 'Ashour',
                'email'          => 'reem.ashour@student.unione.com',
                'gender'         => 'female',
                'student_number' => 'STU-2024-0005',
                'faculty_code'   => 'LAW',
                'dept_code'      => null,
                'academic_year'  => 1,
                'enrolled_at'    => '2024-09-15',
            ],
        ];

        foreach ($students as $data) {
            $userId = DB::table('users')->insertGetId([
                'national_id'       => $data['national_id'],
                'first_name'        => $data['first_name'],
                'last_name'         => $data['last_name'],
                'email'             => $data['email'],
                'password'          => $password,
                'gender'            => $data['gender'],
                'date_of_birth'     => '2002-08-10',
                'is_active'         => true,
                'email_verified_at' => $now,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);

            DB::table('role_user')->insert([
                'user_id'    => $userId,
                'role_id'    => $roleId,
                'granted_at' => $now,
            ]);

            DB::table('students')->insert([
                'user_id'           => $userId,
                'student_number'    => $data['student_number'],
                'faculty_id'        => $faculties[$data['faculty_code']],
                'department_id'     => $data['dept_code'] ? $depts[$data['dept_code']] : null,
                'academic_year'     => $data['academic_year'],
                'semester'          => 'first',
                'enrollment_status' => 'active',
                'enrolled_at'       => $data['enrolled_at'],
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
        }
    }
}
