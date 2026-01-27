<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeasonPointsRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'season_id',
        'position_from',
        'position_to',
        'points',
    ];

    protected $casts = [
        'position_from' => 'integer',
        'position_to' => 'integer',
        'points' => 'integer',
    ];

    // =========================================================================
    // Relationships
    // =========================================================================

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    public function scopeForSeason($query, int $seasonId)
    {
        return $query->where('season_id', $seasonId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('position_from');
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Check if a position falls within this rule's range
     */
    public function coversPosition(int $position): bool
    {
        return $position >= $this->position_from && $position <= $this->position_to;
    }

    /**
     * Get points for a given position from a collection of rules
     */
    public static function getPointsForPosition(int $position, $rules): int
    {
        foreach ($rules as $rule) {
            if ($rule->coversPosition($position)) {
                return $rule->points;
            }
        }

        // Default: 5 points for positions not covered
        return 5;
    }
}
