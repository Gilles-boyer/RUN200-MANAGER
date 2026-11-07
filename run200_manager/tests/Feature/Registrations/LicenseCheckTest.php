<?php

declare(strict_types=1);

use App\Application\Registrations\UseCases\RecordLicenseCheck;
use App\Models\RaceRegistration;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function licenseCheckRegistration(bool $configured = true): RaceRegistration
{
    $registration = RaceRegistration::factory()->accepted()->create();
    $registration->race->update([
        'race_date' => '2026-11-08',
        'required_license_code' => $configured ? 'RCCR' : null,
    ]);

    return $registration->fresh(['pilot', 'race', 'licenseCheck']);
}

function licenseCheckExpected(RaceRegistration $registration): array
{
    $registration->load(['pilot', 'race', 'licenseCheck']);

    return [
        'pilot_id' => $registration->pilot_id,
        'license_number' => (string) $registration->pilot->license_number,
        'first_name' => (string) $registration->pilot->first_name,
        'last_name' => (string) $registration->pilot->last_name,
        'race_date' => $registration->race->race_date->toDateString(),
        'required_license_code' => $registration->race->required_license_code,
        'check_id' => $registration->licenseCheck?->id,
        'check_updated_at' => $registration->licenseCheck?->updated_at?->format('Y-m-d H:i:s.u'),
        'check_revision' => $registration->licenseCheck?->revision,
    ];
}

function validLicenseObservation(array $changes = []): array
{
    return array_replace([
        'result' => 'valid',
        'source' => 'ligue_ffsa',
        'source_access_status' => 'consulted',
        'license_type_observed' => 'RCCR',
        'license_state_observed' => 'active',
        'license_year_observed' => 2026,
        'identity_matches' => true,
        'license_number_matches' => true,
        'valid_for_race_date' => true,
        'valid_until' => '2026-12-31',
        'reason_code' => null,
        'note' => null,
    ], $changes);
}

function licenseChecker(string $role = 'STAFF_ADMINISTRATIF'): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

test('a configured race starts unverified and a direct FFSA observation records a current check', function (): void {
    $registration = licenseCheckRegistration();
    $staff = licenseChecker();
    $this->actingAs($staff);

    expect($registration->licenseCheckState())->toBe('not_checked');

    $check = app(RecordLicenseCheck::class)->execute(
        $registration->id, validLicenseObservation(), licenseCheckExpected($registration)
    );

    expect($check->result)->toBe('valid')
        ->and($check->checked_by)->toBe($staff->id)
        ->and($check->revision)->toBe(1)
        ->and($registration->fresh(['pilot', 'race', 'licenseCheck'])->licenseCheckState())->toBe('valid')
        ->and($registration->fresh()->status)->toBe('ACCEPTED')
        ->and($registration->passages()->count())->toBe(0);
    $this->assertDatabaseHas('activity_log', [
        'subject_type' => RaceRegistration::class,
        'subject_id' => $registration->id,
        'causer_id' => $staff->id,
        'description' => 'registration.license_checked',
    ]);
});

test('a race without a configured licence is not applicable and cannot be recorded', function (): void {
    $registration = licenseCheckRegistration(false);
    $this->actingAs(licenseChecker());

    expect($registration->licenseCheckState())->toBe('not_applicable');
    expect(fn () => app(RecordLicenseCheck::class)->execute(
        $registration->id, validLicenseObservation(), licenseCheckExpected($registration)
    ))->toThrow(ValidationException::class);
    $this->assertDatabaseCount('race_registration_license_checks', 0);
});

test('verification portal alone and a presented document never create a green badge', function (array $changes): void {
    $registration = licenseCheckRegistration();
    $this->actingAs(licenseChecker());

    expect(fn () => app(RecordLicenseCheck::class)->execute(
        $registration->id, validLicenseObservation($changes), licenseCheckExpected($registration)
    ))->toThrow(ValidationException::class);
    $this->assertDatabaseCount('race_registration_license_checks', 0);
})->with([
    [['source' => 'verification_ffsa']],
    [['source' => 'licence_ffsa']],
    [['source' => 'official_ffsa_document']],
    [['source_access_status' => 'unavailable']],
    [['license_number_matches' => false]],
    [['identity_matches' => false]],
    [['license_type_observed' => 'NCC']],
    [['license_year_observed' => 2025]],
    [['license_state_observed' => 'expired']],
    [['valid_until' => null]],
    [['valid_until' => '2026-11-07']],
]);

test('another licence type and an unavailable portal remain to review', function (array $observation): void {
    $registration = licenseCheckRegistration();
    $this->actingAs(licenseChecker());

    $check = app(RecordLicenseCheck::class)->execute(
        $registration->id, validLicenseObservation($observation), licenseCheckExpected($registration)
    );

    expect($check->result)->toBe('needs_review')
        ->and($registration->fresh(['pilot', 'race', 'licenseCheck'])->licenseCheckState())->toBe('needs_review');
})->with([
    [['result' => 'needs_review', 'license_type_observed' => 'NCC', 'reason_code' => 'other_title_to_review']],
    [['result' => 'needs_review', 'source_access_status' => 'unavailable', 'reason_code' => 'source_unavailable']],
    [['result' => 'needs_review', 'source' => 'official_ffsa_document', 'reason_code' => 'document_only']],
]);

test('only a confirmed invalid state may be labelled invalid', function (): void {
    $registration = licenseCheckRegistration();
    $this->actingAs(licenseChecker());

    $check = app(RecordLicenseCheck::class)->execute(
        $registration->id,
        validLicenseObservation([
            'result' => 'invalid', 'reason_code' => 'revoked',
            'license_state_observed' => 'suspended', 'valid_for_race_date' => false,
        ]),
        licenseCheckExpected($registration)
    );
    expect($check->result)->toBe('invalid');

    expect(fn () => app(RecordLicenseCheck::class)->execute(
        $registration->id,
        validLicenseObservation([
            'result' => 'invalid', 'source_access_status' => 'unavailable',
            'reason_code' => 'revoked', 'license_state_observed' => 'suspended',
            'valid_for_race_date' => false,
        ]),
        licenseCheckExpected($registration->fresh(['pilot', 'race', 'licenseCheck']))
    ))->toThrow(ValidationException::class);
    expect($check->fresh()->revision)->toBe(1);
});

test('contradictory evidence never creates a red licence badge', function (array $changes): void {
    $registration = licenseCheckRegistration();
    $this->actingAs(licenseChecker());

    expect(fn () => app(RecordLicenseCheck::class)->execute(
        $registration->id,
        validLicenseObservation(array_replace([
            'result' => 'invalid', 'reason_code' => 'revoked',
            'license_state_observed' => 'suspended', 'valid_for_race_date' => false,
        ], $changes)),
        licenseCheckExpected($registration)
    ))->toThrow(ValidationException::class);
    $this->assertDatabaseCount('race_registration_license_checks', 0);
})->with([
    [['license_state_observed' => 'active']],
    [['valid_for_race_date' => true]],
    [['source' => 'verification_ffsa']],
    [['license_type_observed' => 'NCC']],
    [['license_year_observed' => 2025]],
    [['identity_matches' => false]],
    [['license_number_matches' => false]],
    [['reason_code' => 'expired', 'license_state_observed' => 'expired', 'valid_until' => '2026-12-31']],
    [['reason_code' => 'expired', 'license_state_observed' => 'active', 'valid_until' => '2026-11-07']],
    [['reason_code' => 'no_current_license_confirmed', 'license_state_observed' => 'active']],
]);

test('an observed expired or inactive licence can be recorded as invalid', function (array $changes): void {
    $registration = licenseCheckRegistration();
    $this->actingAs(licenseChecker());

    $check = app(RecordLicenseCheck::class)->execute(
        $registration->id,
        validLicenseObservation(array_replace([
            'result' => 'invalid', 'valid_for_race_date' => false,
        ], $changes)),
        licenseCheckExpected($registration)
    );

    expect($check->result)->toBe('invalid');
})->with([
    [['reason_code' => 'expired', 'license_state_observed' => 'expired', 'valid_until' => '2026-11-07']],
    [['reason_code' => 'no_current_license_confirmed', 'license_state_observed' => 'inactive', 'valid_until' => null]],
]);

test('changed pilot or race information makes the old result stale', function (string $field): void {
    $registration = licenseCheckRegistration();
    $this->actingAs(licenseChecker());
    app(RecordLicenseCheck::class)->execute(
        $registration->id, validLicenseObservation(), licenseCheckExpected($registration)
    );

    if ($field === 'race_date') {
        $registration->race->update(['race_date' => '2026-11-09']);
    } elseif ($field === 'required_license_code') {
        $registration->race->update(['required_license_code' => 'NCC']);
    } else {
        $registration->pilot->update([$field => $field === 'license_number' ? '654321' : 'Modifie']);
    }

    expect($registration->fresh(['pilot', 'race', 'licenseCheck'])->licenseCheckState())->toBe('stale');
    expect(RaceRegistration::query()->whereLicenseCheckState('stale')->pluck('race_registrations.id')->all())
        ->toContain($registration->id);
})->with(['license_number', 'first_name', 'last_name', 'race_date', 'required_license_code']);

test('a previous decision cannot be silently overwritten after a concurrent revision', function (): void {
    $registration = licenseCheckRegistration();
    $this->actingAs(licenseChecker());
    $initial = licenseCheckExpected($registration);
    $check = app(RecordLicenseCheck::class)->execute($registration->id, validLicenseObservation(), $initial);
    $snapshot = licenseCheckExpected($registration->fresh(['pilot', 'race', 'licenseCheck']));
    $changed = app(RecordLicenseCheck::class)->execute(
        $registration->id,
        validLicenseObservation(['result' => 'needs_review', 'reason_code' => 'ambiguous']),
        $snapshot
    );

    expect($changed->revision)->toBe(2);
    expect(fn () => app(RecordLicenseCheck::class)->execute(
        $registration->id, validLicenseObservation(), $snapshot
    ))->toThrow(ValidationException::class);
    expect($check->fresh()->result)->toBe('needs_review');
});

test('a staff role without the dedicated permission cannot write a licence check', function (): void {
    $registration = licenseCheckRegistration();
    $this->actingAs(licenseChecker('CONTROLEUR_TECHNIQUE'));

    expect(fn () => app(RecordLicenseCheck::class)->execute(
        $registration->id, validLicenseObservation(), licenseCheckExpected($registration)
    ))->toThrow(AuthorizationException::class);
    $this->assertDatabaseCount('race_registration_license_checks', 0);
});

test('the query scope filters effective states before pagination', function (): void {
    $this->actingAs(licenseChecker());
    $unconfigured = licenseCheckRegistration(false);
    $unchecked = licenseCheckRegistration();
    $verified = licenseCheckRegistration();
    app(RecordLicenseCheck::class)->execute($verified->id, validLicenseObservation(), licenseCheckExpected($verified));

    expect(RaceRegistration::query()->whereLicenseCheckState('not_applicable')->pluck('race_registrations.id')->all())
        ->toContain($unconfigured->id)
        ->and(RaceRegistration::query()->whereLicenseCheckState('not_checked')->pluck('race_registrations.id')->all())
        ->toContain($unchecked->id)
        ->and(RaceRegistration::query()->whereLicenseCheckState('valid')->pluck('race_registrations.id')->all())
        ->toContain($verified->id);
});
