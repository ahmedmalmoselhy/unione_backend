<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = [
        'user_id',
        'student_number',
        'faculty_id',
        'department_id',
        'academic_year',
        'semester',
        'enrollment_status',
        'gpa',
        'enrolled_at',
        'graduated_at',
    ];

    protected function casts(): array
    {
        return [
            'gpa' => 'decimal:2',
            'enrolled_at' => 'date',
            'graduated_at' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function departmentHistory(): HasMany
    {
        return $this->hasMany(StudentDepartmentHistory::class);
    }
}
