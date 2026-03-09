<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Grants the department_admin role to the existing head of each faculty-scoped
 * department. No new users are created — the head (already a professor or employee)
 * simply also receives the department_admin role so they can access the dashboard.
 *
 * Running this seeder multiple times is safe — it skips existing entries.
 */
class DepartmentAdminSeeder extends Seeder
{
    public function run(): void
    {
        $now           = now();
        $deptAdminRole = DB::table('roles')->where('name', 'department_admin')->value('id');

        // All faculty-scoped departments that already have a head assigned
        $departments = DB::table('departments')
            ->where('scope', 'faculty')
            ->whereNotNull('faculty_id')
            ->whereNotNull('head_id')
            ->orderBy('id')
            ->get();

        foreach ($departments as $dept) {
            // Skip if department_admin already exists for this department
            $existing = DB::table('role_user')
                ->where('role_id', $deptAdminRole)
                ->where('department_id', $dept->id)
                ->whereNull('revoked_at')
                ->exists();

            if ($existing) {
                $this->command->line("  Skipping [{$dept->code}] — admin already assigned.");
                continue;
            }

            // Grant department_admin to the existing department head
            DB::table('role_user')->insert([
                'user_id'       => $dept->head_id,
                'role_id'       => $deptAdminRole,
                'department_id' => $dept->id,
                'granted_at'    => $now,
            ]);

            $this->command->info("  Assigned department_admin to head of [{$dept->code}].");
        }
    }
}