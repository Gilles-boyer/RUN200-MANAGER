<?php

namespace App\Models;

use App\Casts\TitleCaseCast;
use App\Casts\UppercaseCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Race extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'season_id',
        'name',
        'slug',
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

    // =========================================================================
    // Boot
    // =========================================================================

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Race $race) {
            if (empty($race->slug)) {
                $race->slug = static::generateUniqueSlug($race->name, $race->race_date);
            }
        });

        static::updating(function (Race $race) {
            // Regénérer le slug si le nom ou la date change
            if ($race->isDirty(['name', 'race_date']) && !$race->isDirty('slug')) {
                $race->slug = static::generateUniqueSlug($race->name, $race->race_date, $race->id);
            }
        });
    }

    /**
     * Générer un slug unique pour la course
     */
    public static function generateUniqueSlug(string $name, $date, ?int $excludeId = null): string
    {
        $dateString = $date instanceof \DateTimeInterface ? $date->format('Y-m-d') : $date;
        $baseSlug = Str::slug($name . '-' . $dateString);
        $slug = $baseSlug;
        $counter = 1;

        $query = static::where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;

            $query = static::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }

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

    /**
     * Documents officiels de la course (tableau d'affichage)
     *
     * @return HasMany<RaceDocument, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(RaceDocument::class);
    }

    /**
     * Documents publiés de la course
     *
     * @return HasMany<RaceDocument, $this>
     */
    public function publishedDocuments(): HasMany
    {
        /** @var HasMany<RaceDocument, $this> */
        return $this->hasMany(RaceDocument::class)
            ->where('status', 'PUBLISHED')
            ->with('category')
            ->orderBy('sort_order');
    }

    /**
     * URL publique du tableau d'affichage
     */
    public function getBoardUrlAttribute(): string
    {
        return route('board.show', $this->slug);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'slug', 'race_date', 'location', 'status', 'entry_fee_cents'])
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
