<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentTermGpa extends Model
{
    protected $fillable = [
        'student_id',
        'academic_term_id',
        'gpa',
        'credit_hours',
    ];

    protected function casts(): array
    {
        return [
            'gpa'          => 'decimal:2',
            'credit_hours' => 'integer',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicTerm(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class);
    }
}
