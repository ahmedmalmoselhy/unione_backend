<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds students programmatically:
 *
 * CSIT (immediate, 4 depts Ã— 4 years Ã— 100 active + 40 grads per dept)
 * ENG  (deferred,  ENG-GEN yr1 Ã— 100 + 4 depts Ã— 3 yrs Ã— 100 active + 40 grads per dept)
 * BUS  (deferred,  BUS-GEN yr1 Ã— 100 + 4 depts Ã— 3 yrs Ã— 100 active + 40 grads per dept)
 * MED  (none,      5 yrs Ã— 50 active + 50 grads)
 * LAW  (none,      4 yrs Ã— 50 active + 40 grads)
 *
 * Total active â‰ˆ 3 350  |  Total grads â‰ˆ 570  |  Grand total â‰ˆ 3 920
 */
class StudentSeeder extends Seeder
{
    private array $maleFirst   = ['Ali','Omar','Youssef','Hassan','Karim','Tarek','Islam','Ahmad','Mostafa','Mohamed','Wael','Amr','Ramy','Adel','Ziad','Amir','Sherif','Khaled','Fady','Bassem','Mahmoud','Nader','Hossam','Sami','Raed'];
    private array $femaleFirst = ['Mariam','Nour','Lina','Sara','Rania','Dina','Hana','Noha','Ola','Yasmine','Reem','Salma','Farida','Hala','Enas','Ghada','Donia','Mona','Nadia','Rasha','Iman','Maram','Laila','Dalia','Heba'];
    private array $lastNames   = ['Mohsen','Adel','Tarek','Samy','Nabil','Magdy','ElBadry','Samir','Fouad','Kamal','Fathy','Wahba','Ramadan','Soliman','Zaki','Mansour','Abdallah','Ibrahim','Naguib','Yousef','Gaber','Helmy','Rizk','Barakat','Osman','ElMasry','Galal','Tawfik','Khaled','Ashraf'];

    private int $nationalIdCounter = 40000000000000;
    private int $studentCounter    = 0;

    public function run(): void
    {
        $now      = now();
        $password = Hash::make('241996');
        $roleId   = DB::table('roles')->where('name', 'student')->value('id');
        $faculties = DB::table('faculties')->pluck('id', 'code');
        $depts     = DB::table('departments')->pluck('id', 'code');

        // â”€â”€ CSIT (immediate) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        // 4 depts Ã— 4 years Ã— 100 = 1 600 active; 4 depts Ã— 40 grads = 160
        $csitDepts = ['CS', 'IS', 'CYB', 'AI'];
        foreach ($csitDepts as $deptCode) {
            for ($year = 1; $year <= 4; $year++) {
                $enrollYear = 2025 - $year;
                for ($n = 1; $n <= 100; $n++) {
                    $this->seedStudent($now, $password, $roleId,
                        $faculties['CSIT'], $depts[$deptCode],
                        $year, 'active', $enrollYear . '-09-15', null);
                }
            }
            // Graduated students
            for ($n = 1; $n <= 40; $n++) {
                $this->seedStudent($now, $password, $roleId,
                    $faculties['CSIT'], $depts[$deptCode],
                    4, 'graduated', '2019-09-15', '2023-06-30');
            }
        }

        // â”€â”€ ENG (deferred) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        // Year 1 â†’ ENG-GEN; years 2-4 â†’ specific dept
        // ENG-GEN: 1 Ã— 100 = 100; 4 depts Ã— 3 years Ã— 100 = 1 200; grads 4 Ã— 40 = 160
        for ($n = 1; $n <= 100; $n++) {
            $this->seedStudent($now, $password, $roleId,
                $faculties['ENG'], $depts['ENG-GEN'],
                1, 'active', '2024-09-15', null);
        }
        $engDepts = ['CIVIL', 'ELEC', 'MECH', 'ARCH'];
        foreach ($engDepts as $deptCode) {
            for ($year = 2; $year <= 4; $year++) {
                $enrollYear = 2025 - $year;
                for ($n = 1; $n <= 100; $n++) {
                    $this->seedStudent($now, $password, $roleId,
                        $faculties['ENG'], $depts[$deptCode],
                        $year, 'active', $enrollYear . '-09-15', null);
                }
            }
            for ($n = 1; $n <= 40; $n++) {
                $this->seedStudent($now, $password, $roleId,
                    $faculties['ENG'], $depts[$deptCode],
                    4, 'graduated', '2019-09-15', '2023-06-30');
            }
        }

        // â”€â”€ BUS (deferred) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        // Year 1 â†’ BUS-GEN; years 2-4 â†’ specific dept
        for ($n = 1; $n <= 100; $n++) {
            $this->seedStudent($now, $password, $roleId,
                $faculties['BUS'], $depts['BUS-GEN'],
                1, 'active', '2024-09-15', null);
        }
        $busDepts = ['MKT', 'BUS-FIN', 'BUS-HR', 'ACCT'];
        foreach ($busDepts as $deptCode) {
            for ($year = 2; $year <= 4; $year++) {
                $enrollYear = 2025 - $year;
                for ($n = 1; $n <= 100; $n++) {
                    $this->seedStudent($now, $password, $roleId,
                        $faculties['BUS'], $depts[$deptCode],
                        $year, 'active', $enrollYear . '-09-15', null);
                }
            }
            for ($n = 1; $n <= 40; $n++) {
                $this->seedStudent($now, $password, $roleId,
                    $faculties['BUS'], $depts[$deptCode],
                    4, 'graduated', '2019-09-15', '2023-06-30');
            }
        }

        // â”€â”€ MED (none â€” students have no dept) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        // 5 years Ã— 50 = 250 active; 50 grads
        for ($year = 1; $year <= 5; $year++) {
            for ($n = 1; $n <= 50; $n++) {
                $enrollYear = 2025 - $year;
                $this->seedStudent($now, $password, $roleId,
                    $faculties['MED'], $depts['MED-GEN'],
                    $year, 'active', $enrollYear . '-09-15', null);
            }
        }
        for ($n = 1; $n <= 50; $n++) {
            $this->seedStudent($now, $password, $roleId,
                $faculties['MED'], $depts['MED-GEN'],
                5, 'graduated', '2016-09-15', '2021-06-30');
        }

        // â”€â”€ LAW (none â€” students have no dept) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        // 4 years Ã— 50 = 200 active; 40 grads
        for ($year = 1; $year <= 4; $year++) {
            for ($n = 1; $n <= 50; $n++) {
                $enrollYear = 2025 - $year;
                $this->seedStudent($now, $password, $roleId,
                    $faculties['LAW'], $depts['LAW-GEN'],
                    $year, 'active', $enrollYear . '-09-15', null);
            }
        }
        for ($n = 1; $n <= 40; $n++) {
            $this->seedStudent($now, $password, $roleId,
                $faculties['LAW'], $depts['LAW-GEN'],
                4, 'graduated', '2019-09-15', '2023-06-30');
        }
    }

    private function seedStudent(
        mixed  $now,
        string $password,
        int    $roleId,
        int    $facultyId,
        ?int   $departmentId,
        int    $academicYear,
        string $status,
        string $enrolledAt,
        ?string $graduatedAt,
    ): void {
        $this->nationalIdCounter++;
        $this->studentCounter++;

        $isFemale   = ($this->studentCounter % 3 === 0);
        $firstName  = $this->pick($isFemale ? $this->femaleFirst : $this->maleFirst, 'fn' . $this->studentCounter);
        $lastName   = $this->pick($this->lastNames, 'ln' . $this->studentCounter);
        $email      = strtolower($firstName) . '.' . strtolower($lastName) . $this->studentCounter . '@student.unione.com';
        $studentNum = 'STU-' . str_pad($this->studentCounter, 7, '0', STR_PAD_LEFT);
        $dob        = (1995 + $academicYear) . '-' . str_pad(($this->studentCounter % 12) + 1, 2, '0', STR_PAD_LEFT) . '-10';

        $userId = DB::table('users')->insertGetId([
            'national_id'       => (string) $this->nationalIdCounter,
            'first_name'        => $firstName,
            'last_name'         => $lastName,
            'email'             => $email,
            'password'          => $password,
            'gender'            => $isFemale ? 'female' : 'male',
            'date_of_birth'     => $dob,
            'is_active'         => $status === 'active',
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
            'student_number'    => $studentNum,
            'faculty_id'        => $facultyId,
            'department_id'     => $departmentId,
            'academic_year'     => $academicYear,
            'semester'          => 'first',
            'enrollment_status' => $status,
            'gpa'               => $status === 'graduated' ? round(rand(250, 400) / 100, 2) : null,
            'enrolled_at'       => $enrolledAt,
            'graduated_at'      => $graduatedAt,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);
    }

    private function pick(array $pool, string $seed): string
    {
        return $pool[abs(crc32($seed)) % count($pool)];
    }
}
