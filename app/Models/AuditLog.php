<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'auditable_type',
        'auditable_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record an audit entry.
     */
    public static function record(
        string  $action,
        string  $auditableType,
        ?int    $auditableId,
        string  $description,
        ?array  $oldValues = null,
        ?array  $newValues = null,
    ): void {
        static::create([
            'user_id'        => auth()->id(),
            'action'         => $action,
            'auditable_type' => $auditableType,
            'auditable_id'   => $auditableId,
            'description'    => $description,
            'old_values'     => $oldValues,
            'new_values'     => $newValues,
            'ip_address'     => request()->ip(),
            'created_at'     => now(),
        ]);
    }
}
