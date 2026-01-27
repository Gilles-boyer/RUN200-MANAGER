<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeasonCategoryStanding extends Model
{
    use HasFactory;

    protected $fillable = [
        'season_id',
        'car_category_id',
        'pilot_id',
        'races_count',
        'base_points',
        'bonus_points',
        'total_points',
        'rank',
        'computed_at',
    ];

    protected $casts = [
        'races_count' => 'integer',
        'base_points' => 'integer',
        'bonus_points' => 'integer',
        'total_points' => 'integer',
        'rank' => 'integer',
        'computed_at' => 'datetime',
    ];

    // =========================================================================
    // Relationships
    // =========================================================================

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CarCategory::class, 'car_category_id');
    }

    public function pilot(): BelongsTo
    {
        return $this->belongsTo(Pilot::class);
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    public function scopeForSeason($query, int $seasonId)
    {
        return $query->where('season_id', $seasonId);
    }

    public function scopeForCategory($query, int $categoryId)
    {
        return $query->where('car_category_id', $categoryId);
    }

    public function scopeRanked($query)
    {
        return $query->whereNotNull('rank')->orderBy('rank');
    }

    public function scopeUnranked($query)
    {
        return $query->whereNull('rank')->orderByDesc('total_points');
    }

    public function scopeWithMinRaces($query, int $minRaces = 2)
    {
        return $query->where('races_count', '>=', $minRaces);
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Check if pilot is eligible for ranking in this category (min 2 races)
     */
    public function isEligibleForRanking(): bool
    {
        return $this->races_count >= 2;
    }

    /**
     * Check if pilot has bonus (participated in all races in this category)
     */
    public function hasBonus(): bool
    {
        return $this->bonus_points > 0;
    }

    /**
     * Get formatted total points with bonus indicator
     */
    public function getFormattedPointsAttribute(): string
    {
        if ($this->hasBonus()) {
            return "{$this->total_points} pts (+{$this->bonus_points} bonus)";
        }

        return "{$this->total_points} pts";
    }

    /**
     * Get rank display (with "NC" for not classified)
     */
    public function getRankDisplayAttribute(): string
    {
        if (! $this->isEligibleForRanking()) {
            return 'NC';
        }

        return $this->rank ? (string) $this->rank : '-';
    }
}
