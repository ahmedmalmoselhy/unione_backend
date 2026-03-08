<?php

namespace App\Imports;

use App\Models\Enrollment;
use App\Models\Grade;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GradesImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
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
        $errors              = [];
        $validRows           = [];
        $seenEnrollmentIds   = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            $data      = array_map(fn ($v) => is_string($v) ? trim($v) : $v, $row->toArray());

            $validator = Validator::make($data, $this->rules(), $this->messages());
            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $error) {
                    $errors[] = "Row {$rowNumber}: {$error}";
                }
                continue;
            }

            $enrollmentId = (int) $data['enrollment_id'];

            if (in_array($enrollmentId, $seenEnrollmentIds)) {
                $errors[] = "Row {$rowNumber}: Enrollment ID {$enrollmentId} is duplicated within the file.";
                continue;
            }
            $seenEnrollmentIds[] = $enrollmentId;

            // Scope check: enrollment must belong to admin's scope
            $enrollment = Enrollment::with('student')->find($enrollmentId);
            if (! $enrollment) {
                $errors[] = "Row {$rowNumber}: Enrollment ID {$enrollmentId} not found.";
                continue;
            }

            if ($this->scopedFacultyId && $enrollment->student?->faculty_id !== $this->scopedFacultyId) {
                $errors[] = "Row {$rowNumber}: Enrollment ID {$enrollmentId} is not in your faculty.";
                continue;
            }

            if ($this->scopedDepartmentId && $enrollment->student?->department_id !== $this->scopedDepartmentId) {
                $errors[] = "Row {$rowNumber}: Enrollment ID {$enrollmentId} is not in your department.";
                continue;
            }

            $validRows[] = [
                'enrollment_id' => $enrollmentId,
                'midterm'       => isset($data['midterm']) && $data['midterm'] !== '' ? (float) $data['midterm'] : null,
                'coursework'    => isset($data['coursework']) && $data['coursework'] !== '' ? (float) $data['coursework'] : null,
                'final'         => isset($data['final']) && $data['final'] !== '' ? (float) $data['final'] : null,
                'total'         => isset($data['total']) && $data['total'] !== '' ? (float) $data['total'] : null,
                'letter_grade'  => $data['letter_grade'] ?: null,
                'grade_points'  => isset($data['grade_points']) && $data['grade_points'] !== '' ? (float) $data['grade_points'] : null,
            ];
        }

        if (! empty($errors)) {
            $this->importErrors = $errors;
            return;
        }

        DB::transaction(function () use ($validRows) {
            foreach ($validRows as $row) {
                Grade::updateOrCreate(
                    ['enrollment_id' => $row['enrollment_id']],
                    array_merge($row, [
                        'graded_by' => auth()->id(),
                        'graded_at' => now(),
                    ]),
                );
                $this->importedCount++;
            }
        });
    }

    private function rules(): array
    {
        return [
            'enrollment_id' => ['required', 'integer', 'min:1'],
            'midterm'       => ['nullable', 'numeric', 'min:0'],
            'coursework'    => ['nullable', 'numeric', 'min:0'],
            'final'         => ['nullable', 'numeric', 'min:0'],
            'total'         => ['nullable', 'numeric', 'min:0'],
            'letter_grade'  => ['nullable', Rule::in(['A+', 'A', 'B+', 'B', 'C+', 'C', 'D+', 'D', 'F'])],
            'grade_points'  => ['nullable', 'numeric', 'min:0', 'max:4'],
        ];
    }

    private function messages(): array
    {
        return [
            'enrollment_id.required' => 'The enrollment_id column is required.',
            'letter_grade.in'        => 'Letter grade must be one of: A+, A, B+, B, C+, C, D+, D, F.',
        ];
    }
}
