<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds one department administrator per academic department.
 * Each admin is a new employee (also gets the 'employee' role) assigned to
 * their respective department.
 *
 * Running this seeder multiple times is safe — it skips existing entries.
 */
class DepartmentAdminSeeder extends Seeder
{
    public function run(): void
    {
        $now              = now();
        $password         = Hash::make('Admin@2025!');
        $employeeRoleId   = DB::table('roles')->where('name', 'employee')->value('id');
        $deptAdminRole    = DB::table('roles')->where('name', 'department_admin')->value('id');

        // Only seed academic departments (managerial ones don't normally have dept admins)
        $departments = DB::table('departments')
            ->where('type', 'academic')
            ->whereNotNull('faculty_id')
            ->orderBy('id')
            ->get();

        foreach ($departments as $dept) {
            // Skip if a department_admin already exists for this department
            $existing = DB::table('role_user')
                ->where('role_id', $deptAdminRole)
                ->where('department_id', $dept->id)
                ->whereNull('revoked_at')
                ->exists();

            if ($existing) {
                $this->command->line("  Skipping department [{$dept->code}] — admin already assigned.");
                continue;
            }

            $email = 'admin.' . strtolower($dept->code) . '@unione.com';

            if (DB::table('users')->where('email', $email)->exists()) {
                $this->command->line("  Skipping [{$email}] — already exists.");
                continue;
            }

            $userId = DB::table('users')->insertGetId([
                'national_id'       => '8' . str_pad($dept->id, 13, '0', STR_PAD_LEFT),
                'first_name'        => $dept->code,
                'last_name'         => 'Admin',
                'email'             => $email,
                'password'          => $password,
                'gender'            => 'male',
                'date_of_birth'     => '1988-06-15',
                'is_active'         => true,
                'must_change_password' => true,
                'email_verified_at' => $now,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);

            // Employee role (required to access the dashboard)
            DB::table('role_user')->insert([
                'user_id'    => $userId,
                'role_id'    => $employeeRoleId,
                'granted_at' => $now,
            ]);

            // Department admin role with scope
            DB::table('role_user')->insert([
                'user_id'       => $userId,
                'role_id'       => $deptAdminRole,
                'department_id' => $dept->id,
                'granted_at'    => $now,
            ]);

            // Employee record
            DB::table('employees')->insert([
                'user_id'         => $userId,
                'staff_number'    => 'DA-' . str_pad($dept->id, 3, '0', STR_PAD_LEFT),
                'department_id'   => $dept->id,
                'job_title'       => 'Department Administrator',
                'employment_type' => 'full_time',
                'salary'          => 12000.00,
                'hired_at'        => now()->format('Y-m-d'),
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);

            $this->command->info("  Created department admin [{$email}] for [{$dept->code}].");
        }
    }
}
