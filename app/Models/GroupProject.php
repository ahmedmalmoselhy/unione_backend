<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupProject extends Model
{
    protected $fillable = [
        'section_id',
        'title',
        'description',
        'due_at',
        'max_members',
        'is_active',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'is_active' => 'boolean',
            'max_members' => 'integer',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(GroupProjectMember::class);
    }
}
