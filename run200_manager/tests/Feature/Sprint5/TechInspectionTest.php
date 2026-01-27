<?php

declare(strict_types=1);

namespace Tests\Feature\Sprint5;

use App\Application\Registrations\UseCases\RecordTechInspection;
use App\Models\RaceRegistration;
use App\Models\TechInspection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create roles
    $roles = ['ADMIN', 'PILOTE', 'STAFF_ADMINISTRATIF', 'CONTROLEUR_TECHNIQUE', 'STAFF_ENTREE', 'STAFF_SONO'];
    foreach ($roles as $role) {
        Role::findOrCreate($role, 'web');
    }

    // Create permissions
    $permissions = [
        'tech_inspection.manage',
        'tech_inspection.view',
    ];
    foreach ($permissions as $perm) {
        Permission::findOrCreate($perm, 'web');
    }

    // Assign permissions to roles
    Role::findByName('CONTROLEUR_TECHNIQUE')->givePermissionTo(['tech_inspection.manage', 'tech_inspection.view']);
    Role::findByName('ADMIN')->givePermissionTo($permissions);

    $this->techController = User::factory()->create();
    $this->techController->assignRole('CONTROLEUR_TECHNIQUE');
});

// ============================================================================
// RecordTechInspection Use Case Tests
// ============================================================================

test('tech controller can validate inspection (OK)', function () {
    $registration = RaceRegistration::factory()->accepted()->create();

    $useCase = new RecordTechInspection;
    $inspection = $useCase->execute($registration, 'OK', null, $this->techController);

    expect($inspection)->toBeInstanceOf(TechInspection::class)
        ->and($inspection->status)->toBe('OK')
        ->and($inspection->notes)->toBeNull()
        ->and($inspection->inspected_by)->toBe($this->techController->id);

    $registration->refresh();
    expect($registration->status)->toBe('TECH_CHECKED_OK');
});

test('tech controller can fail inspection with notes', function () {
    $registration = RaceRegistration::factory()->accepted()->create();
    $notes = 'Frein arrière défectueux';

    $useCase = new RecordTechInspection;
    $inspection = $useCase->execute($registration, 'FAIL', $notes, $this->techController);

    expect($inspection)->toBeInstanceOf(TechInspection::class)
        ->and($inspection->status)->toBe('FAIL')
        ->and($inspection->notes)->toBe($notes)
        ->and($inspection->inspected_by)->toBe($this->techController->id);

    $registration->refresh();
    expect($registration->status)->toBe('TECH_CHECKED_FAIL');
});

test('failing inspection without notes throws exception', function () {
    $registration = RaceRegistration::factory()->accepted()->create();

    $useCase = new RecordTechInspection;

    expect(fn () => $useCase->execute($registration, 'FAIL', null, $this->techController))
        ->toThrow(\InvalidArgumentException::class, 'Les notes sont obligatoires');
});

test('failing inspection with empty notes throws exception', function () {
    $registration = RaceRegistration::factory()->accepted()->create();

    $useCase = new RecordTechInspection;

    expect(fn () => $useCase->execute($registration, 'FAIL', '  ', $this->techController))
        ->toThrow(\InvalidArgumentException::class, 'Les notes sont obligatoires');
});

test('cannot inspect pending registration', function () {
    $registration = RaceRegistration::factory()->pending()->create();

    $useCase = new RecordTechInspection;

    expect(fn () => $useCase->execute($registration, 'OK', null, $this->techController))
        ->toThrow(\InvalidArgumentException::class);
});

test('cannot inspect refused registration', function () {
    $registration = RaceRegistration::factory()->refused()->create();

    $useCase = new RecordTechInspection;

    expect(fn () => $useCase->execute($registration, 'OK', null, $this->techController))
        ->toThrow(\InvalidArgumentException::class);
});

test('can inspect admin_checked registration', function () {
    $registration = RaceRegistration::factory()->create(['status' => 'ADMIN_CHECKED']);

    $useCase = new RecordTechInspection;
    $inspection = $useCase->execute($registration, 'OK', null, $this->techController);

    expect($inspection->status)->toBe('OK');
    $registration->refresh();
    expect($registration->status)->toBe('TECH_CHECKED_OK');
});

test('cannot inspect registration twice', function () {
    $registration = RaceRegistration::factory()->accepted()->create();

    $useCase = new RecordTechInspection;
    $useCase->execute($registration, 'OK', null, $this->techController);

    // Refresh pour avoir le nouveau statut
    $registration->refresh();

    // Vérifier que l'inspection existe
    expect($registration->techInspection)->not->toBeNull();

    // Remettre manuellement le statut à ACCEPTED pour tester la vérification d'inspection existante
    $registration->update(['status' => 'ACCEPTED']);

    expect(fn () => $useCase->execute($registration, 'FAIL', 'Autre problème', $this->techController))
        ->toThrow(\InvalidArgumentException::class, 'déjà été enregistré');
});

test('invalid status throws exception', function () {
    $registration = RaceRegistration::factory()->accepted()->create();

    $useCase = new RecordTechInspection;

    expect(fn () => $useCase->execute($registration, 'INVALID', null, $this->techController))
        ->toThrow(\InvalidArgumentException::class, 'Le statut doit être OK ou FAIL');
});

// ============================================================================
// Pass & Fail Helper Methods
// ============================================================================

test('pass helper method works', function () {
    $registration = RaceRegistration::factory()->accepted()->create();

    $useCase = new RecordTechInspection;
    $inspection = $useCase->pass($registration, $this->techController);

    expect($inspection->status)->toBe('OK');
});

test('fail helper method works', function () {
    $registration = RaceRegistration::factory()->accepted()->create();

    $useCase = new RecordTechInspection;
    $inspection = $useCase->fail($registration, $this->techController, 'Pneus usés');

    expect($inspection->status)->toBe('FAIL')
        ->and($inspection->notes)->toBe('Pneus usés');
});

// ============================================================================
// Reset Tests
// ============================================================================

test('can reset failed inspection', function () {
    $registration = RaceRegistration::factory()->accepted()->create();

    $useCase = new RecordTechInspection;
    $useCase->fail($registration, $this->techController, 'Problème initial');

    $registration->refresh();
    expect($registration->status)->toBe('TECH_CHECKED_FAIL');

    $useCase->reset($registration, $this->techController);

    $registration->refresh();
    expect($registration->status)->toBe('ACCEPTED')
        ->and($registration->techInspection)->toBeNull();
});

test('cannot reset passed inspection', function () {
    $registration = RaceRegistration::factory()->accepted()->create();

    $useCase = new RecordTechInspection;
    $useCase->pass($registration, $this->techController);

    $registration->refresh();

    expect(fn () => $useCase->reset($registration, $this->techController))
        ->toThrow(\InvalidArgumentException::class);
});

test('reset restores admin_checked status if admin checkpoint passed', function () {
    $registration = RaceRegistration::factory()->create(['status' => 'ADMIN_CHECKED']);

    // Simuler un passage checkpoint admin
    $registration->passages()->create([
        'checkpoint_id' => \App\Models\Checkpoint::factory()->adminCheck()->create()->id,
        'scanned_by' => $this->techController->id,
        'scanned_at' => now(),
    ]);

    $useCase = new RecordTechInspection;
    $useCase->fail($registration, $this->techController, 'Problème');

    $registration->refresh();
    $useCase->reset($registration, $this->techController);

    $registration->refresh();
    expect($registration->status)->toBe('ADMIN_CHECKED');
});

// ============================================================================
// Model Tests
// ============================================================================

test('tech inspection model has correct relationships', function () {
    $registration = RaceRegistration::factory()->accepted()->create();
    $inspector = User::factory()->create();

    $inspection = TechInspection::create([
        'race_registration_id' => $registration->id,
        'status' => 'OK',
        'inspected_by' => $inspector->id,
        'inspected_at' => now(),
    ]);

    expect($inspection->registration->id)->toBe($registration->id)
        ->and($inspection->inspector->id)->toBe($inspector->id);
});

test('tech inspection status helpers work correctly', function () {
    $okInspection = TechInspection::factory()->passed()->create();
    expect($okInspection->isOk())->toBeTrue()
        ->and($okInspection->isFail())->toBeFalse();

    $failedInspection = TechInspection::factory()->failed()->create();
    expect($failedInspection->isFail())->toBeTrue()
        ->and($failedInspection->isOk())->toBeFalse();
});

test('tech inspection scopes work', function () {
    TechInspection::factory()->passed()->count(3)->create();
    TechInspection::factory()->failed()->count(2)->create();

    expect(TechInspection::ok()->count())->toBe(3)
        ->and(TechInspection::failed()->count())->toBe(2);
});

test('race registration has tech inspection relationship', function () {
    $registration = RaceRegistration::factory()->accepted()->create();

    $inspection = TechInspection::factory()
        ->forRegistration($registration)
        ->passed()
        ->create();

    expect($registration->techInspection->id)->toBe($inspection->id);
});
