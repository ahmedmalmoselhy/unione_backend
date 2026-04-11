<?php

namespace App\Notifications;

use App\Models\ExamSchedule;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExamSchedulePublished extends Notification
{
    public function __construct(
        public readonly ExamSchedule $examSchedule,
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
        $section = $this->examSchedule->section;
        $course  = $section?->course;

        $courseLabel = $course
            ? "{$course->code} - {$course->name}"
            : 'your section';

        return (new MailMessage)
            ->subject('Exam schedule published')
            ->line("The exam schedule for {$courseLabel} has been published.")
            ->line('Date: ' . optional($this->examSchedule->exam_date)->toDateString())
            ->line('Time: ' . $this->examSchedule->start_time . ' - ' . $this->examSchedule->end_time)
            ->line('Location: ' . ($this->examSchedule->location ?: 'TBA'));
    }

    public function toArray(object $notifiable): array
    {
        $section = $this->examSchedule->section;
        $course  = $section?->course;

        return [
            'type'             => 'exam_schedule_published',
            'title'            => 'Exam schedule published',
            'body'             => 'Exam schedule details are now available for your section.',
            'section_id'       => $section?->id,
            'exam_schedule_id' => $this->examSchedule->id,
            'course_code'      => $course?->code,
            'exam_date'        => optional($this->examSchedule->exam_date)->toDateString(),
            'start_time'       => $this->examSchedule->start_time,
            'end_time'         => $this->examSchedule->end_time,
            'location'         => $this->examSchedule->location,
            'published_at'     => $this->examSchedule->published_at?->toDateTimeString(),
        ];
    }
}
