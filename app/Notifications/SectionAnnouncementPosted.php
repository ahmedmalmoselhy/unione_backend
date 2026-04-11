<?php

namespace App\Notifications;

use App\Models\SectionAnnouncement;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SectionAnnouncementPosted extends Notification
{
    public function __construct(
        public readonly SectionAnnouncement $announcement,
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
        $section = $this->announcement->section;
        $course  = $section?->course;

        $courseLabel = $course
            ? "{$course->code} - {$course->name}"
            : 'your section';

        return (new MailMessage)
            ->subject("New announcement: {$this->announcement->title}")
            ->line("A new announcement has been posted for {$courseLabel}.")
            ->line($this->announcement->body);
    }

    public function toArray(object $notifiable): array
    {
        $section = $this->announcement->section;
        $course  = $section?->course;

        return [
            'type'                    => 'section_announcement',
            'title'                   => $this->announcement->title,
            'body'                    => $this->announcement->body,
            'section_id'              => $section?->id,
            'course_code'             => $course?->code,
            'section_announcement_id' => $this->announcement->id,
        ];
    }
}
