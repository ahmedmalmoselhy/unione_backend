<?php

namespace App\Exports;

use App\Models\Grade;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GradesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function __construct(
        private readonly ?int $facultyId,
        private readonly ?int $departmentId,
        private readonly array $filters = [],
    ) {}

    public function query()
    {
        return Grade::query()
            ->with([
                'enrollment.student.user',
                'enrollment.section.course',
                'enrollment.academicTerm',
            ])
            ->when($this->facultyId, fn ($q) => $q->whereHas('enrollment.student', fn ($s) => $s->where('faculty_id', $this->facultyId)))
            ->when($this->departmentId, fn ($q) => $q->whereHas('enrollment.student', fn ($s) => $s->where('department_id', $this->departmentId)))
            ->when(!empty($this->filters['term_id']), fn ($q) => $q->whereHas('enrollment', fn ($e) => $e->where('academic_term_id', $this->filters['term_id'])))
            ->when(!empty($this->filters['letter_grade']), fn ($q) => $q->where('letter_grade', $this->filters['letter_grade']))
            ->latest('graded_at');
    }

    public function headings(): array
    {
        return [
            'Enrollment ID', 'Student Number', 'Student Name',
            'Course Code', 'Course Name', 'Term',
            'Midterm', 'Coursework', 'Final', 'Total',
            'Letter Grade', 'GPA Points',
        ];
    }

    public function map($grade): array
    {
        return [
            $grade->enrollment_id,
            $grade->enrollment?->student?->student_number,
            trim(($grade->enrollment?->student?->user?->first_name ?? '') . ' ' . ($grade->enrollment?->student?->user?->last_name ?? '')),
            $grade->enrollment?->section?->course?->code,
            $grade->enrollment?->section?->course?->name,
            $grade->enrollment?->academicTerm?->name,
            $grade->midterm,
            $grade->coursework,
            $grade->final,
            $grade->total,
            $grade->letter_grade,
            $grade->grade_points,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
