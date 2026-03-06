<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UniversityVicePresident extends Model
{
    protected $fillable = [
        'professor_id',
        'title',
        'title_ar',
        'order',
        'is_active',
        'appointed_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active'    => 'boolean',
            'appointed_at' => 'date',
            'ended_at'     => 'date',
        ];
    }

    public function professor(): BelongsTo
    {
        return $this->belongsTo(Professor::class);
    }
}
