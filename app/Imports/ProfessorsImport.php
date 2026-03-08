<?php

namespace App\Imports;

use App\Models\Department;
use App\Models\Faculty;
use App\Models\Professor;
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

class ProfessorsImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
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
        $errors          = [];
        $validRows       = [];
        $seenEmails      = [];
        $seenNationalIds = [];
        $seenStaffNumbers = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            $data      = array_map('trim', $row->toArray());

            $validator = Validator::make($data, $this->rules(), $this->messages());
            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $error) {
                    $errors[] = "Row {$rowNumber}: {$error}";
                }
                continue;
            }

            $email       = strtolower($data['email']);
            $nationalId  = $data['national_id'];
            $staffNumber = $data['staff_number'];

            if (in_array($email, $seenEmails)) {
                $errors[] = "Row {$rowNumber}: Email '{$email}' is duplicated within the file.";
                continue;
            }
            if (in_array($nationalId, $seenNationalIds)) {
                $errors[] = "Row {$rowNumber}: National ID '{$nationalId}' is duplicated within the file.";
                continue;
            }
            if (in_array($staffNumber, $seenStaffNumbers)) {
                $errors[] = "Row {$rowNumber}: Staff number '{$staffNumber}' is duplicated within the file.";
                continue;
            }

            $seenEmails[]       = $email;
            $seenNationalIds[]  = $nationalId;
            $seenStaffNumbers[] = $staffNumber;

            [$departmentId, $lookupError] = $this->resolveDepartment($data, $rowNumber);
            if ($lookupError) {
                $errors[] = $lookupError;
                continue;
            }

            $validRows[] = array_merge($data, ['_department_id' => $departmentId]);
        }

        if (! empty($errors)) {
            $this->importErrors = $errors;
            return;
        }

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

                Professor::create([
                    'user_id'         => $user->id,
                    'staff_number'    => $row['staff_number'],
                    'department_id'   => $row['_department_id'],
                    'specialization'  => $row['specialization'] ?? null,
                    'academic_rank'   => $row['academic_rank'] ?? 'lecturer',
                    'office_location' => $row['office_location'] ?? null,
                    'hired_at'        => ! empty($row['hired_at']) ? date('Y-m-d', strtotime($row['hired_at'])) : now()->toDateString(),
                ]);

                $this->importedCount++;
            }
        });
    }

    private function rules(): array
    {
        return [
            'national_id'   => ['required', 'string', 'min:5', 'max:30', 'unique:users,national_id'],
            'first_name'    => ['required', 'string', 'max:100'],
            'last_name'     => ['required', 'string', 'max:100'],
            'email'         => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone'         => ['nullable', 'string', 'max:30'],
            'gender'        => ['required', Rule::in(['male', 'female'])],
            'date_of_birth' => ['nullable', 'date'],
            'staff_number'  => ['required', 'string', 'max:50', 'unique:professors,staff_number'],
            'department'    => [$this->scopedDepartmentId ? 'nullable' : 'required', 'string'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'academic_rank'  => ['nullable', Rule::in(['lecturer', 'assistant_professor', 'associate_professor', 'professor'])],
            'office_location' => ['nullable', 'string', 'max:100'],
            'hired_at'       => ['nullable', 'date'],
        ];
    }

    private function messages(): array
    {
        return [
            'national_id.unique'   => 'National ID :input already exists.',
            'email.unique'         => 'Email :input already exists.',
            'staff_number.unique'  => 'Staff number :input already exists.',
            'academic_rank.in'     => 'Academic rank must be: lecturer, assistant_professor, associate_professor, or professor.',
        ];
    }

    private function resolveDepartment(array $row, int $rowNumber): array
    {
        if ($this->scopedDepartmentId) {
            return [$this->scopedDepartmentId, null];
        }

        if ($this->scopedFacultyId) {
            $dept = Department::where('faculty_id', $this->scopedFacultyId)
                ->where('name', $row['department'] ?? '')
                ->where('type', 'academic')
                ->first();
            if (! $dept) {
                $name = $row['department'] ?? '';
                return [null, "Row {$rowNumber}: Department \"{$name}\" not found in your faculty."];
            }
            return [$dept->id, null];
        }

        // System admin — look up by department name (unique enough with faculty context)
        $dept = Department::where('name', $row['department'] ?? '')
            ->where('type', 'academic')
            ->first();
        if (! $dept) {
            $name = $row['department'] ?? '';
            return [null, "Row {$rowNumber}: Department \"{$name}\" not found. For ambiguous names, prefix with faculty using faculty/department format."];
        }

        return [$dept->id, null];
    }
}
