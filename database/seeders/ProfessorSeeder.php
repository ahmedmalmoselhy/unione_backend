<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProfessorSeeder extends Seeder
{
    // â”€â”€ Name pools (Arabic/Egyptian) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    private array $maleFirst    = ['Ahmed','Mohamed','Khaled','Omar','Hossam','Tarek','Sherif','Walid','Amr','Ramy','Bassem','Adel','Islam','Ziad','Wael','Karim','Fady','Amir','Mostafa','Hassan'];
    private array $femaleFirst  = ['Rania','Dina','Sara','Iman','Noha','Mona','Hala','Ghada','Yasmine','Ola','Nadia','Rasha','Salma','Enas','Farida','Nour','Mariam','Hana','Reem','Donia'];
    private array $lastNames    = ['Farouk','ElSherif','Mansour','Kamal','Gaber','Helmy','Rizk','Fathy','Soliman','Wahba','ElMasry','Abdallah','Ibrahim','Naguib','Zaki','Yousef','Samir','Barakat','ElGohary','Fouad','Tawfik','Galal','Osman','Lotfy','Ashraf','Mustafa','Ramadan','Sobhy','Fawzy','Badawi'];
    private array $ranks        = ['professor','professor','associate_professor','associate_professor','assistant_professor','assistant_professor'];

    // â”€â”€ Dept-specific data â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    private array $deptMeta = [
        'CS'       => ['specializations' => ['Algorithms & Theory','Software Engineering','Computer Networks','Programming Languages','Human-Computer Interaction','Formal Methods'], 'building' => 'CSIT Building'],
        'IS'       => ['specializations' => ['Database Systems','Enterprise Architecture','Business Intelligence','ERP Systems','Information Security','Cloud Computing'], 'building' => 'CSIT Building'],
        'CYB'      => ['specializations' => ['Network Security','Cryptography','Penetration Testing','Digital Forensics','Malware Analysis','Security Governance'], 'building' => 'CSIT Building'],
        'AI'       => ['specializations' => ['Machine Learning','Deep Learning','Natural Language Processing','Computer Vision','Robotics','Reinforcement Learning'], 'building' => 'CSIT Building'],
        'CIVIL'    => ['specializations' => ['Structural Engineering','Geotechnical Engineering','Environmental Engineering','Transportation Engineering','Construction Management','Water Resources'], 'building' => 'Engineering Block A'],
        'ELEC'     => ['specializations' => ['Power Systems','Electronics & Circuits','Control Systems','Telecommunications','Signal Processing','Embedded Systems'], 'building' => 'Engineering Block B'],
        'MECH'     => ['specializations' => ['Thermodynamics','Fluid Mechanics','Manufacturing','Robotics & Automation','Materials Science','CAD/CAM'], 'building' => 'Engineering Block C'],
        'ARCH'     => ['specializations' => ['Urban Design','Architectural History','Sustainable Architecture','Interior Architecture','BIM & Digital Design','Landscape Architecture'], 'building' => 'Architecture Studio'],
        'MED-INT'  => ['specializations' => ['Cardiology','Endocrinology','Gastroenterology','Rheumatology','Pulmonology','Nephrology'], 'building' => 'Medical Complex'],
        'MED-SURG' => ['specializations' => ['General Surgery','Orthopedic Surgery','Neurosurgery','Cardiothoracic Surgery','Plastic Surgery','Vascular Surgery'], 'building' => 'Medical Complex'],
        'MED-PHAR' => ['specializations' => ['Clinical Pharmacology','Pharmacokinetics','Drug Discovery','Toxicology','Pharmacogenomics','Neuropharmacology'], 'building' => 'Medical Complex'],
        'MED-PATH' => ['specializations' => ['Histopathology','Clinical Pathology','Forensic Pathology','Molecular Pathology','Cytopathology','Immunopathology'], 'building' => 'Medical Complex'],
        'MKT'      => ['specializations' => ['Digital Marketing','Consumer Behavior','Brand Management','Market Research','International Marketing','Advertising'], 'building' => 'Business Tower'],
        'BUS-FIN'  => ['specializations' => ['Investment & Portfolio','Corporate Finance','Financial Derivatives','Banking','Risk Management','Islamic Finance'], 'building' => 'Business Tower'],
        'BUS-HR'   => ['specializations' => ['Organizational Behavior','Talent Management','Labor Relations','Training & Development','Performance Management','Compensation & Benefits'], 'building' => 'Business Tower'],
        'ACCT'     => ['specializations' => ['Financial Accounting','Managerial Accounting','Auditing','Tax Accounting','Cost Accounting','Forensic Accounting'], 'building' => 'Business Tower'],
        'LAW-PUB'  => ['specializations' => ['Constitutional Law','Administrative Law','International Public Law','Human Rights Law','Environmental Law','Tax Law'], 'building' => 'Law Building'],
        'LAW-PRI'  => ['specializations' => ['Commercial Law','Contract Law','Property Law','Family Law','Intellectual Property','Civil Procedure'], 'building' => 'Law Building'],
        'LAW-CRI'  => ['specializations' => ['Criminal Law','Criminal Procedure','Criminology','Juvenile Justice','Cyber Crime Law','International Criminal Law'], 'building' => 'Law Building'],
        'ENG-GEN'  => ['specializations' => ['Foundation Mathematics','Applied Physics','Engineering Drawing','Technical Communication','Introduction to Engineering','Computer-Aided Design'], 'building' => 'Engineering Block A'],
        'BUS-GEN'  => ['specializations' => ['Introduction to Business','Business Mathematics','Microeconomics','Business Communication','Office Administration','Organizational Management'], 'building' => 'Business Tower'],
        'MED-GEN'  => ['specializations' => ['Anatomy','Physiology','Biochemistry','Medical Ethics','Histology','Embryology'], 'building' => 'Medical Complex'],
        'LAW-GEN'  => ['specializations' => ['Introduction to Law','Legal Reasoning','Legal Research & Writing','Constitutional Foundations','History of Law','Comparative Law'], 'building' => 'Law Building'],
    ];

    // All departments should have professors (including general/holding departments)
    private array $noProfessorCodes = [];

    private int $nationalIdCounter = 20000000000000;
    private int $staffCounter      = 0;
    private int $userCounter       = 0;

    // Track by gender to alternate somewhat
    private array $maleIdx   = [];
    private array $femaleIdx = [];

    public function run(): void
    {
        $now      = now();
        $password = Hash::make('241996');
        $roleId   = DB::table('roles')->where('name', 'professor')->value('id');
        $deanRole = DB::table('roles')->where('name', 'dean')->value('id');
        $headRole = DB::table('roles')->where('name', 'department_head')->value('id');

        $departments = DB::table('departments')
            ->where('type', 'academic')
            ->whereNotNull('faculty_id')
            ->whereNotIn('code', $this->noProfessorCodes)
            ->orderBy('faculty_id')
            ->orderBy('id')
            ->get();

        $faculties = DB::table('faculties')->get()->keyBy('id');

        // Group depts by faculty (to track dean assignment)
        $deanAssigned = [];

        foreach ($departments as $dept) {
            $meta          = $this->deptMeta[$dept->code] ?? ['specializations' => ['General Studies'], 'building' => 'Main Building'];
            $specializations = $meta['specializations'];
            $building        = $meta['building'];
            $profCount       = count($specializations); // 6 per dept

            $firstProfUserId = null;

            for ($i = 0; $i < $profCount; $i++) {
                $isFemale  = ($i % 3 === 2); // roughly 1 in 3 is female
                $firstName = $this->pickName($isFemale ? 'female' : 'male', $dept->code . $i);
                $lastName  = $this->pickLastName($dept->code . $i . 'l');
                $this->nationalIdCounter++;
                $this->staffCounter++;

                $email      = strtolower(substr($firstName, 0, 1) . '.' . $lastName) . $this->staffCounter . '@unione.com';
                $staffNum   = 'PROF-' . str_pad($this->staffCounter, 4, '0', STR_PAD_LEFT);
                $rank       = $this->ranks[$i % count($this->ranks)];
                $hiredYear  = rand(2000, 2022);
                $dob        = ($hiredYear - rand(27, 40)) . '-' . str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT) . '-01';

                $userId = DB::table('users')->insertGetId([
                    'national_id'       => (string) $this->nationalIdCounter,
                    'first_name'        => $firstName,
                    'last_name'         => $lastName,
                    'email'             => $email,
                    'password'          => $password,
                    'gender'            => $isFemale ? 'female' : 'male',
                    'date_of_birth'     => $dob,
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

                DB::table('professors')->insert([
                    'user_id'         => $userId,
                    'staff_number'    => $staffNum,
                    'department_id'   => $dept->id,
                    'specialization'  => $specializations[$i],
                    'academic_rank'   => $rank,
                    'office_location' => $building . ', Office ' . ($i + 1) . '0' . $dept->id,
                    'hired_at'        => $hiredYear . '-09-01',
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ]);

                $professorId = DB::table('professors')->where('user_id', $userId)->value('id');

                if ($i === 0) {
                    $firstProfUserId  = $userId;
                    $firstProfId      = $professorId;
                }

                // First professor per dept becomes department head
                if ($i === 0) {
                    DB::table('role_user')->insert([
                        'user_id'       => $userId,
                        'role_id'       => $headRole,
                        'department_id' => $dept->id,
                        'granted_at'    => $now,
                    ]);
                    DB::table('departments')->where('id', $dept->id)->update(['head_id' => $userId]);
                }
            }

            // First professor in each faculty becomes the dean
            $fid = $dept->faculty_id;
            if ($firstProfUserId && ! isset($deanAssigned[$fid])) {
                $deanAssigned[$fid] = $firstProfUserId;
                DB::table('role_user')->insert([
                    'user_id'    => $firstProfUserId,
                    'role_id'    => $deanRole,
                    'faculty_id' => $fid,
                    'granted_at' => $now,
                ]);
                DB::table('faculties')->where('id', $fid)->update(['dean_id' => $firstProfUserId]);
            }
        }
    }

    private function pickName(string $gender, string $seed): string
    {
        $pool = $gender === 'female' ? $this->femaleFirst : $this->maleFirst;
        $idx  = abs(crc32($seed)) % count($pool);

        return $pool[$idx];
    }

    private function pickLastName(string $seed): string
    {
        $idx = abs(crc32($seed)) % count($this->lastNames);

        return $this->lastNames[$idx];
    }
}
