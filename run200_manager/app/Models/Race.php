<?php

namespace App\Models;

use App\Casts\TitleCaseCast;
use App\Casts\UppercaseCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Race extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'season_id',
        'name',
        'race_date',
        'location',
        'status',
        'entry_fee_cents',
    ];

    protected $casts = [
        'race_date' => 'date',
        'entry_fee_cents' => 'integer',
        // Formatage des données
        'name' => TitleCaseCast::class,
        'location' => UppercaseCast::class,
    ];

    /**
     * Get the entry fee in cents (uses default if not set)
     */
    public function getEntryFeeCentsAttribute($value): int
    {
        return $value ?? (int) config('stripe.registration_fee_cents', 5000);
    }

    /**
     * Get the entry fee formatted for display (e.g., "50,00 €")
     */
    public function getFormattedEntryFeeAttribute(): string
    {
        return number_format($this->entry_fee_cents / 100, 2, ',', ' ').' €';
    }

    /**
     * Get the entry fee in euros (decimal)
     */
    public function getEntryFeeAttribute(): float
    {
        return $this->entry_fee_cents / 100;
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(RaceRegistration::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(RaceResult::class);
    }

    public function resultImports(): HasMany
    {
        return $this->hasMany(ResultImport::class);
    }

    public function latestImport()
    {
        return $this->hasOne(ResultImport::class)->latestOfMany();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'race_date', 'location', 'status', 'entry_fee_cents'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'OPEN');
    }

    public function isOpen(): bool
    {
        return $this->status === 'OPEN';
    }

    public function isDraft(): bool
    {
        return $this->status === 'DRAFT';
    }

    public function isClosed(): bool
    {
        return $this->status === 'CLOSED';
    }

    public function isResultsReady(): bool
    {
        return $this->status === 'RESULTS_READY';
    }

    public function isPublished(): bool
    {
        return $this->status === 'PUBLISHED';
    }

    public function canImportResults(): bool
    {
        return in_array($this->status, ['CLOSED', 'RUNNING', 'RESULTS_READY']);
    }

    public function canPublishResults(): bool
    {
        return $this->status === 'RESULTS_READY';
    }
}
