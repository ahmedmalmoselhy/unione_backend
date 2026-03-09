<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $now      = now();
        $faculties = DB::table('faculties')->pluck('id', 'code');

        $departments = [

            // =====================================================================
            // UNIVERSITY-LEVEL MANAGEMENT (scope: university, no faculty_id)
            // =====================================================================
            ['faculty_id' => null, 'name' => 'Human Resources',            'name_ar' => 'الموارد البشرية',              'code' => 'HR',      'type' => 'managerial', 'scope' => 'university', 'is_preparatory' => false, 'is_mandatory' => false],
            ['faculty_id' => null, 'name' => 'Finance & Accounting',        'name_ar' => 'المالية والمحاسبة',            'code' => 'FIN',     'type' => 'managerial', 'scope' => 'university', 'is_preparatory' => false, 'is_mandatory' => false],
            ['faculty_id' => null, 'name' => 'Information Technology',      'name_ar' => 'تقنية المعلومات',              'code' => 'IT-MGMT', 'type' => 'managerial', 'scope' => 'university', 'is_preparatory' => false, 'is_mandatory' => false],
            ['faculty_id' => null, 'name' => 'Student Affairs',             'name_ar' => 'شؤون الطلاب',                  'code' => 'SA',      'type' => 'managerial', 'scope' => 'university', 'is_preparatory' => false, 'is_mandatory' => false],
            ['faculty_id' => null, 'name' => 'Admissions & Registration',   'name_ar' => 'القبول والتسجيل',             'code' => 'ADM',     'type' => 'managerial', 'scope' => 'university', 'is_preparatory' => false, 'is_mandatory' => false],

            // =====================================================================
            // CSIT — immediate enrollment
            // Mandatory managerial + 4 academic departments
            // =====================================================================
            ['faculty_id' => $faculties['CSIT'], 'name' => 'Students Care',    'name_ar' => 'رعاية الطلاب',       'code' => 'CSIT-SC',  'type' => 'managerial', 'scope' => 'faculty', 'is_preparatory' => false, 'is_mandatory' => true],
            ['faculty_id' => $faculties['CSIT'], 'name' => 'Students Affairs', 'name_ar' => 'شؤون الطلاب',        'code' => 'CSIT-SA',  'type' => 'managerial', 'scope' => 'faculty', 'is_preparatory' => false, 'is_mandatory' => true],
            ['faculty_id' => $faculties['CSIT'], 'name' => 'Legal',            'name_ar' => 'الشؤون القانونية',   'code' => 'CSIT-LGL', 'type' => 'managerial', 'scope' => 'faculty', 'is_preparatory' => false, 'is_mandatory' => true],

            ['faculty_id' => $faculties['CSIT'], 'name' => 'Computer Science',        'name_ar' => 'علوم الحاسب',           'code' => 'CS',  'type' => 'academic', 'scope' => 'faculty', 'is_preparatory' => false, 'is_mandatory' => false],
            ['faculty_id' => $faculties['CSIT'], 'name' => 'Information Systems',     'name_ar' => 'نظم المعلومات',         'code' => 'IS',  'type' => 'academic', 'scope' => 'faculty', 'is_preparatory' => false, 'is_mandatory' => false],
            ['faculty_id' => $faculties['CSIT'], 'name' => 'Cybersecurity',           'name_ar' => 'الأمن السيبراني',       'code' => 'CYB', 'type' => 'academic', 'scope' => 'faculty', 'is_preparatory' => false, 'is_mandatory' => false],
            ['faculty_id' => $faculties['CSIT'], 'name' => 'Artificial Intelligence', 'name_ar' => 'الذكاء الاصطناعي',     'code' => 'AI',  'type' => 'academic', 'scope' => 'faculty', 'is_preparatory' => false, 'is_mandatory' => false],

            // =====================================================================
            // ENGINEERING — deferred enrollment
            // Mandatory managerial + General (year-1 holding) + 4 specific academic depts
            // =====================================================================
            ['faculty_id' => $faculties['ENG'], 'name' => 'Students Care',    'name_ar' => 'رعاية الطلاب',       'code' => 'ENG-SC',  'type' => 'managerial', 'scope' => 'faculty', 'is_preparatory' => false, 'is_mandatory' => true],
            ['faculty_id' => $faculties['ENG'], 'name' => 'Students Affairs', 'name_ar' => 'شؤون الطلاب',        'code' => 'ENG-SA',  'type' => 'managerial', 'scope' => 'faculty', 'is_preparatory' => false, 'is_mandatory' => true],
            ['faculty_id' => $faculties['ENG'], 'name' => 'Legal',            'name_ar' => 'الشؤون القانونية',   'code' => 'ENG-LGL', 'type' => 'managerial', 'scope' => 'faculty', 'is_preparatory' => false, 'is_mandatory' => true],
            ['faculty_id' => $faculties['ENG'], 'name' => 'General',          'name_ar' => 'القسم العام',         'code' => 'ENG-GEN', 'type' => 'academic',   'scope' => 'faculty', 'is_preparatory' => false, 'is_mandatory' => true],

            ['faculty_id' => $faculties['ENG'], 'name' => 'Civil Engineering',      'name_ar' => 'الهندسة المدنية',       'code' => 'CIVIL', 'type' => 'academic', 'scope' => 'faculty', 'is_preparatory' => false, 'is_mandatory' => false],
            ['faculty_id' => $faculties['ENG'], 'name' => 'Electrical Engineering', 'name_ar' => 'الهندسة الكهربائية',   'code' => 'ELEC',  'type' => 'academic', 'scope' => 'faculty', 'is_preparatory' => false, 'is_mandatory' => false],
            ['faculty_id' => $faculties['ENG'], 'name' => 'Mechanical Engineering', 'name_ar' => 'الهندسة الميكانيكية',  'code' => 'MECH',  'type' => 'academic', 'scope' => 'faculty', 'is_preparatory' => false, 'is_mandatory' => false],
            ['faculty_id' => $faculties['ENG'], 'name' => 'Architecture',           'name_ar' => 'العمارة والتصميم',      'code' => 'ARCH',  'type' => 'academic', 'scope' => 'faculty', 'is_preparatory' => false, 'is_mandatory' => false],

            // =====================================================================
            // MEDICINE — none enrollment (departments are for professors only)
            // =====================================================================
            ['faculty_id' => $faculties['MED'], 'name' => 'Students Care',    'name_ar' => 'رعاية الطلاب',       'code' => 'MED-SC',  'type' => 'managerial', 'scope' => 'faculty', 'is_preparatory' => false, 'is_mandatory' => true],
            ['faculty_id' => $faculties['MED'], 'name' => 'Students Affairs', 'name_ar' => 'شؤون الطلاب',        'code' => 'MED-SA',  'type' => 'managerial', 'scope' => 'faculty', 'is_preparatory' => false, 'is_mandatory' => true],
            ['faculty_id' => $faculties['MED'], 'name' => 'Legal',            'name_ar' => 'الشؤون القانونية',   'code' => 'MED-LGL', 'type' => 'managerial', 'scope' => 'faculty', 'is_preparatory' => false, 'is_mandatory' => true],

            ['faculty_id' => $faculties['MED'], 'name' => 'General',          'name_ar' => 'القسم العام',         'code' => 'MED-GEN',  'type' => 'academic',   'scope' => 'faculty', 'is_preparatory' => false, 'is_mandatory' => true],
            ['faculty_id' => $faculties['MED'], 'name' => 'Internal Medicine', 'name_ar' => 'الطب الباطني',       'code' => 'MED-INT',  'type' => 'academic', 'scope' => 'faculty', 'is_preparatory' => false, 'is_mandatory' => false],
            ['faculty_id' => $faculties['MED'], 'name' => 'Surgery',           'name_ar' => 'الجراحة',            'code' => 'MED-SURG', 'type' => 'academic', 'scope' => 'faculty', 'is_preparatory' => false, 'is_mandatory' => false],
            ['faculty_id' => $faculties['MED'], 'name' => 'Pharmacology',      'name_ar' => 'علم الأدوية',        'code' => 'MED-PHAR', 'type' => 'academic', 'scope' => 'faculty', 'is_preparatory' => false, 'is_mandatory' => false],
            ['faculty_id' => $faculties['MED'], 'name' => 'Pathology',         'name_ar' => 'علم الأمراض',        'code' => 'MED-PATH', 'type' => 'academic', 'scope' => 'faculty', 'is_preparatory' => false, 'is_mandatory' => false],

            // =====================================================================
            // BUSINESS — deferred enrollment
            // =====================================================================
            ['faculty_id' => $faculties['BUS'], 'name' => 'Students Care',    'name_ar' => 'رعاية الطلاب',       'code' => 'BUS-SC',  'type' => 'managerial', 'scope' => 'faculty', 'is_preparatory' => false, 'is_mandatory' => true],
            ['faculty_id' => $faculties['BUS'], 'name' => 'Students Affairs', 'name_ar' => 'شؤون الطلاب',        'code' => 'BUS-SA',  'type' => 'managerial', 'scope' => 'faculty', 'is_preparatory' => false, 'is_mandatory' => true],
            ['faculty_id' => $faculties['BUS'], 'name' => 'Legal',            'name_ar' => 'الشؤون القانونية',   'code' => 'BUS-LGL', 'type' => 'managerial', 'scope' => 'faculty', 'is_preparatory' => false, 'is_mandatory' => true],
            ['faculty_id' => $faculties['BUS'], 'name' => 'General',          'name_ar' => 'القسم العام',         'code' => 'BUS-GEN', 'type' => 'academic',   'scope' => 'faculty', 'is_preparatory' => false, 'is_mandatory' => true],

            ['faculty_id' => $faculties['BUS'], 'name' => 'Marketing',            'name_ar' => 'التسويق',                   'code' => 'MKT',     'type' => 'academic', 'scope' => 'faculty', 'is_preparatory' => false, 'is_mandatory' => false],
            ['faculty_id' => $faculties['BUS'], 'name' => 'Finance & Banking',    'name_ar' => 'المالية والمصرفية',         'code' => 'BUS-FIN', 'type' => 'academic', 'scope' => 'faculty', 'is_preparatory' => false, 'is_mandatory' => false],
            ['faculty_id' => $faculties['BUS'], 'name' => 'Human Resource Mgmt', 'name_ar' => 'إدارة الموارد البشرية',     'code' => 'BUS-HR',  'type' => 'academic', 'scope' => 'faculty', 'is_preparatory' => false, 'is_mandatory' => false],
            ['faculty_id' => $faculties['BUS'], 'name' => 'Accounting',           'name_ar' => 'المحاسبة',                  'code' => 'ACCT',    'type' => 'academic', 'scope' => 'faculty', 'is_preparatory' => false, 'is_mandatory' => false],

            // =====================================================================
            // LAW — none enrollment
            // =====================================================================
            ['faculty_id' => $faculties['LAW'], 'name' => 'Students Care',    'name_ar' => 'رعاية الطلاب',       'code' => 'LAW-SC',  'type' => 'managerial', 'scope' => 'faculty', 'is_preparatory' => false, 'is_mandatory' => true],
            ['faculty_id' => $faculties['LAW'], 'name' => 'Students Affairs', 'name_ar' => 'شؤون الطلاب',        'code' => 'LAW-SA',  'type' => 'managerial', 'scope' => 'faculty', 'is_preparatory' => false, 'is_mandatory' => true],
            ['faculty_id' => $faculties['LAW'], 'name' => 'Legal',            'name_ar' => 'الشؤون القانونية',   'code' => 'LAW-LGL', 'type' => 'managerial', 'scope' => 'faculty', 'is_preparatory' => false, 'is_mandatory' => true],
            ['faculty_id' => $faculties['LAW'], 'name' => 'General',          'name_ar' => 'القسم العام',         'code' => 'LAW-GEN', 'type' => 'academic',   'scope' => 'faculty', 'is_preparatory' => false, 'is_mandatory' => true],

            ['faculty_id' => $faculties['LAW'], 'name' => 'Public Law',    'name_ar' => 'القانون العام',    'code' => 'LAW-PUB', 'type' => 'academic', 'scope' => 'faculty', 'is_preparatory' => false, 'is_mandatory' => false],
            ['faculty_id' => $faculties['LAW'], 'name' => 'Private Law',   'name_ar' => 'القانون الخاص',   'code' => 'LAW-PRI', 'type' => 'academic', 'scope' => 'faculty', 'is_preparatory' => false, 'is_mandatory' => false],
            ['faculty_id' => $faculties['LAW'], 'name' => 'Criminal Law',  'name_ar' => 'القانون الجنائي', 'code' => 'LAW-CRI', 'type' => 'academic', 'scope' => 'faculty', 'is_preparatory' => false, 'is_mandatory' => false],
        ];

        foreach ($departments as $dept) {
            DB::table('departments')->updateOrInsert(
                ['code' => $dept['code']],
                array_merge($dept, ['is_active' => true, 'created_at' => $now, 'updated_at' => $now])
            );
        }
    }
}