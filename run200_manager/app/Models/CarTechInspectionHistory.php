<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Historique des contrôles techniques effectués sur les voitures.
 * Permet de tracer tous les contrôles passés avec annotations.
 */
class CarTechInspectionHistory extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'car_id',
        'race_registration_id',
        'tech_inspection_id',
        'status',
        'notes',
        'inspection_details',
        'inspected_by',
        'inspected_at',
    ];

    protected $casts = [
        'inspected_at' => 'datetime',
        'inspection_details' => 'array',
    ];

    // =========================================================================
    // Relationships
    // =========================================================================

    /**
     * La voiture concernée par le contrôle.
     */
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    /**
     * L'inscription à la course associée (si applicable).
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(RaceRegistration::class, 'race_registration_id');
    }

    /**
     * Le contrôle technique principal associé (si applicable).
     */
    public function techInspection(): BelongsTo
    {
        return $this->belongsTo(TechInspection::class);
    }

    /**
     * L'inspecteur qui a réalisé le contrôle.
     */
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
            ->logOnly(['status', 'notes', 'inspection_details'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // =========================================================================
    // Accessors & Helpers
    // =========================================================================

    /**
     * Vérifie si le contrôle est OK.
     */
    public function isOk(): bool
    {
        return $this->status === 'OK';
    }

    /**
     * Vérifie si le contrôle a échoué.
     */
    public function isFail(): bool
    {
        return $this->status === 'FAIL';
    }

    /**
     * Obtient le nom complet de la voiture.
     */
    public function getCarFullNameAttribute(): string
    {
        return $this->car ? $this->car->full_name : 'N/A';
    }

    /**
     * Obtient le nom de l'inspecteur.
     */
    public function getInspectorNameAttribute(): string
    {
        return $this->inspector ? $this->inspector->name : 'Inconnu';
    }

    /**
     * Obtient le nom de la course (si applicable).
     */
    public function getRaceNameAttribute(): ?string
    {
        return $this->registration?->race?->name;
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    /**
     * Scope pour filtrer par voiture.
     */
    public function scopeForCar($query, int $carId)
    {
        return $query->where('car_id', $carId);
    }

    /**
     * Scope pour filtrer par statut.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope pour filtrer par inspecteur.
     */
    public function scopeByInspector($query, int $inspectorId)
    {
        return $query->where('inspected_by', $inspectorId);
    }

    /**
     * Scope pour obtenir uniquement les contrôles OK.
     */
    public function scopeOkOnly($query)
    {
        return $query->where('status', 'OK');
    }

    /**
     * Scope pour obtenir uniquement les contrôles échoués.
     */
    public function scopeFailedOnly($query)
    {
        return $query->where('status', 'FAIL');
    }

    /**
     * Scope pour trier par date décroissante (plus récents en premier).
     */
    public function scopeLatestFirst($query)
    {
        return $query->orderBy('inspected_at', 'desc');
    }
}
