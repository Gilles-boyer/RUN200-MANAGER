<?php

declare(strict_types=1);

namespace Tests\Feature\Sprint3;

use App\Application\Registrations\UseCases\ValidateRegistration;
use App\Models\RaceRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $roles = ['ADMIN', 'PILOTE', 'STAFF_ADMINISTRATIF', 'CONTROLEUR_TECHNIQUE', 'STAFF_ENTREE', 'STAFF_SONO'];
    foreach ($roles as $role) {
        Role::findOrCreate($role, 'web');
    }

    $this->staffUser = User::factory()->create();
    $this->staffUser->assignRole('STAFF_ADMINISTRATIF');
});

// ============================================================================
// ValidateRegistration Use Case Tests
// ============================================================================

test('staff can accept a pending registration', function () {
    $registration = RaceRegistration::factory()->pending()->create();

    $useCase = new ValidateRegistration;
    $result = $useCase->execute($registration, 'ACCEPTED', null, $this->staffUser);

    expect($result->status)->toBe('ACCEPTED')
        ->and($result->validated_at)->not->toBeNull()
        ->and($result->validated_by)->toBe($this->staffUser->id)
        ->and($result->reason)->toBeNull();
});

test('staff can refuse a pending registration with reason', function () {
    $registration = RaceRegistration::factory()->pending()->create();
    $reason = 'Documents manquants';

    $useCase = new ValidateRegistration;
    $result = $useCase->execute($registration, 'REFUSED', $reason, $this->staffUser);

    expect($result->status)->toBe('REFUSED')
        ->and($result->validated_at)->not->toBeNull()
        ->and($result->validated_by)->toBe($this->staffUser->id)
        ->and($result->reason)->toBe($reason);
});

test('refusing a registration without reason throws exception', function () {
    $registration = RaceRegistration::factory()->pending()->create();

    $useCase = new ValidateRegistration;

    expect(fn () => $useCase->execute($registration, 'REFUSED', null, $this->staffUser))
        ->toThrow(\InvalidArgumentException::class, 'Une raison est obligatoire pour un refus');
});

test('refusing a registration with empty reason throws exception', function () {
    $registration = RaceRegistration::factory()->pending()->create();

    $useCase = new ValidateRegistration;

    expect(fn () => $useCase->execute($registration, 'REFUSED', '', $this->staffUser))
        ->toThrow(\InvalidArgumentException::class, 'Une raison est obligatoire pour un refus');
});

test('cannot validate already accepted registration', function () {
    $registration = RaceRegistration::factory()->accepted()->create();

    $useCase = new ValidateRegistration;

    expect(fn () => $useCase->execute($registration, 'ACCEPTED', null, $this->staffUser))
        ->toThrow(\InvalidArgumentException::class, 'Seules les inscriptions en attente peuvent être validées');
});

test('cannot validate already refused registration', function () {
    $registration = RaceRegistration::factory()->refused()->create();

    $useCase = new ValidateRegistration;

    expect(fn () => $useCase->execute($registration, 'REFUSED', 'New reason', $this->staffUser))
        ->toThrow(\InvalidArgumentException::class, 'Seules les inscriptions en attente peuvent être validées');
});

test('validation records validated_at timestamp', function () {
    $registration = RaceRegistration::factory()->pending()->create();

    $useCase = new ValidateRegistration;
    $result = $useCase->execute($registration, 'ACCEPTED', null, $this->staffUser);

    expect($result->validated_at)->not->toBeNull()
        ->and($result->validated_at->diffInSeconds(now()))->toBeLessThan(5);
});

test('only ACCEPTED and REFUSED are valid statuses', function () {
    $registration = RaceRegistration::factory()->pending()->create();

    $useCase = new ValidateRegistration;

    expect(fn () => $useCase->execute($registration, 'INVALID_STATUS', null, $this->staffUser))
        ->toThrow(\InvalidArgumentException::class);
});
