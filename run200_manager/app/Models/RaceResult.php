<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaceResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'race_id',
        'race_registration_id',
        'result_import_id',
        'position',
        'bib',
        'raw_time',
        'time_ms',
        'pilot_name',
        'car_description',
        'category_name',
    ];

    protected $casts = [
        'position' => 'integer',
        'bib' => 'integer',
        'time_ms' => 'integer',
    ];

    // =========================================================================
    // Relationships
    // =========================================================================

    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(RaceRegistration::class, 'race_registration_id');
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(ResultImport::class, 'result_import_id');
    }

    // =========================================================================
    // Accessors
    // =========================================================================

    /**
     * Get formatted time from milliseconds
     */
    public function getFormattedTimeAttribute(): ?string
    {
        if (! $this->time_ms) {
            return $this->raw_time;
        }

        $ms = $this->time_ms;
        $hours = floor($ms / 3600000);
        $ms %= 3600000;
        $minutes = floor($ms / 60000);
        $ms %= 60000;
        $seconds = floor($ms / 1000);
        $milliseconds = $ms % 1000;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d.%03d', $hours, $minutes, $seconds, $milliseconds);
        }

        if ($minutes > 0) {
            return sprintf('%d:%02d.%03d', $minutes, $seconds, $milliseconds);
        }

        return sprintf('%d.%03d', $seconds, $milliseconds);
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    public function scopeForRace($query, $raceId)
    {
        return $query->where('race_id', $raceId);
    }

    public function scopeOrderedByPosition($query)
    {
        return $query->orderBy('position');
    }

    public function scopeForPilot($query, $pilotId)
    {
        return $query->whereHas('registration', fn ($q) => $q->where('pilot_id', $pilotId));
    }

    public function scopeForCategory($query, $categoryName)
    {
        return $query->where('category_name', $categoryName);
    }

    /**
     * Get podium results (top 3)
     */
    public function scopePodium($query)
    {
        return $query->whereIn('position', [1, 2, 3])->orderBy('position');
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Get points based on position (F1-style points table)
     */
    public function getPoints(?array $pointsTable = null): int
    {
        $pointsTable = $pointsTable ?? [
            1 => 25,
            2 => 18,
            3 => 15,
            4 => 12,
            5 => 10,
            6 => 8,
            7 => 6,
            8 => 4,
            9 => 2,
            10 => 1,
        ];

        return $pointsTable[$this->position] ?? 0;
    }
}
