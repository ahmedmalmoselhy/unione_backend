<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = [
        'faculty_id',
        'name',
        'name_ar',
        'code',
        'type',
        'is_preparatory',
        'head_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_preparatory' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    public function head(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_id');
    }

    public function professors(): HasMany
    {
        return $this->hasMany(Professor::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'department_course')
            ->withPivot('is_owner');
    }

    public function departmentHistoriesFrom(): HasMany
    {
        return $this->hasMany(StudentDepartmentHistory::class, 'from_department_id');
    }

    public function departmentHistoriesTo(): HasMany
    {
        return $this->hasMany(StudentDepartmentHistory::class, 'to_department_id');
    }
}
