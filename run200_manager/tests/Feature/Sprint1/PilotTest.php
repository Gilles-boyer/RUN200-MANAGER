<?php

use App\Models\Pilot;
use App\Models\User;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolesAndPermissionsSeeder']);
});

test('un utilisateur peut avoir un pilote associé', function () {
    $user = User::factory()->create();
    $user->assignRole('PILOTE');
    $pilot = Pilot::factory()->create(['user_id' => $user->id]);

    expect($user->fresh()->pilot)->not->toBeNull()
        ->and($user->pilot->id)->toBe($pilot->id)
        ->and($user->isPilot())->toBeTrue();
});

test('un pilote a un numéro de licence unique entre 1 et 6 chiffres', function () {
    $pilot1 = Pilot::factory()->create(['license_number' => '123']);

    expect($pilot1->license_number)->toBe('123');

    // Test unicité
    expect(fn () => Pilot::factory()->create(['license_number' => '123']))
        ->toThrow(Exception::class);
});

test('un pilote peut être mineur avec tuteur', function () {
    $pilot = Pilot::factory()->minor()->withGuardian()->create();

    expect($pilot->is_minor)->toBeTrue()
        ->and($pilot->guardian_name)->not->toBeNull()
        ->and($pilot->guardian_phone)->not->toBeNull();
});

test('un pilote majeur ne nécessite pas de tuteur', function () {
    $pilot = Pilot::factory()->create([
        'is_minor' => false,
        'guardian_name' => null,
        'guardian_phone' => null,
    ]);

    expect($pilot->is_minor)->toBeFalse()
        ->and($pilot->guardian_name)->toBeNull()
        ->and($pilot->guardian_phone)->toBeNull();
});

test('un pilote peut avoir plusieurs voitures', function () {
    $pilot = Pilot::factory()->hasCars(3)->create();

    expect($pilot->cars)->toHaveCount(3)
        ->and($pilot->cars->first()->pilot_id)->toBe($pilot->id);
});

test('scope whereActiveSeason fonctionne correctement', function () {
    Pilot::factory()->create(['is_active_season' => true]);
    Pilot::factory()->create(['is_active_season' => false]);

    $activePilots = Pilot::whereActiveSeason()->get();

    expect($activePilots)->toHaveCount(1);
});
