<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UniversityAdminSeeder extends Seeder
{
    public function run(): void
    {
        $roleId = DB::table('roles')->where('name', 'university_admin')->value('id');
        if (! $roleId) {
            return;
        }

        // Skip if a university_admin is already assigned
        if (DB::table('role_user')->where('role_id', $roleId)->whereNull('revoked_at')->exists()) {
            return;
        }

        $now   = now();
        $email = 'university.admin@unione.com';

        $userId = DB::table('users')->where('email', $email)->value('id');

        if (! $userId) {
            $userId = DB::table('users')->insertGetId([
                'national_id'          => '10000000000002',
                'first_name'           => 'University',
                'last_name'            => 'Admin',
                'email'                => $email,
                'password'             => Hash::make('Admin@2025!'),
                'gender'               => 'male',
                'date_of_birth'        => '1980-01-01',
                'is_active'            => true,
                'must_change_password' => false,
                'email_verified_at'    => $now,
                'created_at'           => $now,
                'updated_at'           => $now,
            ]);
        }

        DB::table('role_user')->insert([
            'user_id'    => $userId,
            'role_id'    => $roleId,
            'granted_at' => $now,
        ]);
    }
}
