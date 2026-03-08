<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function __construct(
        private readonly ?int $facultyId,
        private readonly ?int $departmentId,
        private readonly array $filters = [],
    ) {}

    public function query()
    {
        return Employee::query()
            ->with(['user', 'department.faculty'])
            ->join('users', 'employees.user_id', '=', 'users.id')
            ->when($this->facultyId, fn ($q) => $q->whereHas('department', fn ($d) => $d->where('faculty_id', $this->facultyId)))
            ->when($this->departmentId, fn ($q) => $q->where('employees.department_id', $this->departmentId))
            ->when(!empty($this->filters['department_id']), fn ($q) => $q->where('employees.department_id', $this->filters['department_id']))
            ->when(!empty($this->filters['employment_type']), fn ($q) => $q->where('employees.employment_type', $this->filters['employment_type']))
            ->orderBy('users.first_name')
            ->orderBy('users.last_name')
            ->select('employees.*');
    }

    public function headings(): array
    {
        return [
            'Staff Number', 'First Name', 'Last Name', 'Email', 'National ID',
            'Phone', 'Gender', 'Date of Birth', 'Department', 'Faculty',
            'Job Title', 'Employment Type', 'Salary',
            'Hired At', 'Terminated At', 'Account Status',
        ];
    }

    public function map($employee): array
    {
        return [
            $employee->staff_number,
            $employee->user->first_name,
            $employee->user->last_name,
            $employee->user->email,
            $employee->user->national_id,
            $employee->user->phone,
            $employee->user->gender,
            $employee->user->date_of_birth?->format('Y-m-d'),
            $employee->department?->name,
            $employee->department?->faculty?->name,
            $employee->job_title,
            $employee->employment_type,
            $employee->salary,
            $employee->hired_at?->format('Y-m-d'),
            $employee->terminated_at?->format('Y-m-d'),
            $employee->user->is_active ? 'Active' : 'Inactive',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
