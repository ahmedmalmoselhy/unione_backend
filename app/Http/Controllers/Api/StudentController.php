<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Services\CacheService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class StudentController extends Controller
{
    /**
     * GET /api/student/profile
     * Returns the authenticated student's profile with faculty and department.
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        $cacheKey = CacheService::key('student:profile', $user->id);

        $student = CacheService::remember(
            $cacheKey,
            fn () => $user->student()
                ->with(['faculty:id,name,code', 'department:id,name,code'])
                ->firstOrFail(),
            ttl: 1800, // 30 minutes
            tags: [CacheService::TAG_ORGANIZATION, CacheService::TAG_USER]
        );

        return response()->json([
            'student' => [
                'id'                => $student->id,
                'student_number'    => $student->student_number,
                'academic_year'     => $student->academic_year,
                'semester'          => $student->semester,
                'enrollment_status' => $student->enrollment_status,
                'gpa'               => $student->gpa,
                'academic_standing' => $student->academic_standing,
                'enrolled_at'       => $student->enrolled_at?->toDateString(),
                'graduated_at'      => $student->graduated_at?->toDateString(),
                'faculty'           => $student->faculty ? [
                    'id'   => $student->faculty->id,
                    'name' => $student->faculty->name,
                    'code' => $student->faculty->code,
                ] : null,
                'department' => $student->department ? [
                    'id'   => $student->department->id,
                    'name' => $student->department->name,
                    'code' => $student->department->code,
                ] : null,
            ],
        ]);
    }

    /**
     * GET /api/student/enrollments
     * Returns the authenticated student's enrollments with section, course,
     * academic term, and grade (if graded).
     */
    public function enrollments(Request $request): JsonResponse
    {
        $student = $request->user()
            ->student()
            ->firstOrFail();

        $enrollments = $student->enrollments()
            ->with([
                'section.course:id,code,name,credit_hours',
                'section.academicTerm:id,name,academic_year,semester',
                'grade',
            ])
            ->latest()
            ->get()
            ->map(function ($enrollment) {
                $section = $enrollment->section;
                $course  = $section?->course;
                $term    = $section?->academicTerm;
                $grade   = $enrollment->grade;

                return [
                    'id'            => $enrollment->id,
                    'status'        => $enrollment->status,
                    'registered_at' => $enrollment->registered_at?->toDateTimeString(),
                    'dropped_at'    => $enrollment->dropped_at?->toDateTimeString(),
                    'course' => $course ? [
                        'id'           => $course->id,
                        'code'         => $course->code,
                        'name'         => $course->name,
                        'credit_hours' => $course->credit_hours,
                    ] : null,
                    'section' => $section ? [
                        'id'       => $section->id,
                        'room'     => $section->room,
                        'schedule' => $section->schedule,
                    ] : null,
                    'academic_term' => $term ? [
                        'id'            => $term->id,
                        'name'          => $term->name,
                        'academic_year' => $term->academic_year,
                        'semester'      => $term->semester,
                    ] : null,
                    'grade' => $grade ? [
                        'midterm'      => $grade->midterm,
                        'final'        => $grade->final,
                        'coursework'   => $grade->coursework,
                        'total'        => $grade->total,
                        'letter_grade' => $grade->letter_grade,
                        'grade_points' => $grade->grade_points,
                        'graded_at'    => $grade->graded_at?->toDateTimeString(),
                    ] : null,
                ];
            });

        return response()->json(['enrollments' => $enrollments]);
    }

    /**
     * GET /api/student/grades
     * Returns all graded enrollments for the authenticated student.
     */
    public function grades(Request $request): JsonResponse
    {
        $student = $request->user()
            ->student()
            ->firstOrFail();

        // Index semester GPAs keyed by academic_term_id for O(1) lookup
        $termGpas = $student->termGpas()->get()->keyBy('academic_term_id');

        $grades = $student->enrollments()
            ->with(['section.course', 'section.academicTerm', 'grade'])
            ->whereHas('grade')
            ->latest()
            ->get()
            ->map(function ($enrollment) use ($termGpas) {
                $section = $enrollment->section;
                $course  = $section?->course;
                $term    = $section?->academicTerm;
                $grade   = $enrollment->grade;
                $termGpa = $term ? $termGpas->get($term->id)?->gpa : null;

                return [
                    'enrollment_id' => $enrollment->id,
                    'status'        => $enrollment->status,
                    'course' => $course ? [
                        'id'           => $course->id,
                        'code'         => $course->code,
                        'name'         => $course->name,
                        'credit_hours' => $course->credit_hours,
                    ] : null,
                    'academic_term' => $term ? [
                        'id'            => $term->id,
                        'name'          => $term->name,
                        'academic_year' => $term->academic_year,
                        'semester'      => $term->semester,
                        'semester_gpa'  => $termGpa !== null ? (float) $termGpa : null,
                    ] : null,
                    'grade' => [
                        'midterm'      => $grade->midterm,
                        'final'        => $grade->final,
                        'coursework'   => $grade->coursework,
                        'total'        => $grade->total,
                        'letter_grade' => $grade->letter_grade,
                        'grade_points' => $grade->grade_points,
                        'graded_at'    => $grade->graded_at?->toDateTimeString(),
                    ],
                ];
            });

        return response()->json(['grades' => $grades]);
    }

    /**
     * GET /api/student/schedule
     * Returns the authenticated student's schedule for the active academic term.
     */
    public function schedule(Request $request): JsonResponse
    {
        $student = $request->user()
            ->student()
            ->firstOrFail();

        $currentTerm = AcademicTerm::where('is_active', true)->latest('academic_year')->first();

        $enrollments = $student->enrollments()
            ->with(['section.course', 'section.professor.user', 'section.academicTerm'])
            ->when($currentTerm, fn ($q) => $q->where('academic_term_id', $currentTerm->id))
            ->whereIn('status', ['registered', 'completed'])
            ->get();

        if ($enrollments->isEmpty() && $currentTerm) {
            $enrollments = $student->enrollments()
                ->with(['section.course', 'section.professor.user', 'section.academicTerm'])
                ->whereIn('status', ['registered', 'completed'])
                ->get();
        }

        $scheduleEntries = $enrollments->flatMap(function ($enrollment) {
            $section  = $enrollment->section;
            $schedule = $section?->schedule ?? [];

            $courseData = [
                'id'   => $section?->course?->id,
                'code' => $section?->course?->code ?? '',
                'name' => $section?->course?->name ?? '',
            ];
            $professorName = $section?->professor?->user
                ? $section->professor->user->first_name . ' ' . $section->professor->user->last_name
                : null;

            if (empty($schedule)) {
                return collect([[
                    'day'        => 'Unscheduled',
                    'start_time' => null,
                    'end_time'   => null,
                    'room'       => $section?->room,
                    'type'       => 'lecture',
                    'course'     => $courseData,
                    'professor'  => $professorName,
                ]]);
            }

            return collect($schedule)->map(fn ($slot) => [
                'day'        => ucfirst(strtolower($slot['day'] ?? '')),
                'start_time' => $slot['start_time'] ?? null,
                'end_time'   => $slot['end_time'] ?? null,
                'room'       => $section?->room,
                'type'       => $slot['type'] ?? 'lecture',
                'course'     => $courseData,
                'professor'  => $professorName,
            ]);
        })->values();

        return response()->json([
            'academic_term'   => $currentTerm ? [
                'id'            => $currentTerm->id,
                'name'          => $currentTerm->name,
                'academic_year' => $currentTerm->academic_year,
                'semester'      => $currentTerm->semester,
            ] : null,
            'schedule' => $scheduleEntries,
        ]);
    }

    /**
     * GET /api/student/transcript
     * Returns the student's full academic transcript grouped by term.
     */
    public function transcript(Request $request): JsonResponse
    {
        $student = $request->user()
            ->student()
            ->with(['user', 'faculty', 'department'])
            ->firstOrFail();

        $termGpas = $student->termGpas()->get()->keyBy('academic_term_id');

        $enrollments = $student->enrollments()
            ->with(['section.course', 'section.academicTerm', 'grade'])
            ->whereHas('grade')
            ->whereIn('status', ['completed'])
            ->get();

        $terms = $enrollments->groupBy(fn ($e) => $e->section->academicTerm->id)
            ->map(function ($termEnrollments) use ($termGpas) {
                $term        = $termEnrollments->first()->section->academicTerm;
                $termGpa     = $termGpas->get($term->id);
                $totalCredits = $termEnrollments->sum(fn ($e) => $e->section->course->credit_hours ?? 0);

                $courses = $termEnrollments->map(fn ($e) => [
                    'course' => [
                        'id'           => $e->section->course->id,
                        'code'         => $e->section->course->code,
                        'name'         => $e->section->course->name,
                        'credit_hours' => $e->section->course->credit_hours,
                    ],
                    'grade' => [
                        'midterm'      => $e->grade->midterm,
                        'final'        => $e->grade->final,
                        'coursework'   => $e->grade->coursework,
                        'total'        => $e->grade->total,
                        'letter_grade' => $e->grade->letter_grade,
                        'grade_points' => $e->grade->grade_points,
                    ],
                ])->values();

                return [
                    'academic_term' => [
                        'id'            => $term->id,
                        'name'          => $term->name,
                        'academic_year' => $term->academic_year,
                        'semester'      => $term->semester,
                    ],
                    'term_gpa'     => $termGpa ? (float) $termGpa->gpa : null,
                    'term_credits' => $totalCredits,
                    'courses'      => $courses,
                ];
            })
            ->sortBy(fn ($t) => $t['academic_term']['id'])
            ->values();

        return response()->json([
            'student' => [
                'student_number'    => $student->student_number,
                'name'              => $student->user->first_name . ' ' . $student->user->last_name,
                'faculty'           => $student->faculty?->name,
                'department'        => $student->department?->name,
                'gpa'               => $student->gpa,
                'academic_standing' => $student->academic_standing,
            ],
            'terms' => $terms,
        ]);
    }

    /**
     * GET /api/student/academic-history
     * Full term-by-term history including all enrollment statuses,
     * plus credit-hour progress toward graduation.
     */
    public function academicHistory(Request $request): JsonResponse
    {
        $student = $request->user()
            ->student()
            ->with(['user', 'faculty', 'department'])
            ->firstOrFail();

        $termGpas = $student->termGpas()->with('academicTerm')->get()->keyBy('academic_term_id');

        // All enrollments — not filtered by status or grade existence
        $allEnrollments = $student->enrollments()
            ->with(['section.course', 'section.academicTerm', 'grade'])
            ->get();

        // Credits earned = sum of credit hours from *completed* enrollments only
        $creditsEarned = $allEnrollments
            ->where('status', 'completed')
            ->sum(fn ($e) => $e->section?->course?->credit_hours ?? 0);

        $creditsRequired = $student->department?->required_credit_hours;

        $terms = $allEnrollments
            ->filter(fn ($e) => $e->section?->academicTerm !== null)
            ->groupBy(fn ($e) => $e->section->academicTerm->id)
            ->map(function (Collection $termEnrollments) use ($termGpas) {
                $term    = $termEnrollments->first()->section->academicTerm;
                $termGpa = $termGpas->get($term->id);

                $courses = $termEnrollments->map(fn ($e) => [
                    'enrollment_id' => $e->id,
                    'status'        => $e->status,
                    'registered_at' => $e->registered_at?->toDateTimeString(),
                    'dropped_at'    => $e->dropped_at?->toDateTimeString(),
                    'course'        => [
                        'id'           => $e->section->course->id,
                        'code'         => $e->section->course->code,
                        'name'         => $e->section->course->name,
                        'credit_hours' => $e->section->course->credit_hours,
                    ],
                    'grade' => $e->grade ? [
                        'letter_grade' => $e->grade->letter_grade,
                        'total'        => $e->grade->total,
                        'grade_points' => $e->grade->grade_points,
                    ] : null,
                ])->values();

                return [
                    'academic_term' => [
                        'id'            => $term->id,
                        'name'          => $term->name,
                        'academic_year' => $term->academic_year,
                        'semester'      => $term->semester,
                    ],
                    'term_gpa'     => $termGpa ? (float) $termGpa->gpa : null,
                    'term_credits' => $termGpa ? (int) $termGpa->credit_hours : null,
                    'courses'      => $courses,
                ];
            })
            ->sortBy(fn ($t) => $t['academic_term']['id'])
            ->values();

        return response()->json([
            'student' => [
                'student_number'    => $student->student_number,
                'name'              => $student->user->first_name . ' ' . $student->user->last_name,
                'faculty'           => $student->faculty?->name,
                'department'        => $student->department?->name,
                'enrollment_status' => $student->enrollment_status,
                'academic_year'     => $student->academic_year,
                'semester'          => $student->semester,
                'gpa'               => $student->gpa,
                'academic_standing' => $student->academic_standing,
            ],
            'progress' => [
                'credits_earned'   => $creditsEarned,
                'credits_required' => $creditsRequired,
                'progress_pct'     => ($creditsRequired && $creditsRequired > 0)
                    ? round(min($creditsEarned / $creditsRequired * 100, 100), 1)
                    : null,
            ],
            'terms' => $terms,
        ]);
    }

    /**
     * GET /api/student/transcript/pdf
     * Downloads the authenticated student's transcript as a PDF file.
     */
    public function transcriptPdf(Request $request)
    {
        $student = $request->user()
            ->student()
            ->with(['user', 'faculty', 'department'])
            ->firstOrFail();

        $student->load([
            'termGpas.academicTerm',
            'enrollments' => fn ($q) => $q
                ->where('status', 'completed')
                ->whereHas('grade')
                ->with(['section.course', 'section.academicTerm', 'grade']),
        ]);

        $termGpas = $student->termGpas->keyBy('academic_term_id');

        $terms = $student->enrollments
            ->filter(fn ($e) => $e->section?->academicTerm)
            ->groupBy(fn ($e) => $e->section->academicTerm->id)
            ->map(function ($termEnrollments) use ($termGpas) {
                $term         = $termEnrollments->first()->section->academicTerm;
                $termGpa      = $termGpas->get($term->id);
                $totalCredits = $termEnrollments->sum(fn ($e) => $e->section->course->credit_hours ?? 0);

                $courses = $termEnrollments->map(fn ($e) => [
                    'course' => $e->section->course,
                    'grade'  => $e->grade,
                ])->values();

                return [
                    'academic_term' => $term,
                    'term_gpa'      => $termGpa ? (float) $termGpa->gpa : null,
                    'term_credits'  => $totalCredits,
                    'courses'       => $courses,
                ];
            })
            ->sortBy(fn ($t) => $t['academic_term']->id)
            ->values();

        $pdf = Pdf::loadView('dashboard.students.transcript-pdf', compact('student', 'terms'));

        return $pdf->download("transcript-{$student->student_number}.pdf");
    }

    /**
     * GET /api/student/schedule/ics
     * Downloads the authenticated student's schedule as an RFC 5545 iCalendar (.ics) file.
     */
    public function scheduleIcs(Request $request): \Illuminate\Http\Response
    {
        $student = $request->user()
            ->student()
            ->firstOrFail();

        $currentTerm = AcademicTerm::where('is_active', true)->latest('academic_year')->first();

        $enrollments = $student->enrollments()
            ->with(['section.course', 'section.professor.user', 'section.academicTerm'])
            ->when($currentTerm, fn ($q) => $q->where('academic_term_id', $currentTerm->id))
            ->whereIn('status', ['registered', 'completed'])
            ->get();

        // Fallback: if no active term, pull all registered/completed sections
        if ($enrollments->isEmpty() && $currentTerm) {
            $enrollments = $student->enrollments()
                ->with(['section.course', 'section.professor.user', 'section.academicTerm'])
                ->whereIn('status', ['registered', 'completed'])
                ->get();
        }

        // iCal weekday abbreviations keyed by lowercase day name
        $rruleDayMap = [
            'monday'    => 'MO',
            'tuesday'   => 'TU',
            'wednesday' => 'WE',
            'thursday'  => 'TH',
            'friday'    => 'FR',
            'saturday'  => 'SA',
            'sunday'    => 'SU',
        ];

        // Carbon integer day-of-week (Sunday = 0, Monday = 1 … Saturday = 6)
        $carbonDowMap = [
            'sunday'    => Carbon::SUNDAY,
            'monday'    => Carbon::MONDAY,
            'tuesday'   => Carbon::TUESDAY,
            'wednesday' => Carbon::WEDNESDAY,
            'thursday'  => Carbon::THURSDAY,
            'friday'    => Carbon::FRIDAY,
            'saturday'  => Carbon::SATURDAY,
        ];

        $uid    = 0;
        $events = '';

        foreach ($enrollments as $enrollment) {
            $section = $enrollment->section;
            if (! $section) {
                continue;
            }

            $slots  = $section->schedule ?? [];
            $term   = $section->academicTerm;
            $course = $section->course;

            $professorName = $section->professor?->user
                ? $section->professor->user->first_name . ' ' . $section->professor->user->last_name
                : '';

            // Use term dates when available; fall back to sane defaults
            $termStart = $term?->starts_at ? (clone $term->starts_at)->startOfDay() : Carbon::now()->startOfWeek();
            $termEnd   = $term?->ends_at   ? (clone $term->ends_at)->endOfDay()     : Carbon::now()->addMonths(4);

            $untilStr = $termEnd->format('Ymd') . 'T235959';

            foreach ($slots as $slot) {
                $day  = strtolower($slot['day'] ?? '');
                $rruleDay = $rruleDayMap[$day] ?? null;
                $carbonDow = $carbonDowMap[$day] ?? null;

                if (! $rruleDay || ! $carbonDow) {
                    continue;
                }

                $startTime = $slot['start_time'] ?? null;
                $endTime   = $slot['end_time'] ?? null;

                if (! $startTime || ! $endTime) {
                    continue;
                }

                // Find the first occurrence of this weekday on or after the term start date
                $firstDay = (clone $termStart);
                if ($firstDay->dayOfWeek !== $carbonDow) {
                    $firstDay->next($carbonDow);
                }

                $dateStr   = $firstDay->format('Ymd');
                $startFmt  = str_replace(':', '', $startTime) . '00';   // HH:MM → HHMMSS
                $endFmt    = str_replace(':', '', $endTime) . '00';

                $dtStart = "{$dateStr}T{$startFmt}";
                $dtEnd   = "{$dateStr}T{$endFmt}";

                $uid++;
                $summary  = ($course?->code ?? '') . ' - ' . ($course?->name ?? '');
                $location = $section->room ?? '';
                $desc     = $professorName ? "Instructor: {$professorName}" : '';

                $events .= "BEGIN:VEVENT\r\n";
                $events .= "UID:unione-{$student->id}-{$uid}@unione.local\r\n";
                $events .= "DTSTART:{$dtStart}\r\n";
                $events .= "DTEND:{$dtEnd}\r\n";
                $events .= "RRULE:FREQ=WEEKLY;BYDAY={$rruleDay};UNTIL={$untilStr}\r\n";
                $events .= "SUMMARY:{$summary}\r\n";
                $events .= "LOCATION:{$location}\r\n";
                if ($desc) {
                    $events .= "DESCRIPTION:{$desc}\r\n";
                }
                $events .= "END:VEVENT\r\n";
            }
        }

        $ics  = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//UniOne//Student Schedule//EN\r\n";
        $ics .= "CALSCALE:GREGORIAN\r\n";
        $ics .= "METHOD:PUBLISH\r\n";
        $ics .= $events;
        $ics .= "END:VCALENDAR\r\n";

        return response($ics, 200)
            ->header('Content-Type', 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="schedule.ics"');
    }
}
