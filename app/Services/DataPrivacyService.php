<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Employee;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Notification;
use App\Models\Professor;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DataPrivacyService
{
    /**
     * Export all personal data for a user (GDPR Article 20 - Data Portability).
     *
     * @param int $userId User ID to export data for
     * @return array Complete personal data
     */
    public function exportUserData(int $userId): array
    {
        $user = User::with(['accountProfile'])->findOrFail($userId);

        $data = [
            'user' => [
                'id' => $user->id,
                'national_id' => $user->national_id,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'phone' => $user->phone,
                'gender' => $user->gender,
                'date_of_birth' => $user->date_of_birth,
                'created_at' => $user->created_at->toIso8601String(),
                'updated_at' => $user->updated_at->toIso8601String(),
                'profile' => $user->accountProfile?->toArray(),
            ],
            'roles' => $user->roles()->get()->map(function ($role) {
                return [
                    'name' => $role->name,
                    'scope_type' => $role->pivot->scope_type,
                    'scope_id' => $role->pivot->scope_id,
                    'assigned_at' => $role->pivot->created_at->toIso8601String(),
                ];
            }),
        ];

        // Add student data if exists
        $student = Student::where('user_id', $userId)->first();
        if ($student) {
            $data['student'] = $this->exportStudentData($student);
        }

        // Add professor data if exists
        $professor = Professor::where('user_id', $userId)->first();
        if ($professor) {
            $data['professor'] = $this->exportProfessorData($professor);
        }

        // Add employee data if exists
        $employee = Employee::where('user_id', $userId)->first();
        if ($employee) {
            $data['employee'] = $this->exportEmployeeData($employee);
        }

        // Add notifications
        $data['notifications'] = Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get()
            ->map(fn ($n) => [
                'title' => $n->title,
                'body' => $n->body,
                'type' => $n->type,
                'read_at' => $n->read_at?->toIso8601String(),
                'created_at' => $n->created_at->toIso8601String(),
            ]);

        return $data;
    }

    /**
     * Export student-specific data.
     */
    protected function exportStudentData(Student $student): array
    {
        return [
            'student_number' => $student->student_number,
            'academic_year' => $student->academic_year,
            'semester' => $student->semester,
            'enrollment_status' => $student->enrollment_status,
            'gpa' => $student->gpa,
            'academic_standing' => $student->academic_standing,
            'enrolled_at' => $student->enrolled_at?->toIso8601String(),
            'graduated_at' => $student->graduated_at?->toIso8601String(),
            'faculty' => $student->faculty?->name,
            'department' => $student->department?->name,
            'enrollments' => Enrollment::where('student_id', $student->id)
                ->with(['section.course', 'grade'])
                ->get()
                ->map(fn ($e) => [
                    'course' => $e->section?->course?->name,
                    'status' => $e->status,
                    'grade' => $e->grade?->letter_grade,
                    'registered_at' => $e->registered_at?->toIso8601String(),
                ]),
        ];
    }

    /**
     * Export professor-specific data.
     */
    protected function exportProfessorData(Professor $professor): array
    {
        return [
            'staff_number' => $professor->staff_number,
            'academic_rank' => $professor->academic_rank,
            'specialization' => $professor->specialization,
            'office' => $professor->office,
            'hire_date' => $professor->hire_date?->toIso8601String(),
            'department' => $professor->department?->name,
        ];
    }

    /**
     * Export employee-specific data.
     */
    protected function exportEmployeeData(Employee $employee): array
    {
        return [
            'staff_number' => $employee->staff_number,
            'job_title' => $employee->job_title,
            'employment_type' => $employee->employment_type,
            'salary' => $employee->salary,
            'hire_date' => $employee->hire_date?->toIso8601String(),
        ];
    }

    /**
     * Anonymize user data (GDPR Article 17 - Right to be Forgotten).
     * Soft deletes user and anonymizes personal information.
     *
     * @param int $userId User ID to anonymize
     * @return bool Success status
     */
    public function anonymizeUser(int $userId): bool
    {
        return DB::transaction(function () use ($userId) {
            $user = User::findOrFail($userId);

            // Delete avatar if exists
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            // Anonymize user record
            $user->update([
                'national_id' => 'ANONYMIZED-' . $user->id,
                'email' => "anonymized-{$user->id}@deleted.local",
                'first_name' => 'Deleted',
                'last_name' => 'User',
                'phone' => null,
                'date_of_birth' => null,
                'avatar_path' => null,
            ]);

            // Soft delete the user
            $user->delete();

            // Delete associated notifications
            Notification::where('user_id', $userId)->delete();

            return true;
        });
    }

    /**
     * Delete all data for a user (hard delete - use with caution).
     * This is irreversible!
     *
     * @param int $userId User ID to delete
     * @return bool Success status
     */
    public function hardDeleteUser(int $userId): bool
    {
        return DB::transaction(function () use ($userId) {
            $user = User::findOrFail($userId);

            // Delete avatar
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            // Delete associated records
            $student = Student::where('user_id', $userId)->first();
            if ($student) {
                Enrollment::where('student_id', $student->id)->delete();
                $student->delete();
            }

            $professor = Professor::where('user_id', $userId)->first();
            if ($professor) {
                $professor->delete();
            }

            $employee = Employee::where('user_id', $userId)->first();
            if ($employee) {
                $employee->delete();
            }

            // Delete notifications
            Notification::where('user_id', $userId)->delete();

            // Finally delete the user
            $user->forceDelete();

            return true;
        });
    }
}
