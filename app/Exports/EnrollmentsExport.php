<?php

namespace App\Exports;

use App\Models\Enrollment;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EnrollmentsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function __construct(
        private readonly ?int $facultyId,
        private readonly ?int $departmentId,
        private readonly array $filters = [],
    ) {}

    public function query()
    {
        return Enrollment::query()
            ->with(['student.user', 'section.course', 'academicTerm'])
            ->when($this->facultyId, fn ($q) => $q->whereHas('student', fn ($s) => $s->where('faculty_id', $this->facultyId)))
            ->when($this->departmentId, fn ($q) => $q->whereHas('student', fn ($s) => $s->where('department_id', $this->departmentId)))
            ->when(!empty($this->filters['term_id']), fn ($q) => $q->where('academic_term_id', $this->filters['term_id']))
            ->when(!empty($this->filters['status']), fn ($q) => $q->where('status', $this->filters['status']))
            ->latest('registered_at');
    }

    public function headings(): array
    {
        return [
            'Enrollment ID', 'Student Number', 'Student Name',
            'Section ID', 'Course Code', 'Course Name',
            'Term', 'Status', 'Registered At', 'Dropped At',
        ];
    }

    public function map($enrollment): array
    {
        return [
            $enrollment->id,
            $enrollment->student?->student_number,
            trim(($enrollment->student?->user?->first_name ?? '') . ' ' . ($enrollment->student?->user?->last_name ?? '')),
            $enrollment->section_id,
            $enrollment->section?->course?->code,
            $enrollment->section?->course?->name,
            $enrollment->academicTerm?->name,
            $enrollment->status,
            $enrollment->registered_at?->format('Y-m-d H:i'),
            $enrollment->dropped_at?->format('Y-m-d H:i'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
