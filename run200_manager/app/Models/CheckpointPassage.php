<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CheckpointPassage extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'race_registration_id',
        'checkpoint_id',
        'scanned_by',
        'scanned_at',
        'meta',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
        'meta' => 'array',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(RaceRegistration::class, 'race_registration_id');
    }

    public function checkpoint(): BelongsTo
    {
        return $this->belongsTo(Checkpoint::class);
    }

    public function scanner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['checkpoint_id', 'scanned_by', 'scanned_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
