<?php

namespace App\Exports;

use App\Models\Professor;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProfessorsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function __construct(
        private readonly ?int $facultyId,
        private readonly ?int $departmentId,
        private readonly array $filters = [],
    ) {}

    public function query()
    {
        return Professor::query()
            ->with(['user', 'department.faculty'])
            ->join('users', 'professors.user_id', '=', 'users.id')
            ->when($this->facultyId, fn ($q) => $q->whereHas('department', fn ($d) => $d->where('faculty_id', $this->facultyId)))
            ->when($this->departmentId, fn ($q) => $q->where('professors.department_id', $this->departmentId))
            ->when(!empty($this->filters['department_id']), fn ($q) => $q->where('professors.department_id', $this->filters['department_id']))
            ->when(!empty($this->filters['rank']), fn ($q) => $q->where('professors.academic_rank', $this->filters['rank']))
            ->orderBy('users.first_name')
            ->orderBy('users.last_name')
            ->select('professors.*');
    }

    public function headings(): array
    {
        return [
            'Staff Number', 'First Name', 'Last Name', 'Email', 'National ID',
            'Phone', 'Gender', 'Date of Birth', 'Department', 'Faculty',
            'Specialization', 'Academic Rank', 'Office Location',
            'Hired At', 'Account Status',
        ];
    }

    public function map($professor): array
    {
        return [
            $professor->staff_number,
            $professor->user->first_name,
            $professor->user->last_name,
            $professor->user->email,
            $professor->user->national_id,
            $professor->user->phone,
            $professor->user->gender,
            $professor->user->date_of_birth?->format('Y-m-d'),
            $professor->department?->name,
            $professor->department?->faculty?->name,
            $professor->specialization,
            $professor->academic_rank,
            $professor->office_location,
            $professor->hired_at?->format('Y-m-d'),
            $professor->user->is_active ? 'Active' : 'Inactive',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
