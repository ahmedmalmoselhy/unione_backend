<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
    ];

    protected function casts(): array
    {
        return [
            'established_at' => 'date',
        ];
    }
}
