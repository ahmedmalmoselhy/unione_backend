<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = [
        'code',
        'name',
        'name_ar',
        'description',
        'credit_hours',
        'lecture_hours',
        'lab_hours',
        'level',
        'is_elective',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_elective' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'department_course')
            ->withPivot('is_owner');
    }

    public function prerequisites(): BelongsToMany
    {
        return $this->belongsToMany(
            Course::class,
            'course_prerequisites',
            'course_id',
            'prerequisite_id'
        );
    }

    public function dependents(): BelongsToMany
    {
        return $this->belongsToMany(
            Course::class,
            'course_prerequisites',
            'prerequisite_id',
            'course_id'
        );
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }
}
