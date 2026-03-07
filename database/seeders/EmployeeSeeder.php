<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $now      = now();
        $password = Hash::make('241996');
        $roleId   = DB::table('roles')->where('name', 'employee')->value('id');
        $depts    = DB::table('departments')->pluck('id', 'code');

        $employees = [

            // ── Human Resources ─────────────────────────────────────────────────
            [
                'national_id'      => '30000000000001',
                'first_name'       => 'Magda',
                'last_name'        => 'Osman',
                'email'            => 'm.osman@unione.com',
                'gender'           => 'female',
                'dept_code'        => 'HR',
                'staff_number'     => 'EMP-0001',
                'job_title'        => 'HR Manager',
                'employment_type'  => 'full_time',
                'salary'           => 12000.00,
                'hired_at'         => '2015-03-01',
            ],
            [
                'national_id'      => '30000000000002',
                'first_name'       => 'Fady',
                'last_name'        => 'Hanna',
                'email'            => 'f.hanna@unione.com',
                'gender'           => 'male',
                'dept_code'        => 'HR',
                'staff_number'     => 'EMP-0002',
                'job_title'        => 'Recruitment Specialist',
                'employment_type'  => 'full_time',
                'salary'           => 7500.00,
                'hired_at'         => '2019-06-01',
            ],

            // ── Finance & Accounting ─────────────────────────────────────────────
            [
                'national_id'      => '30000000000003',
                'first_name'       => 'Hisham',
                'last_name'        => 'Tawfik',
                'email'            => 'h.tawfik@unione.com',
                'gender'           => 'male',
                'dept_code'        => 'FIN',
                'staff_number'     => 'EMP-0003',
                'job_title'        => 'Chief Financial Officer',
                'employment_type'  => 'full_time',
                'salary'           => 20000.00,
                'hired_at'         => '2010-01-01',
            ],
            [
                'national_id'      => '30000000000004',
                'first_name'       => 'Enas',
                'last_name'        => 'Abdel-Aziz',
                'email'            => 'e.abdelaziz@unione.com',
                'gender'           => 'female',
                'dept_code'        => 'FIN',
                'staff_number'     => 'EMP-0004',
                'job_title'        => 'Senior Accountant',
                'employment_type'  => 'full_time',
                'salary'           => 9000.00,
                'hired_at'         => '2017-09-01',
            ],
            [
                'national_id'      => '30000000000005',
                'first_name'       => 'Ziad',
                'last_name'        => 'Saeed',
                'email'            => 'z.saeed@unione.com',
                'gender'           => 'male',
                'dept_code'        => 'FIN',
                'staff_number'     => 'EMP-0005',
                'job_title'        => 'Payroll Officer',
                'employment_type'  => 'full_time',
                'salary'           => 7000.00,
                'hired_at'         => '2020-01-15',
            ],

            // ── Information Technology ───────────────────────────────────────────
            [
                'national_id'      => '30000000000006',
                'first_name'       => 'Wael',
                'last_name'        => 'Shawky',
                'email'            => 'w.shawky@unione.com',
                'gender'           => 'male',
                'dept_code'        => 'IT-MGMT',
                'staff_number'     => 'EMP-0006',
                'job_title'        => 'IT Director',
                'employment_type'  => 'full_time',
                'salary'           => 18000.00,
                'hired_at'         => '2012-05-01',
            ],
            [
                'national_id'      => '30000000000007',
                'first_name'       => 'Nadia',
                'last_name'        => 'Lotfy',
                'email'            => 'n.lotfy@unione.com',
                'gender'           => 'female',
                'dept_code'        => 'IT-MGMT',
                'staff_number'     => 'EMP-0007',
                'job_title'        => 'Systems Administrator',
                'employment_type'  => 'full_time',
                'salary'           => 10000.00,
                'hired_at'         => '2018-03-01',
            ],
            [
                'national_id'      => '30000000000008',
                'first_name'       => 'Karim',
                'last_name'        => 'Ashraf',
                'email'            => 'k.ashraf@unione.com',
                'gender'           => 'male',
                'dept_code'        => 'IT-MGMT',
                'staff_number'     => 'EMP-0008',
                'job_title'        => 'Help Desk Technician',
                'employment_type'  => 'full_time',
                'salary'           => 6000.00,
                'hired_at'         => '2021-09-01',
            ],

            // ── Student Affairs ──────────────────────────────────────────────────
            [
                'national_id'      => '30000000000009',
                'first_name'       => 'Ola',
                'last_name'        => 'Mustafa',
                'email'            => 'o.mustafa@unione.com',
                'gender'           => 'female',
                'dept_code'        => 'SA',
                'staff_number'     => 'EMP-0009',
                'job_title'        => 'Student Affairs Director',
                'employment_type'  => 'full_time',
                'salary'           => 14000.00,
                'hired_at'         => '2011-09-01',
            ],
            [
                'national_id'      => '30000000000010',
                'first_name'       => 'Islam',
                'last_name'        => 'Ramadan',
                'email'            => 'i.ramadan@unione.com',
                'gender'           => 'male',
                'dept_code'        => 'SA',
                'staff_number'     => 'EMP-0010',
                'job_title'        => 'Student Counselor',
                'employment_type'  => 'full_time',
                'salary'           => 7000.00,
                'hired_at'         => '2022-01-01',
            ],

            // ── Admissions & Registration ────────────────────────────────────────
            [
                'national_id'      => '30000000000011',
                'first_name'       => 'Rasha',
                'last_name'        => 'Galal',
                'email'            => 'r.galal@unione.com',
                'gender'           => 'female',
                'dept_code'        => 'ADM',
                'staff_number'     => 'EMP-0011',
                'job_title'        => 'Registrar',
                'employment_type'  => 'full_time',
                'salary'           => 13000.00,
                'hired_at'         => '2009-09-01',
            ],
            [
                'national_id'      => '30000000000012',
                'first_name'       => 'Amir',
                'last_name'        => 'Sobhy',
                'email'            => 'a.sobhy@unione.com',
                'gender'           => 'male',
                'dept_code'        => 'ADM',
                'staff_number'     => 'EMP-0012',
                'job_title'        => 'Admissions Officer',
                'employment_type'  => 'full_time',
                'salary'           => 7500.00,
                'hired_at'         => '2020-09-01',
            ],
            [
                'national_id'      => '30000000000013',
                'first_name'       => 'Salma',
                'last_name'        => 'Fawzy',
                'email'            => 's.fawzy@unione.com',
                'gender'           => 'female',
                'dept_code'        => 'ADM',
                'staff_number'     => 'EMP-0013',
                'job_title'        => 'Admissions Officer',
                'employment_type'  => 'part_time',
                'salary'           => 4000.00,
                'hired_at'         => '2023-02-01',
            ],
        ];

        foreach ($employees as $data) {
            $userId = DB::table('users')->insertGetId([
                'national_id'       => $data['national_id'],
                'first_name'        => $data['first_name'],
                'last_name'         => $data['last_name'],
                'email'             => $data['email'],
                'password'          => $password,
                'gender'            => $data['gender'],
                'date_of_birth'     => '1985-04-20',
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

            DB::table('employees')->insert([
                'user_id'         => $userId,
                'staff_number'    => $data['staff_number'],
                'department_id'   => $depts[$data['dept_code']],
                'job_title'       => $data['job_title'],
                'employment_type' => $data['employment_type'],
                'salary'          => $data['salary'],
                'hired_at'        => $data['hired_at'],
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
        }
    }
}
