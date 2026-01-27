<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property bool|null $is_occupied_for_race Dynamic property set when loading spots for a specific race
 * @property RaceRegistration|null $registration_for_race Dynamic property set when loading spots for a specific race
 */
class PaddockSpot extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'spot_number',
        'zone',
        'position_x',
        'position_y',
        'is_available', // Indique si l'emplacement est en service (pas hors service)
        'notes',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'position_x' => 'integer',
        'position_y' => 'integer',
    ];

    // =========================================================================
    // Scopes
    // =========================================================================

    /**
     * Scope pour les emplacements en service
     */
    public function scopeInService(Builder $query): Builder
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope pour les emplacements hors service
     */
    public function scopeOutOfService(Builder $query): Builder
    {
        return $query->where('is_available', false);
    }

    /**
     * Scope pour les emplacements disponibles pour une course spécifique
     */
    public function scopeAvailableForRace(Builder $query, int $raceId): Builder
    {
        return $query->where('is_available', true)
            ->whereDoesntHave('registrations', function ($q) use ($raceId) {
                $q->where('race_id', $raceId)
                    ->whereIn('status', ['ACCEPTED', 'PENDING_VALIDATION']);
            });
    }

    /**
     * Scope pour les emplacements occupés pour une course spécifique
     */
    public function scopeOccupiedForRace(Builder $query, int $raceId): Builder
    {
        return $query->where('is_available', true)
            ->whereHas('registrations', function ($q) use ($raceId) {
                $q->where('race_id', $raceId)
                    ->whereIn('status', ['ACCEPTED', 'PENDING_VALIDATION']);
            });
    }

    /**
     * Scope pour une zone spécifique
     */
    public function scopeInZone(Builder $query, string $zone): Builder
    {
        return $query->where('zone', $zone);
    }

    /**
     * Scope pour trier par numéro d'emplacement
     */
    public function scopeByNumber(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('spot_number', $direction);
    }

    // =========================================================================
    // Relationships
    // =========================================================================

    /**
     * Toutes les inscriptions qui ont utilisé cet emplacement (historique)
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(RaceRegistration::class);
    }

    /**
     * L'inscription pour une course spécifique
     */
    public function registrationForRace(int $raceId): ?RaceRegistration
    {
        /** @var RaceRegistration|null $registration */
        $registration = $this->registrations()
            ->where('race_id', $raceId)
            ->whereIn('status', ['ACCEPTED', 'PENDING_VALIDATION'])
            ->first();

        return $registration;
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Vérifier si l'emplacement est en service
     */
    public function isInService(): bool
    {
        return $this->is_available;
    }

    /**
     * Vérifier si l'emplacement est hors service
     */
    public function isOutOfService(): bool
    {
        return ! $this->is_available;
    }

    /**
     * Vérifier si l'emplacement est disponible pour une course
     */
    public function isAvailableForRace(int $raceId): bool
    {
        if (! $this->isInService()) {
            return false;
        }

        return ! $this->registrations()
            ->where('race_id', $raceId)
            ->whereIn('status', ['ACCEPTED', 'PENDING_VALIDATION'])
            ->exists();
    }

    /**
     * Vérifier si l'emplacement est occupé pour une course
     */
    public function isOccupiedForRace(int $raceId): bool
    {
        return $this->registrations()
            ->where('race_id', $raceId)
            ->whereIn('status', ['ACCEPTED', 'PENDING_VALIDATION'])
            ->exists();
    }

    /**
     * Obtenir le pilote qui occupe cet emplacement pour une course
     */
    public function getPilotForRace(int $raceId): ?Pilot
    {
        /** @var Pilot|null $pilot */
        $pilot = $this->registrationForRace($raceId)?->pilot;

        return $pilot;
    }

    /**
     * Mettre l'emplacement hors service
     */
    public function markAsOutOfService(): void
    {
        $this->update(['is_available' => false]);
    }

    /**
     * Remettre l'emplacement en service
     */
    public function markAsInService(): void
    {
        $this->update(['is_available' => true]);
    }

    /**
     * Obtenir le nom complet de l'emplacement (ex: "Zone A - Emplacement 12")
     */
    public function getFullNameAttribute(): string
    {
        return "Zone {$this->zone} - Emplacement {$this->spot_number}";
    }

    /**
     * Obtenir les coordonnées pour l'affichage sur le plan
     */
    public function getCoordinates(): ?array
    {
        if ($this->position_x && $this->position_y) {
            return [
                'x' => $this->position_x,
                'y' => $this->position_y,
            ];
        }

        return null;
    }

    /**
     * Configure activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['spot_number', 'zone', 'is_available', 'notes'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Obtenir les statistiques des emplacements pour une course
     */
    public static function getStatisticsForRace(int $raceId): array
    {
        $total = static::where('is_available', true)->count();
        $occupied = static::occupiedForRace($raceId)->count();
        $available = $total - $occupied;
        $outOfService = static::where('is_available', false)->count();

        return [
            'total' => $total,
            'available' => $available,
            'occupied' => $occupied,
            'out_of_service' => $outOfService,
            'occupancy_rate' => $total > 0 ? round(($occupied / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Obtenir les statistiques globales (toutes courses confondues)
     */
    public static function getGlobalStatistics(): array
    {
        $total = static::count();
        $inService = static::where('is_available', true)->count();
        $outOfService = $total - $inService;

        return [
            'total' => $total,
            'in_service' => $inService,
            'out_of_service' => $outOfService,
        ];
    }
}
