<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    protected $fillable = [
        'course_id',
        'professor_id',
        'academic_term_id',
        'capacity',
        'room',
        'schedule',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'schedule' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function professor(): BelongsTo
    {
        return $this->belongsTo(Professor::class);
    }

    public function academicTerm(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function waitlists(): HasMany
    {
        return $this->hasMany(EnrollmentWaitlist::class);
    }

    public function attendanceSessions(): HasMany
    {
        return $this->hasMany(AttendanceSession::class);
    }

    public function sectionAnnouncements(): HasMany
    {
        return $this->hasMany(SectionAnnouncement::class);
    }

    public function teachingAssistants(): HasMany
    {
        return $this->hasMany(SectionTeachingAssistant::class);
    }

    public function examSchedule(): HasOne
    {
        return $this->hasOne(ExamSchedule::class);
    }

    public function groupProjects(): HasMany
    {
        return $this->hasMany(GroupProject::class);
    }
}
