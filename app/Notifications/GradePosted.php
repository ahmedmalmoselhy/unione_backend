<?php

namespace App\Notifications;

use App\Models\Enrollment;
use Illuminate\Notifications\Notification;

class GradePosted extends Notification
{
    public function __construct(
        public readonly Enrollment $enrollment,
        public readonly ?string $letterGrade,
        public readonly ?float $total,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $section = $this->enrollment->section;
        $course  = $section->course;

        $gradeText = $this->letterGrade
            ? "Grade: {$this->letterGrade}" . ($this->total !== null ? " ({$this->total}/100)" : '')
            : ($this->total !== null ? "Total: {$this->total}/100" : 'Grade has been recorded.');

        return [
            'type'          => 'grade_posted',
            'title'         => 'Grade Posted',
            'body'          => "Your grade for {$course->code} — {$course->name} has been submitted. {$gradeText}",
            'enrollment_id' => $this->enrollment->id,
            'section_id'    => $section->id,
            'course_code'   => $course->code,
            'letter_grade'  => $this->letterGrade,
            'total'         => $this->total,
        ];
    }
}
