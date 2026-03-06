<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentDepartmentHistory extends Model
{
    protected $table = 'student_department_history';

    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'from_department_id',
        'to_department_id',
        'switched_at',
        'switched_by',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'switched_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function fromDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'from_department_id');
    }

    public function toDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'to_department_id');
    }

    public function switchedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'switched_by');
    }
}
