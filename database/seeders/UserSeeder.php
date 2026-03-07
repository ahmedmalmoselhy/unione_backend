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

        // Only roles that have no dedicated profile table.
        // Students, professors, and employees are handled by their own seeders
        // which create both the user and the profile record together.
        $users = [
            [
                'role'        => 'admin',
                'national_id' => '10000000000001',
                'first_name'  => 'Ahmed',
                'last_name'   => 'AlMoselhy',
                'email'       => 'admin@unione.com',
                'gender'      => 'male',
            ],
            [
                'role'        => 'department_head',
                'national_id' => '10000000000002',
                'first_name'  => 'Youssef',
                'last_name'   => 'Badawi',
                'email'       => 'depthead@unione.com',
                'gender'      => 'male',
            ],
            [
                'role'        => 'dean',
                'national_id' => '10000000000003',
                'first_name'  => 'Samira',
                'last_name'   => 'Khalil',
                'email'       => 'dean@unione.com',
                'gender'      => 'female',
            ],
        ];

        foreach ($users as $data) {
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

            $roleId = DB::table('roles')->where('name', $data['role'])->value('id');

            DB::table('role_user')->insert([
                'user_id'    => $userId,
                'role_id'    => $roleId,
                'granted_at' => $now,
            ]);
        }
    }
}