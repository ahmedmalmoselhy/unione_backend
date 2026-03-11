<?php

namespace App\Notifications;

use App\Models\SectionAnnouncement;
use Illuminate\Notifications\Notification;

class SectionAnnouncementPosted extends Notification
{
    public function __construct(
        public readonly SectionAnnouncement $announcement,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
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
