<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseRating extends Model
{
    protected $fillable = [
        'enrollment_id',
        'rating',
        'comment',
        'rated_at',
    ];

    protected function casts(): array
    {
        return [
            'rating'   => 'integer',
            'rated_at' => 'datetime',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }
}
