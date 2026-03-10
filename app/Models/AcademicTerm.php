<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicTerm extends Model
{
    protected $fillable = [
        'name',
        'name_ar',
        'academic_year',
        'semester',
        'starts_at',
        'ends_at',
        'registration_starts_at',
        'registration_ends_at',
        'withdrawal_deadline',
        'exam_starts_at',
        'exam_ends_at',
        'grade_submission_deadline',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'starts_at'                => 'date',
            'ends_at'                  => 'date',
            'registration_starts_at'   => 'date',
            'registration_ends_at'     => 'date',
            'withdrawal_deadline'      => 'date',
            'exam_starts_at'           => 'date',
            'exam_ends_at'             => 'date',
            'grade_submission_deadline' => 'date',
            'is_active'                => 'boolean',
        ];
    }

    public function getLocalNameAttribute(): string
    {
        return app()->getLocale() === 'ar' ? ($this->name_ar ?: $this->name) : $this->name;
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }
}
