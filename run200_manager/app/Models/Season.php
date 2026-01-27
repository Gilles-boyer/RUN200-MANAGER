<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Season extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function races(): HasMany
    {
        return $this->hasMany(Race::class);
    }

    public function pointsRules(): HasMany
    {
        return $this->hasMany(SeasonPointsRule::class)->orderBy('position_from');
    }

    public function standings(): HasMany
    {
        return $this->hasMany(SeasonStanding::class);
    }

    public function categoryStandings(): HasMany
    {
        return $this->hasMany(SeasonCategoryStanding::class);
    }

    /**
     * Get published races for this season
     */
    public function publishedRaces(): HasMany
    {
        return $this->races()->where('status', 'PUBLISHED');
    }

    /**
     * Get total number of races in the season
     */
    public function getTotalRacesCountAttribute(): int
    {
        return $this->races()->count();
    }

    /**
     * Get number of published races
     */
    public function getPublishedRacesCountAttribute(): int
    {
        return $this->publishedRaces()->count();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'start_date', 'end_date', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
