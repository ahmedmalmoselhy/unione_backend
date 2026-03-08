<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Section;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $admin      = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->first();
        $faculties  = Faculty::where('is_active', true)->pluck('id');
        $departments = Department::where('is_active', true)->pluck('id');
        $sections   = Section::where('is_active', true)->pluck('id');

        $now = Carbon::now();

        $announcements = [
            // University-wide
            [
                'title'      => 'Welcome to the New Academic Year 2025',
                'body'       => "We are pleased to welcome all students, faculty, and staff to the new academic year. Let's make this a productive and rewarding year for everyone.",
                'type'       => 'general',
                'visibility' => 'university',
                'target_id'  => null,
                'published_at' => $now->copy()->subDays(30),
                'expires_at'   => null,
            ],
            [
                'title'      => 'Campus Maintenance: Water Supply Interruption',
                'body'       => 'Please be advised that the campus water supply will be interrupted on Saturday from 8AM–4PM for scheduled maintenance. Plan accordingly.',
                'type'       => 'administrative',
                'visibility' => 'university',
                'target_id'  => null,
                'published_at' => $now->copy()->subDays(5),
                'expires_at'   => $now->copy()->addDays(2),
            ],
            [
                'title'      => 'URGENT: Campus Closure Due to Weather Alert',
                'body'       => 'Due to severe weather forecasts, the campus will be closed tomorrow. All classes are cancelled. Stay safe and monitor official channels for updates.',
                'type'       => 'urgent',
                'visibility' => 'university',
                'target_id'  => null,
                'published_at' => $now->copy()->subDays(2),
                'expires_at'   => $now->copy()->subDay(),
            ],
            [
                'title'      => 'Registration for Spring 2026 Opens Soon',
                'body'       => 'Registration for the Spring 2026 semester will open on December 15. Please review the course catalog and meet with your academic advisor before registering.',
                'type'       => 'academic',
                'visibility' => 'university',
                'target_id'  => null,
                'published_at' => null, // Draft
                'expires_at'   => null,
            ],
        ];

        // Faculty-scoped
        foreach ($faculties->take(3) as $i => $facultyId) {
            $announcements[] = [
                'title'      => 'Faculty Meeting — End of Semester Review',
                'body'       => 'All faculty staff are required to attend the end-of-semester review meeting. Please check your faculty email for specific date and time.',
                'type'       => 'administrative',
                'visibility' => 'faculty',
                'target_id'  => $facultyId,
                'published_at' => $now->copy()->subDays(10 + $i),
                'expires_at'   => $now->copy()->addDays(20),
            ];
        }

        // Department-scoped
        foreach ($departments->take(4) as $i => $deptId) {
            $announcements[] = [
                'title'      => 'Department Seminar: Research Showcase',
                'body'       => 'Our department is hosting a research showcase next week. Students and faculty are encouraged to attend. Refreshments will be provided.',
                'type'       => 'academic',
                'visibility' => 'department',
                'target_id'  => $deptId,
                'published_at' => $now->copy()->subDays(7 + $i),
                'expires_at'   => $now->copy()->addDays(7),
            ];
        }

        // Section-scoped
        foreach ($sections->take(3) as $i => $sectionId) {
            $announcements[] = [
                'title'      => 'Midterm Exam Schedule Update',
                'body'       => 'The midterm exam for this section has been rescheduled. Please check the updated schedule on the course portal.',
                'type'       => 'academic',
                'visibility' => 'section',
                'target_id'  => $sectionId,
                'published_at' => $now->copy()->subDays(3 + $i),
                'expires_at'   => $now->copy()->addDays(14),
            ];
        }

        foreach ($announcements as $data) {
            Announcement::create(array_merge($data, [
                'author_id' => $admin->id,
            ]));
        }
    }
}
