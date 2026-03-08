<?php

namespace Database\Seeders;

use App\Models\AcademicTerm;
use App\Models\Enrollment;
use App\Models\Section;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $activeTerm = AcademicTerm::where('is_active', true)->first();
        $pastTerms  = AcademicTerm::where('is_active', false)->orderByDesc('starts_at')->get();

        $activeSections = Section::where('is_active', true)
            ->where('academic_term_id', $activeTerm?->id)
            ->get();

        $pastSections = Section::where('is_active', false)->get();

        $students = Student::where('enrollment_status', 'active')->get();

        if ($students->isEmpty() || $activeSections->isEmpty()) {
            return;
        }

        $rows = [];

        // Each active student gets 3-5 current-term enrollments (registered)
        foreach ($students as $student) {
            $picked = $activeSections->random(min($activeSections->count(), rand(3, 5)));

            foreach ($picked as $section) {
                $rows[] = [
                    'student_id'       => $student->id,
                    'section_id'       => $section->id,
                    'academic_term_id' => $activeTerm->id,
                    'status'           => 'registered',
                    'registered_at'    => $activeTerm->starts_at->copy()->addDays(rand(0, 7)),
                    'dropped_at'       => null,
                ];
            }
        }

        // Some students also have past-term enrollments (completed / failed / dropped)
        if ($pastSections->isNotEmpty() && $pastTerms->isNotEmpty()) {
            $subset = $students->random(min($students->count(), 10));

            foreach ($subset as $student) {
                $picked = $pastSections->random(min($pastSections->count(), rand(3, 5)));

                foreach ($picked as $section) {
                    $term = $pastTerms->firstWhere('id', $section->academic_term_id) ?? $pastTerms->first();
                    $status = collect(['completed', 'completed', 'completed', 'completed', 'failed', 'dropped'])->random();

                    $registeredAt = $term->starts_at->copy()->addDays(rand(0, 5));

                    $rows[] = [
                        'student_id'       => $student->id,
                        'section_id'       => $section->id,
                        'academic_term_id' => $term->id,
                        'status'           => $status,
                        'registered_at'    => $registeredAt,
                        'dropped_at'       => $status === 'dropped' ? $registeredAt->copy()->addDays(rand(7, 30)) : null,
                    ];
                }
            }
        }

        // Filter out duplicates (student_id + section_id must be unique)
        $unique = collect($rows)->unique(fn ($r) => $r['student_id'] . '-' . $r['section_id']);

        foreach ($unique as $row) {
            Enrollment::create([
                'student_id'       => $row['student_id'],
                'section_id'       => $row['section_id'],
                'academic_term_id' => $row['academic_term_id'],
                'status'           => $row['status'],
                'registered_at'    => $row['registered_at'],
                'dropped_at'       => $row['dropped_at'],
            ]);
        }
    }
}
