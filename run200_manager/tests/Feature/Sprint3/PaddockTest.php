<?php

declare(strict_types=1);

namespace Tests\Feature\Sprint3;

use App\Application\Registrations\UseCases\AssignPaddock;
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
// AssignPaddock Use Case Tests
// ============================================================================

test('staff can assign paddock to accepted registration', function () {
    $registration = RaceRegistration::factory()->accepted()->create();

    $useCase = new AssignPaddock;
    $result = $useCase->execute($registration, 'P1', $this->staffUser);

    expect($result->paddock)->toBe('P1');
});

test('cannot assign paddock to pending registration', function () {
    $registration = RaceRegistration::factory()->pending()->create();

    $useCase = new AssignPaddock;

    expect(fn () => $useCase->execute($registration, 'P1', $this->staffUser))
        ->toThrow(\InvalidArgumentException::class, 'Seules les inscriptions acceptées peuvent recevoir un paddock');
});

test('cannot assign paddock to refused registration', function () {
    $registration = RaceRegistration::factory()->refused()->create();

    $useCase = new AssignPaddock;

    expect(fn () => $useCase->execute($registration, 'P1', $this->staffUser))
        ->toThrow(\InvalidArgumentException::class, 'Seules les inscriptions acceptées peuvent recevoir un paddock');
});

test('paddock must be unique per race', function () {
    $registration1 = RaceRegistration::factory()->accepted()->create([
        'paddock' => 'P1',
    ]);

    $registration2 = RaceRegistration::factory()->accepted()->create([
        'race_id' => $registration1->race_id,
    ]);

    $useCase = new AssignPaddock;

    expect(fn () => $useCase->execute($registration2, 'P1', $this->staffUser))
        ->toThrow(\InvalidArgumentException::class, 'Ce paddock est déjà assigné à une autre inscription pour cette course');
});

test('same paddock can be used for different races', function () {
    $registration1 = RaceRegistration::factory()->accepted()->create([
        'paddock' => 'P1',
    ]);

    // Different race
    $registration2 = RaceRegistration::factory()->accepted()->create();

    $useCase = new AssignPaddock;
    $result = $useCase->execute($registration2, 'P1', $this->staffUser);

    expect($result->paddock)->toBe('P1');
});

test('can change paddock for same registration', function () {
    $registration = RaceRegistration::factory()->accepted()->create([
        'paddock' => 'P1',
    ]);

    $useCase = new AssignPaddock;
    $result = $useCase->execute($registration, 'P2', $this->staffUser);

    expect($result->paddock)->toBe('P2');
});

test('paddock cannot be empty', function () {
    $registration = RaceRegistration::factory()->accepted()->create();

    $useCase = new AssignPaddock;

    expect(fn () => $useCase->execute($registration, '', $this->staffUser))
        ->toThrow(\InvalidArgumentException::class, 'Le numéro de paddock est obligatoire');
});

test('paddock is trimmed before saving', function () {
    $registration = RaceRegistration::factory()->accepted()->create();

    $useCase = new AssignPaddock;
    $result = $useCase->execute($registration, '  P1  ', $this->staffUser);

    expect($result->paddock)->toBe('P1');
});
