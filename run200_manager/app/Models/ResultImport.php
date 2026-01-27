<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ResultImport extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'race_id',
        'uploaded_by',
        'original_filename',
        'stored_path',
        'row_count',
        'status',
        'errors',
    ];

    protected $casts = [
        'errors' => 'array',
        'row_count' => 'integer',
    ];

    // =========================================================================
    // Relationships
    // =========================================================================

    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function results(): HasMany
    {
        return $this->hasMany(RaceResult::class);
    }

    // =========================================================================
    // Activity Log
    // =========================================================================

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'row_count'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // =========================================================================
    // Status Helpers
    // =========================================================================

    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    public function isImported(): bool
    {
        return $this->status === 'IMPORTED';
    }

    public function isFailed(): bool
    {
        return $this->status === 'FAILED';
    }

    public function hasErrors(): bool
    {
        return ! empty($this->errors);
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    public function scopeImported($query)
    {
        return $query->where('status', 'IMPORTED');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'FAILED');
    }

    public function scopeForRace($query, $raceId)
    {
        return $query->where('race_id', $raceId);
    }
}
