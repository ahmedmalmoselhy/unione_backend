<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds one faculty administrator per faculty.
 * Each admin is a new employee (also gets the 'employee' role) assigned to
 * the first managerial department of their faculty, or the first department
 * if no managerial one exists.
 *
 * Running this seeder multiple times is safe — it skips existing entries.
 */
class FacultyAdminSeeder extends Seeder
{
    public function run(): void
    {
        $now              = now();
        $password         = Hash::make('Admin@2025!');
        $employeeRoleId   = DB::table('roles')->where('name', 'employee')->value('id');
        $facultyAdminRole = DB::table('roles')->where('name', 'faculty_admin')->value('id');

        $faculties = DB::table('faculties')->get();

        foreach ($faculties as $faculty) {
            // Skip if a faculty_admin already exists for this faculty
            $existing = DB::table('role_user')
                ->where('role_id', $facultyAdminRole)
                ->where('faculty_id', $faculty->id)
                ->whereNull('revoked_at')
                ->exists();

            if ($existing) {
                $this->command->line("  Skipping faculty [{$faculty->code}] — admin already assigned.");
                continue;
            }

            // Find an appropriate department for the employee record.
            // Prefer the first academic department in this faculty.
            $dept = DB::table('departments')
                ->where('faculty_id', $faculty->id)
                ->where('type', 'academic')
                ->orderBy('id')
                ->first()
                ?? DB::table('departments')
                    ->where('faculty_id', $faculty->id)
                    ->orderBy('id')
                    ->first();

            if (! $dept) {
                $this->command->warn("  No department found for faculty [{$faculty->code}] — skipping.");
                continue;
            }

            $slug    = strtolower($faculty->code);
            $email   = "admin.{$slug}@unione.com";

            if (DB::table('users')->where('email', $email)->exists()) {
                $this->command->line("  Skipping [{$email}] — already exists.");
                continue;
            }

            $userId = DB::table('users')->insertGetId([
                'national_id'       => '9' . str_pad($faculty->id, 13, '0', STR_PAD_LEFT),
                'first_name'        => $faculty->code,
                'last_name'         => 'Admin',
                'email'             => $email,
                'password'          => $password,
                'gender'            => 'male',
                'date_of_birth'     => '1985-01-01',
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

            // Faculty admin role with scope
            DB::table('role_user')->insert([
                'user_id'    => $userId,
                'role_id'    => $facultyAdminRole,
                'faculty_id' => $faculty->id,
                'granted_at' => $now,
            ]);

            // Employee record
            DB::table('employees')->insert([
                'user_id'         => $userId,
                'staff_number'    => 'FA-' . str_pad($faculty->id, 3, '0', STR_PAD_LEFT),
                'department_id'   => $dept->id,
                'job_title'       => 'Faculty Administrator',
                'employment_type' => 'full_time',
                'salary'          => 15000.00,
                'hired_at'        => now()->format('Y-m-d'),
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);

            $this->command->info("  Created faculty admin [{$email}] for [{$faculty->code}].");
        }
    }
}
