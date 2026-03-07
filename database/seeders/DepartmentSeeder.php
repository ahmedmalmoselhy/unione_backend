<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $faculties = DB::table('faculties')->pluck('id', 'code');

        $departments = [

            // =====================================================================
            // UNIVERSITY-LEVEL MANAGEMENT (scope: university, no faculty_id)
            // =====================================================================
            [
                'faculty_id'      => null,
                'name'            => 'Human Resources Department',
                'name_ar'         => 'إدارة الموارد البشرية',
                'code'            => 'HR',
                'type'            => 'managerial',
                'scope'           => 'university',
                'is_preparatory'  => false,
            ],
            [
                'faculty_id'      => null,
                'name'            => 'Finance & Accounting Department',
                'name_ar'         => 'إدارة المالية والمحاسبة',
                'code'            => 'FIN',
                'type'            => 'managerial',
                'scope'           => 'university',
                'is_preparatory'  => false,
            ],
            [
                'faculty_id'      => null,
                'name'            => 'Information Technology Department',
                'name_ar'         => 'إدارة تقنية المعلومات',
                'code'            => 'IT-MGMT',
                'type'            => 'managerial',
                'scope'           => 'university',
                'is_preparatory'  => false,
            ],
            [
                'faculty_id'      => null,
                'name'            => 'Student Affairs Department',
                'name_ar'         => 'إدارة شؤون الطلاب',
                'code'            => 'SA',
                'type'            => 'managerial',
                'scope'           => 'university',
                'is_preparatory'  => false,
            ],
            [
                'faculty_id'      => null,
                'name'            => 'Admissions & Registration Department',
                'name_ar'         => 'إدارة القبول والتسجيل',
                'code'            => 'ADM',
                'type'            => 'managerial',
                'scope'           => 'university',
                'is_preparatory'  => false,
            ],

            // =====================================================================
            // CSIT — immediate enrollment (no preparatory dept needed)
            // =====================================================================
            [
                'faculty_id'     => $faculties['CSIT'],
                'name'           => 'Department of Computer Science',
                'name_ar'        => 'قسم علوم الحاسب',
                'code'           => 'CS',
                'type'           => 'academic',
                'scope'          => 'faculty',
                'is_preparatory' => false,
            ],
            [
                'faculty_id'     => $faculties['CSIT'],
                'name'           => 'Department of Information Systems',
                'name_ar'        => 'قسم نظم المعلومات',
                'code'           => 'IS',
                'type'           => 'academic',
                'scope'          => 'faculty',
                'is_preparatory' => false,
            ],
            [
                'faculty_id'     => $faculties['CSIT'],
                'name'           => 'Department of Cybersecurity',
                'name_ar'        => 'قسم الأمن السيبراني',
                'code'           => 'CYB',
                'type'           => 'academic',
                'scope'          => 'faculty',
                'is_preparatory' => false,
            ],
            [
                'faculty_id'     => $faculties['CSIT'],
                'name'           => 'Department of Artificial Intelligence',
                'name_ar'        => 'قسم الذكاء الاصطناعي',
                'code'           => 'AI',
                'type'           => 'academic',
                'scope'          => 'faculty',
                'is_preparatory' => false,
            ],

            // =====================================================================
            // ENGINEERING — deferred enrollment (has preparatory dept)
            // =====================================================================
            [
                'faculty_id'     => $faculties['ENG'],
                'name'           => 'General Engineering Studies',
                'name_ar'        => 'الدراسات الهندسية العامة',
                'code'           => 'ENG-PREP',
                'type'           => 'academic',
                'scope'          => 'faculty',
                'is_preparatory' => true,       // Year 1 catch-all
            ],
            [
                'faculty_id'     => $faculties['ENG'],
                'name'           => 'Department of Civil Engineering',
                'name_ar'        => 'قسم الهندسة المدنية',
                'code'           => 'CIVIL',
                'type'           => 'academic',
                'scope'          => 'faculty',
                'is_preparatory' => false,
            ],
            [
                'faculty_id'     => $faculties['ENG'],
                'name'           => 'Department of Electrical Engineering',
                'name_ar'        => 'قسم الهندسة الكهربائية',
                'code'           => 'ELEC',
                'type'           => 'academic',
                'scope'          => 'faculty',
                'is_preparatory' => false,
            ],
            [
                'faculty_id'     => $faculties['ENG'],
                'name'           => 'Department of Mechanical Engineering',
                'name_ar'        => 'قسم الهندسة الميكانيكية',
                'code'           => 'MECH',
                'type'           => 'academic',
                'scope'          => 'faculty',
                'is_preparatory' => false,
            ],
            [
                'faculty_id'     => $faculties['ENG'],
                'name'           => 'Department of Architecture',
                'name_ar'        => 'قسم العمارة',
                'code'           => 'ARCH',
                'type'           => 'academic',
                'scope'          => 'faculty',
                'is_preparatory' => false,
            ],

            // =====================================================================
            // MEDICINE — none enrollment (no student departments)
            // Departments exist for professors only
            // =====================================================================
            [
                'faculty_id'     => $faculties['MED'],
                'name'           => 'Department of Internal Medicine',
                'name_ar'        => 'قسم الطب الباطني',
                'code'           => 'MED-INT',
                'type'           => 'academic',
                'scope'          => 'faculty',
                'is_preparatory' => false,
            ],
            [
                'faculty_id'     => $faculties['MED'],
                'name'           => 'Department of Surgery',
                'name_ar'        => 'قسم الجراحة',
                'code'           => 'MED-SURG',
                'type'           => 'academic',
                'scope'          => 'faculty',
                'is_preparatory' => false,
            ],
            [
                'faculty_id'     => $faculties['MED'],
                'name'           => 'Department of Pharmacology',
                'name_ar'        => 'قسم علم الأدوية',
                'code'           => 'MED-PHAR',
                'type'           => 'academic',
                'scope'          => 'faculty',
                'is_preparatory' => false,
            ],

            // =====================================================================
            // BUSINESS — deferred enrollment (has preparatory dept)
            // =====================================================================
            [
                'faculty_id'     => $faculties['BUS'],
                'name'           => 'General Business Studies',
                'name_ar'        => 'الدراسات التجارية العامة',
                'code'           => 'BUS-PREP',
                'type'           => 'academic',
                'scope'          => 'faculty',
                'is_preparatory' => true,       // Year 1 catch-all
            ],
            [
                'faculty_id'     => $faculties['BUS'],
                'name'           => 'Department of Marketing',
                'name_ar'        => 'قسم التسويق',
                'code'           => 'MKT',
                'type'           => 'academic',
                'scope'          => 'faculty',
                'is_preparatory' => false,
            ],
            [
                'faculty_id'     => $faculties['BUS'],
                'name'           => 'Department of Finance & Banking',
                'name_ar'        => 'قسم المالية والمصرفية',
                'code'           => 'BUS-FIN',
                'type'           => 'academic',
                'scope'          => 'faculty',
                'is_preparatory' => false,
            ],
            [
                'faculty_id'     => $faculties['BUS'],
                'name'           => 'Department of Human Resource Management',
                'name_ar'        => 'قسم إدارة الموارد البشرية',
                'code'           => 'BUS-HR',
                'type'           => 'academic',
                'scope'          => 'faculty',
                'is_preparatory' => false,
            ],

            // =====================================================================
            // LAW — none enrollment (no student departments)
            // =====================================================================
            [
                'faculty_id'     => $faculties['LAW'],
                'name'           => 'Department of Public Law',
                'name_ar'        => 'قسم القانون العام',
                'code'           => 'LAW-PUB',
                'type'           => 'academic',
                'scope'          => 'faculty',
                'is_preparatory' => false,
            ],
            [
                'faculty_id'     => $faculties['LAW'],
                'name'           => 'Department of Private Law',
                'name_ar'        => 'قسم القانون الخاص',
                'code'           => 'LAW-PRI',
                'type'           => 'academic',
                'scope'          => 'faculty',
                'is_preparatory' => false,
            ],
        ];

        foreach ($departments as $dept) {
            DB::table('departments')->updateOrInsert(
                ['code' => $dept['code']],
                array_merge($dept, ['is_active' => true, 'created_at' => $now, 'updated_at' => $now])
            );
        }
    }
}
