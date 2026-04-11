<?php

namespace App\Notifications;

use App\Models\Enrollment;
use Illuminate\Notifications\Messages\MailMessage;
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
        $channels = ['database'];

        if (! empty($notifiable->email)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $section = $this->enrollment->section;
        $course  = $section->course;

        $gradeText = $this->letterGrade
            ? "Your final grade is {$this->letterGrade}" . ($this->total !== null ? " ({$this->total}/100)." : '.')
            : ($this->total !== null ? "Your final score is {$this->total}/100." : 'Your final grade has been published.');

        return (new MailMessage)
            ->subject("Final grade published: {$course->code}")
            ->line("Course: {$course->code} - {$course->name}")
            ->line($gradeText);
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
