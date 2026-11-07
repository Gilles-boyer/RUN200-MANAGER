<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaceRegistrationLicenseCheck extends Model
{
    protected $fillable = [
        'race_registration_id',
        'license_number_snapshot',
        'pilot_first_name_snapshot',
        'pilot_last_name_snapshot',
        'race_date_snapshot',
        'required_license_code_snapshot',
        'result',
        'license_type_observed',
        'license_state_observed',
        'license_year_observed',
        'identity_matches',
        'license_number_matches',
        'valid_for_race_date',
        'valid_until',
        'source',
        'source_access_status',
        'reason_code',
        'note',
        'checked_at',
        'checked_by',
        'revision',
    ];

    protected function casts(): array
    {
        return [
            'race_date_snapshot' => 'date',
            'license_year_observed' => 'integer',
            'identity_matches' => 'boolean',
            'license_number_matches' => 'boolean',
            'valid_for_race_date' => 'boolean',
            'valid_until' => 'date',
            'checked_at' => 'datetime',
            'revision' => 'integer',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(RaceRegistration::class, 'race_registration_id');
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function isCurrentFor(RaceRegistration $registration): bool
    {
        return $this->license_number_snapshot === (string) $registration->pilot->license_number
            && $this->pilot_first_name_snapshot === (string) $registration->pilot->first_name
            && $this->pilot_last_name_snapshot === (string) $registration->pilot->last_name
            && $this->race_date_snapshot?->toDateString() === $registration->race->race_date?->toDateString()
            && $this->required_license_code_snapshot === $registration->race->required_license_code;
    }
}
