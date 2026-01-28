<?php

namespace App\Models;

use App\Casts\LicenseNumberCast;
use App\Casts\PhoneNumberCast;
use App\Casts\PostalCodeCast;
use App\Casts\TitleCaseCast;
use App\Casts\UppercaseCast;
use App\Domain\Pilot\ValueObjects\LicenseNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Pilot extends Model
{
    use HasFactory, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'license_number',
        'birth_date',
        'birth_place',
        'phone',
        'permit_number',
        'permit_date',
        'address',
        'city',
        'postal_code',
        'photo_path',
        'is_minor',
        'guardian_first_name',
        'guardian_last_name',
        'guardian_license_number',
        'guardian_phone',
        'emergency_contact_name',
        'emergency_contact_phone',
        'medical_certificate_date',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'permit_date' => 'date',
            'medical_certificate_date' => 'date',
            'is_minor' => 'boolean',
            // Formatage des données
            'first_name' => TitleCaseCast::class,
            'last_name' => UppercaseCast::class,
            'birth_place' => TitleCaseCast::class,
            'city' => UppercaseCast::class,
            'postal_code' => PostalCodeCast::class,
            'phone' => PhoneNumberCast::class,
            'license_number' => LicenseNumberCast::class,
            'guardian_first_name' => TitleCaseCast::class,
            'guardian_last_name' => UppercaseCast::class,
            'guardian_license_number' => LicenseNumberCast::class,
            'emergency_contact_name' => TitleCaseCast::class,
            'emergency_contact_phone' => PhoneNumberCast::class,
        ];
    }

    /**
     * Get the user that owns the pilot profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the cars owned by the pilot.
     */
    public function cars(): HasMany
    {
        return $this->hasMany(Car::class);
    }

    /**
     * Get the race registrations for the pilot.
     */
    public function raceRegistrations(): HasMany
    {
        return $this->hasMany(RaceRegistration::class);
    }

    /**
     * Get the pilot's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the license number as ValueObject.
     */
    public function getLicenseAttribute(): LicenseNumber
    {
        return LicenseNumber::fromString($this->license_number);
    }

    /**
     * Configure activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'first_name',
                'last_name',
                'license_number',
                'phone',
                'address',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Scope a query to only include minors.
     */
    public function scopeWhereIsMinor($query)
    {
        return $query->where('is_minor', true);
    }

    /**
     * Scope a query to only include active season pilots.
     */
    public function scopeWhereActiveSeason($query)
    {
        return $query->where('is_active_season', true);
    }

    /**
     * Scope a query to find by license number.
     */
    public function scopeWhereLicenseNumber($query, string $license)
    {
        return $query->where('license_number', $license);
    }

    /**
     * Required fields for a complete profile.
     */
    public static function requiredFields(): array
    {
        return [
            'first_name',
            'last_name',
            'license_number',
            'birth_date',
            'birth_place',
            'phone',
            'address',
            'city',
            'postal_code',
            'photo_path',
            'emergency_contact_name',
            'emergency_contact_phone',
        ];
    }

    /**
     * Check if the pilot's profile is complete.
     */
    public function isProfileComplete(): bool
    {
        foreach (self::requiredFields() as $field) {
            if (empty($this->{$field})) {
                return false;
            }
        }

        // Additional check for minors - guardian info required
        if ($this->is_minor) {
            $guardianFields = [
                'guardian_first_name',
                'guardian_last_name',
                'emergency_contact_name',
                'emergency_contact_phone',
            ];

            foreach ($guardianFields as $field) {
                if (empty($this->{$field})) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get the profile completion percentage.
     */
    public function getProfileCompletionPercentage(): int
    {
        $requiredFields = self::requiredFields();

        if ($this->is_minor) {
            $requiredFields = array_merge($requiredFields, [
                'guardian_first_name',
                'guardian_last_name',
            ]);
        }

        $totalFields = count($requiredFields);
        $completedFields = 0;

        foreach ($requiredFields as $field) {
            if (! empty($this->{$field})) {
                $completedFields++;
            }
        }

        return $totalFields > 0 ? (int) round(($completedFields / $totalFields) * 100) : 0;
    }

    /**
     * Get the list of missing fields for profile completion.
     */
    public function getMissingFields(): array
    {
        $missingFields = [];
        $requiredFields = self::requiredFields();

        if ($this->is_minor) {
            $requiredFields = array_merge($requiredFields, [
                'guardian_first_name',
                'guardian_last_name',
            ]);
        }

        foreach ($requiredFields as $field) {
            if (empty($this->{$field})) {
                $missingFields[] = $field;
            }
        }

        return $missingFields;
    }

    /**
     * Check if the pilot can register for a race.
     * Requirements: complete profile + at least one car
     */
    public function canRegisterForRace(): bool
    {
        return $this->isProfileComplete() && $this->cars()->count() > 0;
    }

    /**
     * Get reasons why pilot cannot register for race.
     */
    public function getRegistrationBlockingReasons(): array
    {
        $reasons = [];

        if (! $this->isProfileComplete()) {
            $reasons[] = 'Votre profil n\'est pas complet à 100%. Veuillez compléter tous les champs obligatoires.';
        }

        if ($this->cars()->count() === 0) {
            $reasons[] = 'Vous devez enregistrer au moins une voiture pour vous inscrire à une course.';
        }

        return $reasons;
    }
}
