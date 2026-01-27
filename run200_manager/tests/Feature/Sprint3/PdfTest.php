<?php

declare(strict_types=1);

namespace Tests\Feature\Sprint3;

use App\Infrastructure\Pdf\EngagedListPdfService;
use App\Models\Race;
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
// PDF Generation Tests
// ============================================================================

test('can generate engaged list PDF', function () {
    $race = Race::factory()->create(['status' => 'OPEN']);

    // Create some accepted registrations
    RaceRegistration::factory()
        ->accepted()
        ->count(3)
        ->create(['race_id' => $race->id]);

    $pdfService = new EngagedListPdfService;
    $pdf = $pdfService->generate($race);

    expect($pdf)->toBeInstanceOf(\Barryvdh\DomPDF\PDF::class);
});

test('engaged list PDF contains only accepted registrations', function () {
    $race = Race::factory()->create(['status' => 'OPEN']);

    // Create registrations with different statuses
    $accepted = RaceRegistration::factory()
        ->accepted()
        ->create(['race_id' => $race->id]);

    $pending = RaceRegistration::factory()
        ->pending()
        ->create(['race_id' => $race->id]);

    $refused = RaceRegistration::factory()
        ->refused()
        ->create(['race_id' => $race->id]);

    $pdfService = new EngagedListPdfService;
    $pdf = $pdfService->generate($race);

    // The PDF should only contain 1 accepted registration
    $registrations = $race->registrations()->accepted()->get();
    expect($registrations)->toHaveCount(1)
        ->and($registrations->first()->id)->toBe($accepted->id);
});

test('PDF download returns response with correct headers', function () {
    $race = Race::factory()->create(['status' => 'OPEN']);

    RaceRegistration::factory()
        ->accepted()
        ->create(['race_id' => $race->id]);

    $pdfService = new EngagedListPdfService;
    $response = $pdfService->download($race);

    expect($response)->toBeInstanceOf(\Illuminate\Http\Response::class);

    $headers = $response->headers;
    expect($headers->get('Content-Type'))->toBe('application/pdf');
});

test('staff can access engaged PDF route', function () {
    $race = Race::factory()->create(['status' => 'OPEN']);

    RaceRegistration::factory()
        ->accepted()
        ->create(['race_id' => $race->id]);

    $response = $this->actingAs($this->staffUser)
        ->get(route('staff.races.engaged-pdf', $race));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/pdf');
});

test('guest cannot access engaged PDF route', function () {
    $race = Race::factory()->create(['status' => 'OPEN']);

    $response = $this->get(route('staff.races.engaged-pdf', $race));

    $response->assertRedirect(route('login'));
});

test('pilot cannot access engaged PDF route', function () {
    $pilot = User::factory()->create();
    $pilot->assignRole('PILOTE');

    $race = Race::factory()->create(['status' => 'OPEN']);

    $response = $this->actingAs($pilot)
        ->get(route('staff.races.engaged-pdf', $race));

    $response->assertForbidden();
});
