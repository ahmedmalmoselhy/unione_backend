<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'admin',            'label' => 'Administrator'],
            ['name' => 'student',          'label' => 'Student'],
            ['name' => 'professor',        'label' => 'Professor'],
            ['name' => 'employee',         'label' => 'Employee'],
            ['name' => 'department_head',  'label' => 'Department Head'],
            ['name' => 'dean',             'label' => 'Dean'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(['name' => $role['name']], $role);
        }
    }
}
