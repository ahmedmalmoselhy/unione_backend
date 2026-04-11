<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamSchedule extends Model
{
    protected $fillable = [
        'section_id',
        'exam_date',
        'start_time',
        'end_time',
        'location',
        'is_published',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'exam_date' => 'date',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
}
