<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('241996');
        $now      = now();

        $users = [
            [
                'role'        => 'admin',
                'national_id' => '10000000000001',
                'first_name'  => 'Omar',
                'last_name'   => 'Hassan',
                'email'       => 'admin@unione.com',
                'gender'      => 'male',
            ],
            [
                'role'        => 'student',
                'national_id' => '10000000000002',
                'first_name'  => 'Layla',
                'last_name'   => 'Mahmoud',
                'email'       => 'student@unione.com',
                'gender'      => 'female',
            ],
            [
                'role'        => 'professor',
                'national_id' => '10000000000003',
                'first_name'  => 'Karim',
                'last_name'   => 'Nasser',
                'email'       => 'professor@unione.com',
                'gender'      => 'male',
            ],
            [
                'role'        => 'employee',
                'national_id' => '10000000000004',
                'first_name'  => 'Nour',
                'last_name'   => 'Saleh',
                'email'       => 'employee@unione.com',
                'gender'      => 'female',
            ],
            [
                'role'        => 'department_head',
                'national_id' => '10000000000005',
                'first_name'  => 'Youssef',
                'last_name'   => 'Badawi',
                'email'       => 'depthead@unione.com',
                'gender'      => 'male',
            ],
            [
                'role'        => 'dean',
                'national_id' => '10000000000006',
                'first_name'  => 'Samira',
                'last_name'   => 'Khalil',
                'email'       => 'dean@unione.com',
                'gender'      => 'female',
            ],
        ];

        foreach ($users as $data) {
            // Create the user
            $userId = DB::table('users')->insertGetId([
                'national_id'       => $data['national_id'],
                'first_name'        => $data['first_name'],
                'last_name'         => $data['last_name'],
                'email'             => $data['email'],
                'password'          => $password,
                'gender'            => $data['gender'],
                'date_of_birth'     => '1990-01-01',
                'is_active'         => true,
                'email_verified_at' => $now,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);

            // Assign the role
            $roleId = DB::table('roles')->where('name', $data['role'])->value('id');

            DB::table('role_user')->insert([
                'user_id'    => $userId,
                'role_id'    => $roleId,
                'granted_at' => $now,
            ]);
        }
    }
}
