<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
*/

// User's private notification channel
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Admin-only channel for system-wide announcements
Broadcast::channel('admin', function ($user) {
    return $user->hasAnyRole(['admin', 'university_admin', 'faculty_admin']);
});

// Section-specific channel
Broadcast::channel('section.{sectionId}', function ($user, $sectionId) {
    // Check if user is enrolled in or teaching this section
    return $user->student()?->enrollments()
        ->where('section_id', $sectionId)
        ->whereIn('status', ['registered', 'completed'])
        ->exists()
        || $user->professor()?->sections()
            ->where('sections.id', $sectionId)
            ->exists();
});
