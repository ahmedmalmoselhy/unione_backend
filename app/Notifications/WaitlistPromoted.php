<?php

namespace App\Notifications;

use App\Models\Enrollment;
use Illuminate\Notifications\Notification;

class WaitlistPromoted extends Notification
{
    public function __construct(public readonly Enrollment $enrollment) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $section = $this->enrollment->section;
        $course  = $section->course;
        $term    = $this->enrollment->academicTerm;

        return [
            'type'          => 'waitlist_promoted',
            'title'         => 'Enrolled from Waitlist',
            'body'          => "A spot opened up! You have been enrolled in {$course->code} — {$course->name} ({$term->name}).",
            'enrollment_id' => $this->enrollment->id,
            'section_id'    => $section->id,
            'course_code'   => $course->code,
        ];
    }
}
