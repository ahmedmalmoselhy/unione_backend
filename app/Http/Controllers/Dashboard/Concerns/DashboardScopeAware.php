<?php

namespace App\Http\Controllers\Dashboard\Concerns;

use Illuminate\Support\Facades\Auth;

/**
 * Provides scope-aware query helpers for dashboard controllers.
 *
 * - System admin  → scopedFacultyId() = null, scopedDepartmentId() = null  (no filter → sees everything)
 * - Faculty admin → scopedFacultyId() = int,  scopedDepartmentId() = null  (scoped to one faculty)
 * - Dept admin    → scopedFacultyId() = null, scopedDepartmentId() = int   (scoped to one department)
 */
trait DashboardScopeAware
{
    protected function scopedFacultyId(): ?int
    {
        return Auth::user()->scopedFacultyId();
    }

    protected function scopedDepartmentId(): ?int
    {
        return Auth::user()->scopedDepartmentId();
    }

    protected function isSystemAdmin(): bool
    {
        return Auth::user()->isSystemAdmin();
    }

    protected function isFacultyAdmin(): bool
    {
        return Auth::user()->isFacultyAdmin();
    }

    protected function isDepartmentAdmin(): bool
    {
        return Auth::user()->isDepartmentAdmin();
    }

    /**
     * Abort with 403 if the given faculty_id is outside the user's scope.
     */
    protected function authorizeFaculty(int $facultyId): void
    {
        $scoped = $this->scopedFacultyId();

        if ($scoped !== null && $scoped !== $facultyId) {
            abort(403);
        }
    }

    /**
     * Abort with 403 if the given department_id is outside the user's scope.
     * For faculty admins, we check that the department belongs to their faculty.
     */
    protected function authorizeDepartment(int $departmentId): void
    {
        $deptScope = $this->scopedDepartmentId();

        if ($deptScope !== null && $deptScope !== $departmentId) {
            abort(403);
        }

        // Faculty admin: ensure department is within their faculty
        $facultyScope = $this->scopedFacultyId();

        if ($facultyScope !== null) {
            $dept = \App\Models\Department::find($departmentId);
            if (! $dept || $dept->faculty_id !== $facultyScope) {
                abort(403);
            }
        }
    }
}
