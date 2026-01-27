<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class EngagementForm extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'race_registration_id',
        'signature_data',
        'pilot_name',
        'pilot_license_number',
        'pilot_birth_date',
        'pilot_address',
        'pilot_phone',
        'pilot_email',
        'pilot_permit_number',
        'pilot_permit_date',
        'car_make',
        'car_model',
        'car_category',
        'car_race_number',
        'car_cylinders',
        'car_fuel',
        'car_drive',
        'car_has_gas',
        'race_name',
        'race_date',
        'race_location',
        'is_minor',
        'guardian_name',
        'guardian_license_number',
        'guardian_signature_data',
        'witnessed_by',
        'tech_controller_name',
        'tech_checked_at',
        'tech_notes',
        'admin_validated_by',
        'admin_validated_at',
        'admin_notes',
        'signed_at',
        'ip_address',
        'device_info',
    ];

    protected $casts = [
        'pilot_birth_date' => 'date',
        'pilot_permit_date' => 'date',
        'race_date' => 'date',
        'is_minor' => 'boolean',
        'car_has_gas' => 'boolean',
        'car_cylinders' => 'integer',
        'signed_at' => 'datetime',
        'tech_checked_at' => 'datetime',
        'admin_validated_at' => 'datetime',
    ];

    // =========================================================================
    // Relationships
    // =========================================================================

    public function registration(): BelongsTo
    {
        return $this->belongsTo(RaceRegistration::class, 'race_registration_id');
    }

    public function witness(): BelongsTo
    {
        return $this->belongsTo(User::class, 'witnessed_by');
    }

    public function techController(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tech_controller_name');
    }

    public function adminValidator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_validated_by');
    }

    // =========================================================================
    // Activity Log
    // =========================================================================

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['signed_at', 'witnessed_by', 'is_minor'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    public function scopeForRace($query, int $raceId)
    {
        return $query->whereHas('registration', fn ($q) => $q->where('race_id', $raceId));
    }

    public function scopeSignedToday($query)
    {
        return $query->whereDate('signed_at', today());
    }

    public function scopeByWitness($query, int $userId)
    {
        return $query->where('witnessed_by', $userId);
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Check if the form has a guardian signature (required for minors)
     */
    public function hasGuardianSignature(): bool
    {
        return ! empty($this->guardian_signature_data);
    }

    /**
     * Check if the form is complete (has all required signatures)
     */
    public function isComplete(): bool
    {
        if ($this->is_minor) {
            return ! empty($this->signature_data) && ! empty($this->guardian_signature_data);
        }

        return ! empty($this->signature_data);
    }

    /**
     * Get the pilot's full display name
     */
    public function getPilotDisplayNameAttribute(): string
    {
        return $this->pilot_name;
    }

    /**
     * Get the car's full display name
     */
    public function getCarDisplayNameAttribute(): string
    {
        return "{$this->car_make} {$this->car_model} #{$this->car_race_number}";
    }

    /**
     * Create an engagement form from a registration
     *
     * @param  array<string, mixed>  $vehicleDetails
     */
    public static function createFromRegistration(
        RaceRegistration $registration,
        string $signatureData,
        int $witnessedBy,
        ?string $guardianSignatureData = null,
        ?string $ipAddress = null,
        ?string $deviceInfo = null,
        array $vehicleDetails = []
    ): self {
        /** @var \App\Models\Pilot $pilot */
        $pilot = $registration->pilot;
        /** @var \App\Models\Car $car */
        $car = $registration->car;
        /** @var \App\Models\Race $race */
        $race = $registration->race;
        /** @var \App\Models\User|null $user */
        $user = $pilot->user;
        /** @var \App\Models\CarCategory|null $category */
        $category = $car->category;

        $form = self::create([
            'race_registration_id' => $registration->id,
            'signature_data' => $signatureData,
            'pilot_name' => $pilot->full_name,
            'pilot_license_number' => $pilot->license_number,
            'pilot_birth_date' => $pilot->birth_date,
            'pilot_address' => trim("{$pilot->address}, {$pilot->postal_code} {$pilot->city}"),
            'pilot_phone' => $pilot->phone,
            'pilot_email' => $vehicleDetails['pilot_email'] ?? $user?->email,
            'pilot_permit_number' => $vehicleDetails['pilot_permit_number'] ?? null,
            'pilot_permit_date' => $vehicleDetails['pilot_permit_date'] ?? null,
            'car_make' => $car->make,
            'car_model' => $car->model,
            'car_category' => $category?->name ?? 'Non catégorisé',
            'car_race_number' => $car->getAttributes()['race_number'] ?? $car->race_number,
            'car_cylinders' => $vehicleDetails['car_cylinders'] ?? null,
            'car_fuel' => $vehicleDetails['car_fuel'] ?? null,
            'car_drive' => $vehicleDetails['car_drive'] ?? null,
            'car_has_gas' => $vehicleDetails['car_has_gas'] ?? false,
            'race_name' => $race->name,
            'race_date' => $race->race_date,
            'race_location' => $race->location,
            'is_minor' => $pilot->is_minor,
            'guardian_name' => $pilot->is_minor ? "{$pilot->guardian_first_name} {$pilot->guardian_last_name}" : null,
            'guardian_license_number' => $pilot->guardian_license_number,
            'guardian_signature_data' => $guardianSignatureData,
            'witnessed_by' => $witnessedBy,
            'signed_at' => now(),
            'ip_address' => $ipAddress,
            'device_info' => $deviceInfo,
        ]);

        // Dispatch event for email notification
        \App\Events\EngagementFormSigned::dispatch($form);

        return $form;
    }
}
