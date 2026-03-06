<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class University extends Model
{
    protected $table = 'university';

    protected $fillable = [
        'name',
        'name_ar',
        'address',
        'logo_path',
        'established_at',
        'president_id',
    ];

    protected function casts(): array
    {
        return [
            'established_at' => 'date',
        ];
    }

    public function president(): BelongsTo
    {
        return $this->belongsTo(Professor::class, 'president_id');
    }

    public function vicePresidents(): HasMany
    {
        return $this->hasMany(UniversityVicePresident::class)->orderBy('order');
    }
}
