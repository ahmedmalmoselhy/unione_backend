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

        // Only the system-wide admin user.
        // Deans, department heads, professors, students, and employees
        // are created by their own dedicated seeders.
        $adminEmail = 'admin@unione.com';

        if (DB::table('users')->where('email', $adminEmail)->exists()) {
            return;
        }

        $userId = DB::table('users')->insertGetId([
            'national_id'       => '10000000000001',
            'first_name'        => 'Ahmed',
            'last_name'         => 'AlMoselhy',
            'email'             => $adminEmail,
            'password'          => $password,
            'gender'            => 'male',
            'date_of_birth'     => '1990-01-01',
            'is_active'         => true,
            'email_verified_at' => $now,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);

        $roleId = DB::table('roles')->where('name', 'admin')->value('id');

        DB::table('role_user')->insert([
            'user_id'    => $userId,
            'role_id'    => $roleId,
            'granted_at' => $now,
        ]);
    }
}
