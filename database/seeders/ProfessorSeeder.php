<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProfessorSeeder extends Seeder
{
    public function run(): void
    {
        $now      = now();
        $password = Hash::make('241996');
        $roleId   = DB::table('roles')->where('name', 'professor')->value('id');
        $depts    = DB::table('departments')->pluck('id', 'code');

        $professors = [

            // ── CSIT / Computer Science ─────────────────────────────────────────
            [
                'national_id'   => '20000000000001',
                'first_name'    => 'Ahmed',
                'last_name'     => 'Farouk',
                'email'         => 'a.farouk@unione.com',
                'gender'        => 'male',
                'dept_code'     => 'CS',
                'staff_number'  => 'PROF-0001',
                'specialization'=> 'Algorithms & Data Structures',
                'rank'          => 'professor',
                'office'        => 'CSIT Building, Office 101',
                'hired_at'      => '2010-09-01',
            ],
            [
                'national_id'   => '20000000000002',
                'first_name'    => 'Rania',
                'last_name'     => 'El-Sherif',
                'email'         => 'r.elsherif@unione.com',
                'gender'        => 'female',
                'dept_code'     => 'CS',
                'staff_number'  => 'PROF-0002',
                'specialization'=> 'Software Engineering',
                'rank'          => 'associate_professor',
                'office'        => 'CSIT Building, Office 102',
                'hired_at'      => '2014-09-01',
            ],

            // ── CSIT / Information Systems ───────────────────────────────────────
            [
                'national_id'   => '20000000000003',
                'first_name'    => 'Tarek',
                'last_name'     => 'Mansour',
                'email'         => 't.mansour@unione.com',
                'gender'        => 'male',
                'dept_code'     => 'IS',
                'staff_number'  => 'PROF-0003',
                'specialization'=> 'Database Systems',
                'rank'          => 'associate_professor',
                'office'        => 'CSIT Building, Office 201',
                'hired_at'      => '2012-09-01',
            ],
            [
                'national_id'   => '20000000000004',
                'first_name'    => 'Dina',
                'last_name'     => 'Kamal',
                'email'         => 'd.kamal@unione.com',
                'gender'        => 'female',
                'dept_code'     => 'IS',
                'staff_number'  => 'PROF-0004',
                'specialization'=> 'Enterprise Resource Planning',
                'rank'          => 'assistant_professor',
                'office'        => 'CSIT Building, Office 202',
                'hired_at'      => '2018-09-01',
            ],

            // ── CSIT / Cybersecurity ─────────────────────────────────────────────
            [
                'national_id'   => '20000000000005',
                'first_name'    => 'Mostafa',
                'last_name'     => 'Gaber',
                'email'         => 'm.gaber@unione.com',
                'gender'        => 'male',
                'dept_code'     => 'CYB',
                'staff_number'  => 'PROF-0005',
                'specialization'=> 'Network Security',
                'rank'          => 'professor',
                'office'        => 'CSIT Building, Office 301',
                'hired_at'      => '2008-09-01',
            ],

            // ── CSIT / Artificial Intelligence ───────────────────────────────────
            [
                'national_id'   => '20000000000006',
                'first_name'    => 'Sara',
                'last_name'     => 'Helmy',
                'email'         => 's.helmy@unione.com',
                'gender'        => 'female',
                'dept_code'     => 'AI',
                'staff_number'  => 'PROF-0006',
                'specialization'=> 'Machine Learning & Deep Learning',
                'rank'          => 'professor',
                'office'        => 'CSIT Building, Office 401',
                'hired_at'      => '2011-09-01',
            ],

            // ── Engineering / Civil ──────────────────────────────────────────────
            [
                'national_id'   => '20000000000007',
                'first_name'    => 'Hossam',
                'last_name'     => 'Rizk',
                'email'         => 'h.rizk@unione.com',
                'gender'        => 'male',
                'dept_code'     => 'CIVIL',
                'staff_number'  => 'PROF-0007',
                'specialization'=> 'Structural Engineering',
                'rank'          => 'professor',
                'office'        => 'Engineering Building A, Office 101',
                'hired_at'      => '2005-09-01',
            ],
            [
                'national_id'   => '20000000000008',
                'first_name'    => 'Iman',
                'last_name'     => 'Fathy',
                'email'         => 'i.fathy@unione.com',
                'gender'        => 'female',
                'dept_code'     => 'CIVIL',
                'staff_number'  => 'PROF-0008',
                'specialization'=> 'Geotechnical Engineering',
                'rank'          => 'assistant_professor',
                'office'        => 'Engineering Building A, Office 102',
                'hired_at'      => '2019-09-01',
            ],

            // ── Engineering / Electrical ─────────────────────────────────────────
            [
                'national_id'   => '20000000000009',
                'first_name'    => 'Khaled',
                'last_name'     => 'Soliman',
                'email'         => 'k.soliman@unione.com',
                'gender'        => 'male',
                'dept_code'     => 'ELEC',
                'staff_number'  => 'PROF-0009',
                'specialization'=> 'Power Systems',
                'rank'          => 'associate_professor',
                'office'        => 'Engineering Building B, Office 101',
                'hired_at'      => '2013-09-01',
            ],

            // ── Engineering / Mechanical ─────────────────────────────────────────
            [
                'national_id'   => '20000000000010',
                'first_name'    => 'Amr',
                'last_name'     => 'Wahba',
                'email'         => 'a.wahba@unione.com',
                'gender'        => 'male',
                'dept_code'     => 'MECH',
                'staff_number'  => 'PROF-0010',
                'specialization'=> 'Thermodynamics & Heat Transfer',
                'rank'          => 'professor',
                'office'        => 'Engineering Building B, Office 201',
                'hired_at'      => '2007-09-01',
            ],

            // ── Engineering / Architecture ───────────────────────────────────────
            [
                'national_id'   => '20000000000011',
                'first_name'    => 'Noha',
                'last_name'     => 'El-Masry',
                'email'         => 'n.elmasry@unione.com',
                'gender'        => 'female',
                'dept_code'     => 'ARCH',
                'staff_number'  => 'PROF-0011',
                'specialization'=> 'Urban Design',
                'rank'          => 'associate_professor',
                'office'        => 'Architecture Studio, Office 101',
                'hired_at'      => '2015-09-01',
            ],

            // ── Medicine / Internal Medicine ─────────────────────────────────────
            [
                'national_id'   => '20000000000012',
                'first_name'    => 'Sherif',
                'last_name'     => 'Abdallah',
                'email'         => 's.abdallah@unione.com',
                'gender'        => 'male',
                'dept_code'     => 'MED-INT',
                'staff_number'  => 'PROF-0012',
                'specialization'=> 'Cardiology',
                'rank'          => 'professor',
                'office'        => 'Medical Building, Office 101',
                'hired_at'      => '2003-09-01',
            ],
            [
                'national_id'   => '20000000000013',
                'first_name'    => 'Mona',
                'last_name'     => 'Ibrahim',
                'email'         => 'm.ibrahim@unione.com',
                'gender'        => 'female',
                'dept_code'     => 'MED-INT',
                'staff_number'  => 'PROF-0013',
                'specialization'=> 'Endocrinology',
                'rank'          => 'associate_professor',
                'office'        => 'Medical Building, Office 102',
                'hired_at'      => '2012-09-01',
            ],

            // ── Medicine / Surgery ───────────────────────────────────────────────
            [
                'national_id'   => '20000000000014',
                'first_name'    => 'Walid',
                'last_name'     => 'Naguib',
                'email'         => 'w.naguib@unione.com',
                'gender'        => 'male',
                'dept_code'     => 'MED-SURG',
                'staff_number'  => 'PROF-0014',
                'specialization'=> 'General Surgery',
                'rank'          => 'professor',
                'office'        => 'Medical Building, Office 201',
                'hired_at'      => '2000-09-01',
            ],

            // ── Medicine / Pharmacology ──────────────────────────────────────────
            [
                'national_id'   => '20000000000015',
                'first_name'    => 'Hala',
                'last_name'     => 'Zaki',
                'email'         => 'h.zaki@unione.com',
                'gender'        => 'female',
                'dept_code'     => 'MED-PHAR',
                'staff_number'  => 'PROF-0015',
                'specialization'=> 'Clinical Pharmacology',
                'rank'          => 'associate_professor',
                'office'        => 'Medical Building, Office 301',
                'hired_at'      => '2014-09-01',
            ],

            // ── Business / Marketing ─────────────────────────────────────────────
            [
                'national_id'   => '20000000000016',
                'first_name'    => 'Bassem',
                'last_name'     => 'Yousef',
                'email'         => 'b.yousef@unione.com',
                'gender'        => 'male',
                'dept_code'     => 'MKT',
                'staff_number'  => 'PROF-0016',
                'specialization'=> 'Digital Marketing',
                'rank'          => 'assistant_professor',
                'office'        => 'Business Building, Office 101',
                'hired_at'      => '2020-09-01',
            ],

            // ── Business / Finance & Banking ─────────────────────────────────────
            [
                'national_id'   => '20000000000017',
                'first_name'    => 'Ghada',
                'last_name'     => 'Samir',
                'email'         => 'g.samir@unione.com',
                'gender'        => 'female',
                'dept_code'     => 'BUS-FIN',
                'staff_number'  => 'PROF-0017',
                'specialization'=> 'Investment & Portfolio Management',
                'rank'          => 'professor',
                'office'        => 'Business Building, Office 201',
                'hired_at'      => '2006-09-01',
            ],

            // ── Business / HRM ───────────────────────────────────────────────────
            [
                'national_id'   => '20000000000018',
                'first_name'    => 'Adel',
                'last_name'     => 'Barakat',
                'email'         => 'a.barakat@unione.com',
                'gender'        => 'male',
                'dept_code'     => 'BUS-HR',
                'staff_number'  => 'PROF-0018',
                'specialization'=> 'Organizational Behavior',
                'rank'          => 'associate_professor',
                'office'        => 'Business Building, Office 301',
                'hired_at'      => '2016-09-01',
            ],

            // ── Law / Public Law ─────────────────────────────────────────────────
            [
                'national_id'   => '20000000000019',
                'first_name'    => 'Ramy',
                'last_name'     => 'El-Gohary',
                'email'         => 'r.elgohary@unione.com',
                'gender'        => 'male',
                'dept_code'     => 'LAW-PUB',
                'staff_number'  => 'PROF-0019',
                'specialization'=> 'Constitutional Law',
                'rank'          => 'professor',
                'office'        => 'Law Building, Office 101',
                'hired_at'      => '2004-09-01',
            ],

            // ── Law / Private Law ────────────────────────────────────────────────
            [
                'national_id'   => '20000000000020',
                'first_name'    => 'Yasmine',
                'last_name'     => 'Fouad',
                'email'         => 'y.fouad@unione.com',
                'gender'        => 'female',
                'dept_code'     => 'LAW-PRI',
                'staff_number'  => 'PROF-0020',
                'specialization'=> 'Commercial & Contract Law',
                'rank'          => 'associate_professor',
                'office'        => 'Law Building, Office 201',
                'hired_at'      => '2017-09-01',
            ],
        ];

        foreach ($professors as $data) {
            // Create user
            $userId = DB::table('users')->insertGetId([
                'national_id'       => $data['national_id'],
                'first_name'        => $data['first_name'],
                'last_name'         => $data['last_name'],
                'email'             => $data['email'],
                'password'          => $password,
                'gender'            => $data['gender'],
                'date_of_birth'     => '1975-06-15',
                'is_active'         => true,
                'email_verified_at' => $now,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);

            // Assign professor role
            DB::table('role_user')->insert([
                'user_id'    => $userId,
                'role_id'    => $roleId,
                'granted_at' => $now,
            ]);

            // Create professor profile
            DB::table('professors')->insert([
                'user_id'        => $userId,
                'staff_number'   => $data['staff_number'],
                'department_id'  => $depts[$data['dept_code']],
                'specialization' => $data['specialization'],
                'academic_rank'  => $data['rank'],
                'office_location'=> $data['office'],
                'hired_at'       => $data['hired_at'],
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }
    }
}
