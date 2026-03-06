<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UniversitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('university')->updateOrInsert(
            ['id' => 1],
            [
                'name'           => 'Unione University',
                'name_ar'        => 'جامعة يونيون',
                'address'        => '14 Al-Nahda Street, Cairo, Egypt',
                'logo_path'      => null,
                'established_at' => '1993-09-01',
                'created_at'     => now(),
                'updated_at'     => now(),
            ]
        );
    }
}
