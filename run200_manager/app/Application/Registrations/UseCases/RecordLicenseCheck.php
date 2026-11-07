<?php

declare(strict_types=1);

namespace App\Application\Registrations\UseCases;

use App\Models\Pilot;
use App\Models\Race;
use App\Models\RaceRegistration;
use App\Models\RaceRegistrationLicenseCheck;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Facades\Activity;

/** Record an operator's observation; this class does not contact or impersonate FFSA. */
final class RecordLicenseCheck
{
    /**
     * @param  array<string, mixed>  $data  The observation entered by the operator.
     * @param  array<string, mixed>  $expected  The pilot, race and check snapshot shown before confirmation.
     */
    public function execute(int $registrationId, array $data, array $expected): RaceRegistrationLicenseCheck
    {
        $actor = Auth::user();
        if (! $actor instanceof User || ! $actor->can('license.verify')) {
            throw new AuthorizationException('Vous ne pouvez pas vérifier les licences.');
        }

        $validated = Validator::make($data, [
            'result' => ['required', Rule::in(['valid', 'invalid', 'needs_review'])],
            'source' => ['required', Rule::in(['verification_ffsa', 'ligue_ffsa', 'licence_ffsa', 'official_ffsa_document'])],
            'source_access_status' => ['required', Rule::in(['consulted', 'unavailable', 'incomplete'])],
            'license_type_observed' => ['nullable', 'string', 'max:32'],
            'license_state_observed' => ['nullable', Rule::in(['active', 'inactive', 'expired', 'suspended', 'unknown'])],
            'license_year_observed' => ['nullable', 'integer', 'between:1900,2100'],
            'identity_matches' => ['nullable', 'boolean'],
            'license_number_matches' => ['nullable', 'boolean'],
            'valid_for_race_date' => ['nullable', 'boolean'],
            'valid_until' => ['nullable', 'date_format:Y-m-d'],
            'reason_code' => ['nullable', 'string', 'max:40'],
            'note' => ['nullable', 'string', 'max:500'],
        ])->validate();

        $validated['license_type_observed'] = isset($validated['license_type_observed'])
            ? strtoupper(trim((string) $validated['license_type_observed'])) : null;
        $validated['license_state_observed'] ??= null;
        $validated['reason_code'] = isset($validated['reason_code'])
            ? trim((string) $validated['reason_code']) : null;
        $validated['note'] = isset($validated['note']) ? trim((string) $validated['note']) : null;
        $validated['license_year_observed'] = isset($validated['license_year_observed'])
            ? (int) $validated['license_year_observed'] : null;
        foreach (['identity_matches', 'license_number_matches', 'valid_for_race_date'] as $field) {
            $validated[$field] = isset($validated[$field])
                ? filter_var($validated[$field], FILTER_VALIDATE_BOOLEAN) : null;
        }
        $validated['valid_until'] ??= null;

        return DB::transaction(function () use ($registrationId, $validated, $expected, $actor): RaceRegistrationLicenseCheck {
            $registration = RaceRegistration::query()->lockForUpdate()->findOrFail($registrationId);
            $pilot = Pilot::query()->lockForUpdate()->findOrFail($registration->pilot_id);
            $race = Race::query()->lockForUpdate()->findOrFail($registration->race_id);
            $check = RaceRegistrationLicenseCheck::query()
                ->where('race_registration_id', $registration->id)
                ->lockForUpdate()->first();

            if ($race->required_license_code === null) {
                throw ValidationException::withMessages([
                    'licenseCheck' => 'Aucune licence obligatoire n’est configurée pour cette course.',
                ]);
            }

            $actual = [
                'pilot_id' => $pilot->id,
                'license_number' => (string) $pilot->license_number,
                'first_name' => (string) $pilot->first_name,
                'last_name' => (string) $pilot->last_name,
                'race_date' => $race->race_date->toDateString(),
                'required_license_code' => $race->required_license_code,
                'check_id' => $check?->id,
                'check_updated_at' => $check?->updated_at?->format('Y-m-d H:i:s.u'),
                'check_revision' => $check?->revision,
            ];
            foreach ($actual as $key => $value) {
                if (! array_key_exists($key, $expected) || $expected[$key] !== $value) {
                    throw ValidationException::withMessages([
                        'licenseCheck' => 'Le dossier ou un contrôle a changé. Rechargez la liste puis vérifiez à nouveau.',
                    ]);
                }
            }

            $this->assertConsistentObservation($validated, $race);

            $before = $check?->result;
            $attributes = [
                'license_number_snapshot' => (string) $pilot->license_number,
                'pilot_first_name_snapshot' => (string) $pilot->first_name,
                'pilot_last_name_snapshot' => (string) $pilot->last_name,
                'race_date_snapshot' => $race->race_date->toDateString(),
                'required_license_code_snapshot' => $race->required_license_code,
                'result' => $validated['result'],
                'license_type_observed' => $validated['license_type_observed'],
                'license_state_observed' => $validated['license_state_observed'],
                'license_year_observed' => $validated['license_year_observed'] ?? null,
                'identity_matches' => $validated['identity_matches'] ?? null,
                'license_number_matches' => $validated['license_number_matches'] ?? null,
                'valid_for_race_date' => $validated['valid_for_race_date'] ?? null,
                'valid_until' => $validated['valid_until'] ?? null,
                'source' => $validated['source'],
                'source_access_status' => $validated['source_access_status'],
                'reason_code' => $validated['reason_code'],
                'note' => $validated['note'],
                'checked_at' => now(),
                'checked_by' => $actor->id,
                'revision' => ($check?->revision ?? 0) + 1,
            ];

            if ($check === null) {
                $check = RaceRegistrationLicenseCheck::create(['race_registration_id' => $registration->id, ...$attributes]);
            } else {
                $check->update($attributes);
            }

            Activity::performedOn($registration)->causedBy($actor)->withProperties([
                'license_check_id' => $check->id,
                'previous_result' => $before,
                'result' => $check->result,
                'source' => $check->source,
                'source_access_status' => $check->source_access_status,
                'reason_code' => $check->reason_code,
                'revision' => $check->revision,
            ])->log('registration.license_checked');

            return $check->refresh();
        });
    }

    /** @param array<string, mixed> $data */
    private function assertConsistentObservation(array $data, Race $race): void
    {
        $result = $data['result'];
        $source = $data['source'];
        $access = $data['source_access_status'];
        $reason = $data['reason_code'];
        $observedType = $data['license_type_observed'];

        if ($result === 'valid') {
            if ($source !== 'ligue_ffsa' || $access !== 'consulted'
                || $observedType !== strtoupper($race->required_license_code)
                || $data['license_state_observed'] !== 'active'
                || ($data['license_year_observed'] ?? null) !== (int) $race->race_date->format('Y')
                || ($data['identity_matches'] ?? null) !== true
                || ($data['license_number_matches'] ?? null) !== true
                || ($data['valid_for_race_date'] ?? null) !== true
                || ($data['valid_until'] ?? null) === null
                || $data['valid_until'] < $race->race_date->toDateString()
                || $reason !== null) {
                throw ValidationException::withMessages([
                    'result' => 'La licence ne peut être indiquée vérifiée qu’après contrôle sur la ligue FFSA du code, de l’état actif, de la date de fin, du numéro et de l’identité.',
                ]);
            }

            return;
        }

        if ($result === 'invalid') {
            $isConfirmedInvalid = match ($reason) {
                'revoked' => $data['license_state_observed'] === 'suspended'
                    && $observedType === strtoupper($race->required_license_code)
                    && $data['license_year_observed'] === (int) $race->race_date->format('Y'),
                'expired' => $data['license_state_observed'] === 'expired'
                    && $observedType === strtoupper($race->required_license_code)
                    && $data['license_year_observed'] === (int) $race->race_date->format('Y')
                    && $data['valid_until'] !== null
                    && $data['valid_until'] < $race->race_date->toDateString(),
                'no_current_license_confirmed' => $data['license_state_observed'] === 'inactive'
                    && ($observedType === null || $observedType === strtoupper($race->required_license_code))
                    && ($data['license_year_observed'] === null
                        || $data['license_year_observed'] === (int) $race->race_date->format('Y'))
                    && ($data['valid_until'] === null
                        || $data['valid_until'] < $race->race_date->toDateString()),
                default => false,
            };

            if ($access !== 'consulted' || $source !== 'ligue_ffsa'
                || ! in_array($reason, ['expired', 'revoked', 'no_current_license_confirmed'], true)
                || ($data['identity_matches'] ?? null) !== true
                || ($data['license_number_matches'] ?? null) !== true
                || ($data['valid_for_race_date'] ?? null) !== false
                || ! $isConfirmedInvalid) {
                throw ValidationException::withMessages([
                    'result' => 'Confirmez sur la ligue FFSA un état défavorable cohérent avec la date de course, le numéro et l’identité. En cas de doute, indiquez « À examiner ».',
                ]);
            }

            return;
        }

        if (! in_array($reason, [
            'other_title_to_review', 'not_found', 'identity_uncertain', 'number_uncertain',
            'source_unavailable', 'ambiguous', 'document_only', 'other',
        ], true)) {
            throw ValidationException::withMessages(['reason_code' => 'Indiquez pourquoi la licence reste à examiner.']);
        }
        if ($source === 'official_ffsa_document' && $reason !== 'document_only') {
            throw ValidationException::withMessages(['reason_code' => 'Un document présenté seul doit rester à examiner.']);
        }
        if ($access === 'unavailable' && $reason !== 'source_unavailable') {
            throw ValidationException::withMessages(['reason_code' => 'Indiquez que la source FFSA était indisponible.']);
        }
    }
}
