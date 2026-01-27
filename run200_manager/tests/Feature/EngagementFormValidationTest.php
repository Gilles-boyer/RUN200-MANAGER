<?php

use App\Application\Registrations\UseCases\RecordTechInspection;
use App\Application\Registrations\UseCases\ScanCheckpoint;
use App\Application\Registrations\UseCases\UpdateEngagementFormValidation;
use App\Infrastructure\Qr\QrTokenService;
use App\Models\Checkpoint;
use App\Models\EngagementForm;
use App\Models\RaceRegistration;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Créer les permissions nécessaires
    $permissions = [
        'checkpoint.scan.admin_check',
        'checkpoint.scan.tech_check',
        'checkpoint.scan.entry',
        'tech_inspection.manage',
    ];

    foreach ($permissions as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    }

    // Créer les rôles
    $adminRole = Role::firstOrCreate(['name' => 'ADMIN', 'guard_name' => 'web']);
    $techRole = Role::firstOrCreate(['name' => 'CONTROLEUR_TECHNIQUE', 'guard_name' => 'web']);
    $staffRole = Role::firstOrCreate(['name' => 'STAFF_ADMINISTRATIF', 'guard_name' => 'web']);

    $adminRole->givePermissionTo(Permission::all());
    $techRole->givePermissionTo(['tech_inspection.manage', 'checkpoint.scan.tech_check']);
    $staffRole->givePermissionTo(['checkpoint.scan.admin_check']);

    // Créer les checkpoints
    Checkpoint::firstOrCreate(['code' => 'ADMIN_CHECK'], [
        'name' => 'Contrôle Administratif',
        'is_active' => true,
    ]);

    Checkpoint::firstOrCreate(['code' => 'TECH_CHECK'], [
        'name' => 'Contrôle Technique',
        'is_active' => true,
    ]);
});

describe('EngagementForm Validation', function () {
    test('tech inspection updates engagement form', function () {
        // Créer une course et une inscription avec fiche d'engagement
        $registration = RaceRegistration::factory()->create(['status' => 'ACCEPTED']);

        $engagement = EngagementForm::factory()->create([
            'race_registration_id' => $registration->id,
        ]);

        $techController = User::factory()->create();
        $techController->assignRole('CONTROLEUR_TECHNIQUE');

        // Effectuer le contrôle technique
        $useCase = new RecordTechInspection;
        $useCase->execute($registration, 'OK', 'Véhicule conforme', $techController);

        // Vérifier que la fiche d'engagement est mise à jour
        $engagement->refresh();

        expect($engagement->tech_controller_name)->toBe($techController->name)
            ->and($engagement->tech_checked_at)->not->toBeNull()
            ->and($engagement->tech_notes)->toBe('Véhicule conforme');
    });

    test('admin checkpoint scan updates engagement form', function () {
        // Créer une inscription avec fiche d'engagement
        $registration = RaceRegistration::factory()->create(['status' => 'ACCEPTED']);

        $engagement = EngagementForm::factory()->create([
            'race_registration_id' => $registration->id,
        ]);

        $adminStaff = User::factory()->create();
        $adminStaff->assignRole('STAFF_ADMINISTRATIF');

        // Générer un token QR
        $qrService = new QrTokenService;
        $token = $qrService->generate($registration);

        // Scanner le checkpoint administratif
        $scanUseCase = new ScanCheckpoint($qrService);
        $scanUseCase->execute($token, 'ADMIN_CHECK', $adminStaff);

        // Vérifier que la fiche d'engagement est mise à jour
        $engagement->refresh();

        expect($engagement->admin_validated_by)->toBe($adminStaff->id)
            ->and($engagement->admin_validated_at)->not->toBeNull()
            ->and($engagement->admin_notes)->toBe('Validé');
    });

    test('full validation workflow updates engagement form correctly', function () {
        // Créer une inscription avec fiche d'engagement
        $registration = RaceRegistration::factory()->create(['status' => 'ACCEPTED']);

        $engagement = EngagementForm::factory()->create([
            'race_registration_id' => $registration->id,
        ]);

        $adminStaff = User::factory()->create();
        $adminStaff->assignRole('STAFF_ADMINISTRATIF');

        $techController = User::factory()->create();
        $techController->assignRole('CONTROLEUR_TECHNIQUE');

        // Générer un token QR
        $qrService = new QrTokenService;
        $token = $qrService->generate($registration);

        // 1. Scanner le checkpoint administratif
        $scanUseCase = new ScanCheckpoint($qrService);
        $scanUseCase->execute($token, 'ADMIN_CHECK', $adminStaff);

        // 2. Effectuer le contrôle technique
        $registration->refresh();
        $techUseCase = new RecordTechInspection;
        $techUseCase->execute($registration, 'OK', 'RAS', $techController);

        // Vérifier que la fiche d'engagement est complètement validée
        $engagement->refresh();

        $validationService = new UpdateEngagementFormValidation;
        $status = $validationService->getValidationStatus($engagement);

        expect($status['is_signed'])->toBeTrue()
            ->and($status['is_tech_validated'])->toBeTrue()
            ->and($status['is_admin_validated'])->toBeTrue()
            ->and($status['is_fully_validated'])->toBeTrue()
            ->and($engagement->tech_controller_name)->toBe($techController->name)
            ->and($engagement->adminValidator->id)->toBe($adminStaff->id);
    });

    test('engagement form without registration does not crash on validation', function () {
        $registration = RaceRegistration::factory()->create(['status' => 'ACCEPTED']);
        // Pas de fiche d'engagement créée

        $techController = User::factory()->create();
        $techController->assignRole('CONTROLEUR_TECHNIQUE');

        // Le contrôle technique ne doit pas échouer même sans fiche d'engagement
        $useCase = new RecordTechInspection;
        $inspection = $useCase->execute($registration, 'OK', 'Test', $techController);

        expect($inspection)->not->toBeNull()
            ->and($registration->fresh()->status)->toBe('TECH_CHECKED_OK');
    });

    test('validation service returns correct status', function () {
        $registration = RaceRegistration::factory()->create(['status' => 'ACCEPTED']);

        $engagement = EngagementForm::factory()->create([
            'race_registration_id' => $registration->id,
            'tech_controller_name' => null,
            'tech_checked_at' => null,
            'admin_validated_by' => null,
            'admin_validated_at' => null,
        ]);

        $validationService = new UpdateEngagementFormValidation;

        // Status initial
        $status = $validationService->getValidationStatus($engagement);
        expect($status['is_tech_validated'])->toBeFalse()
            ->and($status['is_admin_validated'])->toBeFalse()
            ->and($status['is_fully_validated'])->toBeFalse();

        // Après validation tech
        $techController = User::factory()->create();
        $validationService->recordTechValidation($registration, $techController, 'OK', 'Test');

        $engagement->refresh();
        $status = $validationService->getValidationStatus($engagement);
        expect($status['is_tech_validated'])->toBeTrue()
            ->and($status['is_admin_validated'])->toBeFalse()
            ->and($status['is_fully_validated'])->toBeFalse();

        // Après validation admin
        $adminStaff = User::factory()->create();
        $validationService->recordAdminValidation($registration, $adminStaff);

        $engagement->refresh();
        $status = $validationService->getValidationStatus($engagement);
        expect($status['is_tech_validated'])->toBeTrue()
            ->and($status['is_admin_validated'])->toBeTrue()
            ->and($status['is_fully_validated'])->toBeTrue();
    });
});
