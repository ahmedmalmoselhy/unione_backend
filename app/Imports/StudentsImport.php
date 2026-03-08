<?php

namespace App\Imports;

use App\Models\Department;
use App\Models\Faculty;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    use Importable;

    public array $importErrors = [];
    public int $importedCount  = 0;

    public function __construct(
        private readonly ?int $scopedFacultyId,
        private readonly ?int $scopedDepartmentId,
    ) {}

    public function collection(Collection $rows): void
    {
        $errors         = [];
        $validRows      = [];
        $seenEmails     = [];
        $seenNationalIds = [];
        $seenStudentNumbers = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // Row 1 is header
            $data      = array_map('trim', $row->toArray());

            // Basic field validation
            $validator = Validator::make($data, $this->rules(), $this->messages());
            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $error) {
                    $errors[] = "Row {$rowNumber}: {$error}";
                }
                continue;
            }

            // Within-batch duplicate checks
            $email         = strtolower($data['email']);
            $nationalId    = $data['national_id'];
            $studentNumber = $data['student_number'];

            if (in_array($email, $seenEmails)) {
                $errors[] = "Row {$rowNumber}: Email '{$email}' is duplicated within the file.";
                continue;
            }
            if (in_array($nationalId, $seenNationalIds)) {
                $errors[] = "Row {$rowNumber}: National ID '{$nationalId}' is duplicated within the file.";
                continue;
            }
            if (in_array($studentNumber, $seenStudentNumbers)) {
                $errors[] = "Row {$rowNumber}: Student number '{$studentNumber}' is duplicated within the file.";
                continue;
            }

            $seenEmails[]        = $email;
            $seenNationalIds[]   = $nationalId;
            $seenStudentNumbers[] = $studentNumber;

            // Resolve faculty and department
            [$facultyId, $departmentId, $lookupError] = $this->resolveFacultyDept($data, $rowNumber);
            if ($lookupError) {
                $errors[] = $lookupError;
                continue;
            }

            $validRows[] = array_merge($data, [
                '_faculty_id'    => $facultyId,
                '_department_id' => $departmentId,
            ]);
        }

        if (! empty($errors)) {
            $this->importErrors = $errors;
            return;
        }

        // All rows valid — import in a single transaction
        DB::transaction(function () use ($validRows) {
            foreach ($validRows as $row) {
                $user = User::create([
                    'national_id'          => $row['national_id'],
                    'first_name'           => $row['first_name'],
                    'last_name'            => $row['last_name'],
                    'email'                => strtolower($row['email']),
                    'password'             => Hash::make($row['national_id']),
                    'phone'                => $row['phone'] ?? null,
                    'gender'               => strtolower($row['gender']),
                    'date_of_birth'        => ! empty($row['date_of_birth']) ? date('Y-m-d', strtotime($row['date_of_birth'])) : null,
                    'is_active'            => true,
                    'must_change_password' => false,
                ]);

                Student::create([
                    'user_id'           => $user->id,
                    'student_number'    => $row['student_number'],
                    'faculty_id'        => $row['_faculty_id'],
                    'department_id'     => $row['_department_id'],
                    'academic_year'     => (int) $row['academic_year'],
                    'semester'          => (int) $row['semester'],
                    'enrollment_status' => $row['enrollment_status'] ?: 'active',
                    'enrolled_at'       => now()->toDateString(),
                ]);

                $this->importedCount++;
            }
        });
    }

    private function rules(): array
    {
        return [
            'national_id'      => ['required', 'string', 'min:5', 'max:30', 'unique:users,national_id'],
            'first_name'       => ['required', 'string', 'max:100'],
            'last_name'        => ['required', 'string', 'max:100'],
            'email'            => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone'            => ['nullable', 'string', 'max:30'],
            'gender'           => ['required', Rule::in(['male', 'female'])],
            'date_of_birth'    => ['nullable', 'date'],
            'student_number'   => ['required', 'string', 'max:50', 'unique:students,student_number'],
            'faculty'          => [$this->scopedFacultyId ? 'nullable' : 'required', 'string'],
            'department'       => [$this->scopedDepartmentId ? 'nullable' : 'required', 'string'],
            'academic_year'    => ['required', 'integer', 'between:1,7'],
            'semester'         => ['required', 'integer', 'between:1,2'],
            'enrollment_status' => ['nullable', Rule::in(['active', 'suspended', 'graduated', 'withdrawn'])],
        ];
    }

    private function messages(): array
    {
        return [
            'national_id.unique'    => 'National ID :input already exists.',
            'email.unique'          => 'Email :input already exists.',
            'student_number.unique' => 'Student number :input already exists.',
        ];
    }

    private function resolveFacultyDept(array $row, int $rowNumber): array
    {
        // Department admin — both are locked in
        if ($this->scopedDepartmentId) {
            $dept = Department::find($this->scopedDepartmentId);
            return [$dept?->faculty_id, $this->scopedDepartmentId, null];
        }

        // Faculty admin — faculty is locked in; look up department by name
        if ($this->scopedFacultyId) {
            $dept = Department::where('faculty_id', $this->scopedFacultyId)
                ->where('name', $row['department'] ?? '')
                ->where('type', 'academic')
                ->first();
            if (! $dept) {
                $name = $row['department'] ?? '';
                return [null, null, "Row {$rowNumber}: Department \"{$name}\" not found in your faculty."];
            }
            return [$this->scopedFacultyId, $dept->id, null];
        }

        // System admin — look up both
        $faculty = Faculty::where('name', $row['faculty'] ?? '')->first();
        if (! $faculty) {
            $name = $row['faculty'] ?? '';
            return [null, null, "Row {$rowNumber}: Faculty \"{$name}\" not found."];
        }

        $dept = Department::where('faculty_id', $faculty->id)
            ->where('name', $row['department'] ?? '')
            ->where('type', 'academic')
            ->first();
        if (! $dept) {
            $name = $row['department'] ?? '';
            return [null, null, "Row {$rowNumber}: Department \"{$name}\" not found in faculty \"{$row['faculty']}\"."];
        }

        return [$faculty->id, $dept->id, null];
    }
}
