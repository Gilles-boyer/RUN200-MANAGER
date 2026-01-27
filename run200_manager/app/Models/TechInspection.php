<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class TechInspection extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'race_registration_id',
        'status',
        'notes',
        'inspected_by',
        'inspected_at',
    ];

    protected $casts = [
        'inspected_at' => 'datetime',
    ];

    // =========================================================================
    // Relationships
    // =========================================================================

    public function registration(): BelongsTo
    {
        return $this->belongsTo(RaceRegistration::class, 'race_registration_id');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspected_by');
    }

    // =========================================================================
    // Activity Log
    // =========================================================================

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'notes'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // =========================================================================
    // Status Helpers
    // =========================================================================

    public function isOk(): bool
    {
        return $this->status === 'OK';
    }

    public function isFail(): bool
    {
        return $this->status === 'FAIL';
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    public function scopeOk($query)
    {
        return $query->where('status', 'OK');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'FAIL');
    }

    public function scopeByInspector($query, $userId)
    {
        return $query->where('inspected_by', $userId);
    }
}
