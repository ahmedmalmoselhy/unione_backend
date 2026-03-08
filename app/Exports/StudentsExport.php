<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function __construct(
        private readonly ?int $facultyId,
        private readonly ?int $departmentId,
        private readonly array $filters = [],
    ) {}

    public function query()
    {
        return Student::query()
            ->with(['user', 'faculty', 'department'])
            ->join('users', 'students.user_id', '=', 'users.id')
            ->when($this->facultyId, fn ($q) => $q->where('students.faculty_id', $this->facultyId))
            ->when($this->departmentId, fn ($q) => $q->where('students.department_id', $this->departmentId))
            ->when(!empty($this->filters['faculty_id']), fn ($q) => $q->where('students.faculty_id', $this->filters['faculty_id']))
            ->when(!empty($this->filters['enrollment_status']), fn ($q) => $q->where('students.enrollment_status', $this->filters['enrollment_status']))
            ->orderBy('users.first_name')
            ->orderBy('users.last_name')
            ->select('students.*');
    }

    public function headings(): array
    {
        return [
            'Student Number', 'First Name', 'Last Name', 'Email', 'National ID',
            'Phone', 'Gender', 'Date of Birth', 'Faculty', 'Department',
            'Academic Year', 'Semester', 'Enrollment Status', 'GPA',
            'Enrolled At', 'Account Status',
        ];
    }

    public function map($student): array
    {
        return [
            $student->student_number,
            $student->user->first_name,
            $student->user->last_name,
            $student->user->email,
            $student->user->national_id,
            $student->user->phone,
            $student->user->gender,
            $student->user->date_of_birth?->format('Y-m-d'),
            $student->faculty?->name,
            $student->department?->name,
            $student->academic_year,
            $student->semester,
            $student->enrollment_status,
            $student->gpa,
            $student->enrolled_at?->format('Y-m-d'),
            $student->user->is_active ? 'Active' : 'Inactive',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
