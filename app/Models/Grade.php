<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
    protected $fillable = [
        'enrollment_id',
        'midterm',
        'final',
        'coursework',
        'total',
        'letter_grade',
        'grade_points',
        'graded_by',
        'graded_at',
    ];

    protected function casts(): array
    {
        return [
            'midterm' => 'decimal:2',
            'final' => 'decimal:2',
            'coursework' => 'decimal:2',
            'total' => 'decimal:2',
            'grade_points' => 'decimal:2',
            'graded_at' => 'datetime',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function gradedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }
}
