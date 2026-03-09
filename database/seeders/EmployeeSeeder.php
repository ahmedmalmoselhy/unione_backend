<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    // Map dept_code â†’ ordered list of 5 job titles (index 0 = head)
    private array $deptJobs = [
        'HR'       => ['HR Director',            'HR Manager',            'Recruitment Specialist', 'HR Officer',           'Payroll Officer'],
        'FIN'      => ['Chief Financial Officer', 'Senior Accountant',     'Budget Analyst',         'Finance Officer',      'Payroll Officer'],
        'IT-MGMT'  => ['IT Director',             'Systems Administrator', 'Network Engineer',       'Technical Support',    'Software Developer'],
        'SA'       => ['Student Affairs Director','Student Counselor',     'Affairs Officer',        'Student Support Spec.','Welfare Officer'],
        'ADM'      => ['Registrar',               'Admissions Director',   'Admissions Officer',     'Records Officer',      'Archive Officer'],
        // Faculty-level managerial departments
        'CSIT-SC'  => ['Student Care Director',   'Welfare Coordinator',   'Support Officer',        'Wellness Coach',       'Care Officer'],
        'CSIT-SA'  => ['Student Affairs Director','Affairs Coordinator',   'Events Officer',         'Activities Officer',   'Admin Officer'],
        'CSIT-LGL' => ['Legal Affairs Director',  'Legal Counsel',         'Compliance Officer',     'Legal Officer',        'Contract Spec.'],
        'ENG-SC'   => ['Student Care Director',   'Welfare Coordinator',   'Support Officer',        'Wellness Coach',       'Care Officer'],
        'ENG-SA'   => ['Student Affairs Director','Affairs Coordinator',   'Events Officer',         'Activities Officer',   'Admin Officer'],
        'ENG-LGL'  => ['Legal Affairs Director',  'Legal Counsel',         'Compliance Officer',     'Legal Officer',        'Contract Spec.'],
        'MED-SC'   => ['Student Care Director',   'Welfare Coordinator',   'Support Officer',        'Wellness Coach',       'Care Officer'],
        'MED-SA'   => ['Student Affairs Director','Affairs Coordinator',   'Events Officer',         'Activities Officer',   'Admin Officer'],
        'MED-LGL'  => ['Legal Affairs Director',  'Legal Counsel',         'Compliance Officer',     'Legal Officer',        'Contract Spec.'],
        'BUS-SC'   => ['Student Care Director',   'Welfare Coordinator',   'Support Officer',        'Wellness Coach',       'Care Officer'],
        'BUS-SA'   => ['Student Affairs Director','Affairs Coordinator',   'Events Officer',         'Activities Officer',   'Admin Officer'],
        'BUS-LGL'  => ['Legal Affairs Director',  'Legal Counsel',         'Compliance Officer',     'Legal Officer',        'Contract Spec.'],
        'LAW-SC'   => ['Student Care Director',   'Welfare Coordinator',   'Support Officer',        'Wellness Coach',       'Care Officer'],
        'LAW-SA'   => ['Student Affairs Director','Affairs Coordinator',   'Events Officer',         'Activities Officer',   'Admin Officer'],
        'LAW-LGL'  => ['Legal Affairs Director',  'Legal Counsel',         'Compliance Officer',     'Legal Officer',        'Contract Spec.'],
    ];

    private array $maleFirst   = ['Ahmed','Mohamed','Khaled','Omar','Hossam','Tarek','Wael','Amr','Islam','Ziad','Karim','Fady','Amir','Mostafa','Hassan','Ramy','Bassem','Adel','Walid','Youssef'];
    private array $femaleFirst = ['Rania','Dina','Sara','Iman','Noha','Mona','Hala','Ghada','Yasmine','Ola','Nadia','Rasha','Salma','Enas','Farida','Nour','Mariam','Hana','Reem','Donia'];
    private array $lastNames   = ['Osman','Hanna','Tawfik','Shawky','Lotfy','Ashraf','Mustafa','Galal','Ramadan','Khaled','Fathy','Soliman','Wahba','ElMasry','Abdallah','Ibrahim','Naguib','Zaki','Yousef','Samir','Fouad','Gaber','Mansour','Kamal','Helmy'];

    private int $nationalIdCounter = 30000000000000;
    private int $staffCounter      = 0;

    public function run(): void
    {
        $now         = now();
        $password    = Hash::make('241996');
        $empRoleId   = DB::table('roles')->where('name', 'employee')->value('id');
        $headRoleId  = DB::table('roles')->where('name', 'department_head')->value('id');
        $depts       = DB::table('departments')->pluck('id', 'code');

        foreach ($this->deptJobs as $deptCode => $jobTitles) {
            if (! isset($depts[$deptCode])) {
                continue;
            }

            $deptId           = $depts[$deptCode];
            $firstEmpUserId   = null;

            foreach ($jobTitles as $idx => $jobTitle) {
                $this->nationalIdCounter++;
                $this->staffCounter++;

                $isFemale  = ($idx % 3 === 2);
                $firstName = $this->pick($isFemale ? $this->femaleFirst : $this->maleFirst, $deptCode . $idx . 'f');
                $lastName  = $this->pick($this->lastNames, $deptCode . $idx . 'l');
                $email     = strtolower(substr($firstName, 0, 1)) . '.' . strtolower($lastName) . $this->staffCounter . '@unione.com';
                $staffNum  = 'EMP-' . str_pad($this->staffCounter, 4, '0', STR_PAD_LEFT);
                $empType   = ($idx === 0) ? 'full_time' : (($idx === 4) ? 'part_time' : 'full_time');
                $salary    = ($idx === 0) ? rand(14000, 22000) : rand(5000, 12000);

                $userId = DB::table('users')->insertGetId([
                    'national_id'       => (string) $this->nationalIdCounter,
                    'first_name'        => $firstName,
                    'last_name'         => $lastName,
                    'email'             => $email,
                    'password'          => $password,
                    'gender'            => $isFemale ? 'female' : 'male',
                    'date_of_birth'     => (1985 - $idx) . '-0' . ($idx + 1) . '-15',
                    'is_active'         => true,
                    'email_verified_at' => $now,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]);

                DB::table('role_user')->insert([
                    'user_id'    => $userId,
                    'role_id'    => $empRoleId,
                    'granted_at' => $now,
                ]);

                DB::table('employees')->insert([
                    'user_id'         => $userId,
                    'staff_number'    => $staffNum,
                    'department_id'   => $deptId,
                    'job_title'       => $jobTitle,
                    'employment_type' => $empType,
                    'salary'          => $salary,
                    'hired_at'        => (2010 + $idx) . '-09-01',
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ]);

                if ($idx === 0) {
                    $firstEmpUserId = $userId;
                }
            }

            // First employee in each managerial dept becomes the department head
            if ($firstEmpUserId) {
                DB::table('role_user')->insert([
                    'user_id'       => $firstEmpUserId,
                    'role_id'       => $headRoleId,
                    'department_id' => $deptId,
                    'granted_at'    => $now,
                ]);
                DB::table('departments')->where('id', $deptId)->update(['head_id' => $firstEmpUserId]);
            }
        }
    }

    private function pick(array $pool, string $seed): string
    {
        return $pool[abs(crc32($seed)) % count($pool)];
    }
}
