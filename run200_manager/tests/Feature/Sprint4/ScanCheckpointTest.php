<?php

declare(strict_types=1);

namespace Tests\Feature\Sprint4;

use App\Application\Registrations\UseCases\ScanCheckpoint;
use App\Infrastructure\Qr\QrTokenService;
use App\Models\Checkpoint;
use App\Models\CheckpointPassage;
use App\Models\RaceRegistration;
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
        'checkpoint.scan.admin_check',
        'checkpoint.scan.tech_check',
        'checkpoint.scan.entry',
        'checkpoint.scan.bracelet',
    ];
    foreach ($permissions as $perm) {
        Permission::findOrCreate($perm, 'web');
    }

    // Assign permissions to roles
    Role::findByName('STAFF_ADMINISTRATIF')->givePermissionTo('checkpoint.scan.admin_check');
    Role::findByName('CONTROLEUR_TECHNIQUE')->givePermissionTo('checkpoint.scan.tech_check');
    Role::findByName('STAFF_ENTREE')->givePermissionTo(['checkpoint.scan.entry', 'checkpoint.scan.bracelet']);
    Role::findByName('ADMIN')->givePermissionTo($permissions);

    // Create checkpoints
    Checkpoint::factory()->adminCheck()->create();
    Checkpoint::factory()->techCheck()->create();
    Checkpoint::factory()->entry()->create();
    Checkpoint::factory()->bracelet()->create();
});

// ============================================================================
// ScanCheckpoint Use Case Tests
// ============================================================================

test('staff can scan admin checkpoint with valid token', function () {
    $registration = RaceRegistration::factory()->accepted()->create();
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');

    $qrService = new QrTokenService;
    $token = $qrService->generate($registration);

    $scanUseCase = new ScanCheckpoint($qrService);
    $passage = $scanUseCase->execute($token, 'ADMIN_CHECK', $staff);

    expect($passage)->toBeInstanceOf(CheckpointPassage::class)
        ->and($passage->checkpoint->code)->toBe('ADMIN_CHECK')
        ->and($passage->scanned_by)->toBe($staff->id);

    $registration->refresh();
    expect($registration->status)->toBe('ADMIN_CHECKED');
});

test('scan with invalid token throws exception', function () {
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');

    $qrService = new QrTokenService;
    $scanUseCase = new ScanCheckpoint($qrService);

    expect(fn () => $scanUseCase->execute('invalid_token', 'ADMIN_CHECK', $staff))
        ->toThrow(\InvalidArgumentException::class, 'Token QR invalide');
});

test('user without permission cannot scan checkpoint', function () {
    $registration = RaceRegistration::factory()->accepted()->create();
    $pilotUser = User::factory()->create();
    $pilotUser->assignRole('PILOTE');

    $qrService = new QrTokenService;
    $token = $qrService->generate($registration);

    $scanUseCase = new ScanCheckpoint($qrService);

    expect(fn () => $scanUseCase->execute($token, 'ADMIN_CHECK', $pilotUser))
        ->toThrow(\InvalidArgumentException::class, 'permission');
});

test('cannot scan tech checkpoint before admin checkpoint', function () {
    $registration = RaceRegistration::factory()->accepted()->create();
    $techController = User::factory()->create();
    $techController->assignRole('CONTROLEUR_TECHNIQUE');

    $qrService = new QrTokenService;
    $token = $qrService->generate($registration);

    $scanUseCase = new ScanCheckpoint($qrService);

    expect(fn () => $scanUseCase->execute($token, 'TECH_CHECK', $techController))
        ->toThrow(\InvalidArgumentException::class);
});

test('can scan tech checkpoint after admin checkpoint', function () {
    $registration = RaceRegistration::factory()->accepted()->create();

    $adminStaff = User::factory()->create();
    $adminStaff->assignRole('STAFF_ADMINISTRATIF');

    $techController = User::factory()->create();
    $techController->assignRole('CONTROLEUR_TECHNIQUE');

    $qrService = new QrTokenService;
    $token = $qrService->generate($registration);

    $scanUseCase = new ScanCheckpoint($qrService);

    // First scan admin
    $scanUseCase->execute($token, 'ADMIN_CHECK', $adminStaff);

    // Regenerate token (the old one was used)
    $token = $qrService->generate($registration);

    // Then scan tech
    $passage = $scanUseCase->execute($token, 'TECH_CHECK', $techController);

    expect($passage->checkpoint->code)->toBe('TECH_CHECK');

    $registration->refresh();
    expect($registration->status)->toBe('TECH_CHECKED_OK');
});

test('cannot scan same checkpoint twice', function () {
    $registration = RaceRegistration::factory()->accepted()->create();
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');

    $qrService = new QrTokenService;
    $token = $qrService->generate($registration);

    $scanUseCase = new ScanCheckpoint($qrService);
    $scanUseCase->execute($token, 'ADMIN_CHECK', $staff);

    // Regenerate token
    $token = $qrService->generate($registration);

    expect(fn () => $scanUseCase->execute($token, 'ADMIN_CHECK', $staff))
        ->toThrow(\InvalidArgumentException::class, 'déjà été scanné');
});

test('scan creates checkpoint passage record', function () {
    $registration = RaceRegistration::factory()->accepted()->create();
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');

    $qrService = new QrTokenService;
    $token = $qrService->generate($registration);

    $scanUseCase = new ScanCheckpoint($qrService);
    $scanUseCase->execute($token, 'ADMIN_CHECK', $staff);

    expect(CheckpointPassage::count())->toBe(1);

    $passage = CheckpointPassage::first();
    expect($passage->race_registration_id)->toBe($registration->id)
        ->and($passage->scanned_by)->toBe($staff->id)
        ->and($passage->scanned_at)->not->toBeNull();
});

test('complete workflow through all checkpoints', function () {
    $registration = RaceRegistration::factory()->accepted()->create();

    $adminStaff = User::factory()->create();
    $adminStaff->assignRole('ADMIN'); // Admin has all permissions

    $qrService = new QrTokenService;
    $scanUseCase = new ScanCheckpoint($qrService);

    // Step 1: Admin check
    $token = $qrService->generate($registration);
    $scanUseCase->execute($token, 'ADMIN_CHECK', $adminStaff);
    $registration->refresh();
    expect($registration->status)->toBe('ADMIN_CHECKED');

    // Step 2: Tech check
    $token = $qrService->generate($registration);
    $scanUseCase->execute($token, 'TECH_CHECK', $adminStaff);
    $registration->refresh();
    expect($registration->status)->toBe('TECH_CHECKED_OK');

    // Step 3: Entry
    $token = $qrService->generate($registration);
    $scanUseCase->execute($token, 'ENTRY', $adminStaff);
    $registration->refresh();
    expect($registration->status)->toBe('ENTRY_SCANNED');

    // Step 4: Bracelet
    $token = $qrService->generate($registration);
    $scanUseCase->execute($token, 'BRACELET', $adminStaff);
    $registration->refresh();
    expect($registration->status)->toBe('BRACELET_GIVEN');

    // All 4 passages created
    expect($registration->passages()->count())->toBe(4);
});

test('can get registration info from token', function () {
    $registration = RaceRegistration::factory()->accepted()->create();

    $qrService = new QrTokenService;
    $token = $qrService->generate($registration);

    $scanUseCase = new ScanCheckpoint($qrService);
    $info = $scanUseCase->getRegistrationFromToken($token);

    expect($info)->not->toBeNull()
        ->and($info['registration']->id)->toBe($registration->id)
        ->and($info['pilot'])->not->toBeNull()
        ->and($info['car'])->not->toBeNull()
        ->and($info['race'])->not->toBeNull()
        ->and($info['passed_checkpoints'])->toBeArray();
});

test('inactive checkpoint cannot be scanned', function () {
    $registration = RaceRegistration::factory()->accepted()->create();
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');

    // Deactivate admin checkpoint
    Checkpoint::where('code', 'ADMIN_CHECK')->update(['is_active' => false]);

    $qrService = new QrTokenService;
    $token = $qrService->generate($registration);

    $scanUseCase = new ScanCheckpoint($qrService);

    expect(fn () => $scanUseCase->execute($token, 'ADMIN_CHECK', $staff))
        ->toThrow(\InvalidArgumentException::class, 'désactivé');
});
