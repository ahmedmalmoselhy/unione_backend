<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UniversityVicePresidentSeeder extends Seeder
{
    public function run(): void
    {
        $now        = now();
        $universityId = DB::table('universities')->value('id');

        // Pick 4 professors by staff number to be vice presidents
        $staffNumbers = ['PROF-0001', 'PROF-0003', 'PROF-0005', 'PROF-0007'];
        $professorIds = DB::table('professors')
            ->whereIn('staff_number', $staffNumbers)
            ->pluck('id', 'staff_number');

        $vicePresidents = [
            [
                'staff_number' => 'PROF-0001',
                'title'        => 'Vice President for Academic Affairs',
                'title_ar'     => 'نائب الرئيس للشؤون الأكاديمية',
                'order'        => 1,
                'is_active'    => true,
                'appointed_at' => '2020-09-01',
                'ended_at'     => null,
            ],
            [
                'staff_number' => 'PROF-0003',
                'title'        => 'Vice President for Research & Innovation',
                'title_ar'     => 'نائب الرئيس للبحث العلمي والابتكار',
                'order'        => 2,
                'is_active'    => true,
                'appointed_at' => '2021-01-15',
                'ended_at'     => null,
            ],
            [
                'staff_number' => 'PROF-0005',
                'title'        => 'Vice President for Student Affairs',
                'title_ar'     => 'نائب الرئيس لشؤون الطلاب',
                'order'        => 3,
                'is_active'    => true,
                'appointed_at' => '2019-09-01',
                'ended_at'     => null,
            ],
            [
                'staff_number' => 'PROF-0007',
                'title'        => 'Vice President for Community Service',
                'title_ar'     => 'نائب الرئيس لخدمة المجتمع',
                'order'        => 4,
                'is_active'    => false,
                'appointed_at' => '2018-09-01',
                'ended_at'     => '2023-08-31',
            ],
        ];

        foreach ($vicePresidents as $vp) {
            $staffNumber = $vp['staff_number'];

            if (! isset($professorIds[$staffNumber])) {
                continue;
            }

            DB::table('university_vice_presidents')->insertOrIgnore([
                'professor_id' => $professorIds[$staffNumber],
                'title'        => $vp['title'],
                'title_ar'     => $vp['title_ar'],
                'order'        => $vp['order'],
                'is_active'    => $vp['is_active'],
                'appointed_at' => $vp['appointed_at'],
                'ended_at'     => $vp['ended_at'],
                'university_id' => $universityId,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        }
    }
}
