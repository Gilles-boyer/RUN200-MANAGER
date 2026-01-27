<?php

declare(strict_types=1);

namespace Tests\Feature\Sprint4;

use App\Domain\Registration\Rules\CheckpointTransitions;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ============================================================================
// Checkpoint Transitions Rules Tests
// ============================================================================

test('admin check can be scanned when registration is accepted', function () {
    $canScan = CheckpointTransitions::canScan('ADMIN_CHECK', 'ACCEPTED', []);

    expect($canScan)->toBeTrue();
});

test('admin check cannot be scanned when registration is pending', function () {
    $canScan = CheckpointTransitions::canScan('ADMIN_CHECK', 'PENDING_VALIDATION', []);

    expect($canScan)->toBeFalse();
});

test('tech check requires admin check to be done first', function () {
    // Without admin check
    $canScanWithout = CheckpointTransitions::canScan('TECH_CHECK', 'ADMIN_CHECKED', []);
    expect($canScanWithout)->toBeFalse();

    // With admin check
    $canScanWith = CheckpointTransitions::canScan('TECH_CHECK', 'ADMIN_CHECKED', ['ADMIN_CHECK']);
    expect($canScanWith)->toBeTrue();
});

test('entry requires both admin and tech checks', function () {
    // Missing both
    $canScan1 = CheckpointTransitions::canScan('ENTRY', 'TECH_CHECKED_OK', []);
    expect($canScan1)->toBeFalse();

    // Only admin
    $canScan2 = CheckpointTransitions::canScan('ENTRY', 'TECH_CHECKED_OK', ['ADMIN_CHECK']);
    expect($canScan2)->toBeFalse();

    // Both checks
    $canScan3 = CheckpointTransitions::canScan('ENTRY', 'TECH_CHECKED_OK', ['ADMIN_CHECK', 'TECH_CHECK']);
    expect($canScan3)->toBeTrue();
});

test('bracelet requires all previous checkpoints', function () {
    // Missing entry
    $canScan1 = CheckpointTransitions::canScan('BRACELET', 'ENTRY_SCANNED', ['ADMIN_CHECK', 'TECH_CHECK']);
    expect($canScan1)->toBeFalse();

    // All checks
    $canScan2 = CheckpointTransitions::canScan('BRACELET', 'ENTRY_SCANNED', ['ADMIN_CHECK', 'TECH_CHECK', 'ENTRY']);
    expect($canScan2)->toBeTrue();
});

test('cannot scan same checkpoint twice', function () {
    $canScan = CheckpointTransitions::canScan('ADMIN_CHECK', 'ADMIN_CHECKED', ['ADMIN_CHECK']);

    expect($canScan)->toBeFalse();
});

test('get status after scan returns correct status', function () {
    expect(CheckpointTransitions::getStatusAfterScan('ADMIN_CHECK'))->toBe('ADMIN_CHECKED');
    expect(CheckpointTransitions::getStatusAfterScan('TECH_CHECK'))->toBe('TECH_CHECKED_OK');
    expect(CheckpointTransitions::getStatusAfterScan('ENTRY'))->toBe('ENTRY_SCANNED');
    expect(CheckpointTransitions::getStatusAfterScan('BRACELET'))->toBe('BRACELET_GIVEN');
});

test('unknown checkpoint returns null for status', function () {
    expect(CheckpointTransitions::getStatusAfterScan('UNKNOWN'))->toBeNull();
});

test('error message for already scanned checkpoint', function () {
    $message = CheckpointTransitions::getErrorMessage('ADMIN_CHECK', 'ADMIN_CHECKED', ['ADMIN_CHECK']);

    expect($message)->toContain('déjà été scanné');
});

test('error message for incompatible status', function () {
    $message = CheckpointTransitions::getErrorMessage('ADMIN_CHECK', 'PENDING_VALIDATION', []);

    expect($message)->toContain('Statut d\'inscription incompatible');
});

test('error message for missing prerequisites', function () {
    $message = CheckpointTransitions::getErrorMessage('TECH_CHECK', 'ADMIN_CHECKED', []);

    expect($message)->toContain('préalables manquants');
    expect($message)->toContain('ADMIN_CHECK');
});
