<?php

use App\Livewire\Pilot\Registrations\Create;
use App\Models\Car;
use App\Models\DocumentCategory;
use App\Models\Pilot;
use App\Models\Race;
use App\Models\RaceDocument;
use App\Models\RaceDocumentVersion;
use App\Models\RaceRegistration;
use App\Models\User;
use App\Support\UserFacingError;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    Storage::fake('race-documents');
});

function createPublishedRegulation(Race $race, User $user, string $visibility = 'PUBLIC'): RaceDocument
{
    $category = DocumentCategory::firstOrCreate(
        ['slug' => 'reglement-particulier'],
        ['name' => 'Règlement particulier']
    );

    $document = RaceDocument::create([
        'race_id' => $race->id,
        'category_id' => $category->id,
        'title' => 'Règlement de la course',
        'status' => 'PUBLISHED',
        'visibility' => $visibility,
        'published_at' => now(),
        'published_by' => $user->id,
    ]);

    Storage::disk('race-documents')->put('rules/'.$document->id.'.pdf', '%PDF-1.4 test');
    RaceDocumentVersion::create([
        'document_id' => $document->id,
        'version' => 1,
        'file_path' => 'rules/'.$document->id.'.pdf',
        'original_filename' => 'reglement.pdf',
        'mime_type' => 'application/pdf',
        'file_size' => 13,
        'checksum' => str_repeat('a', 64),
        'uploaded_by' => $user->id,
    ]);

    return $document;
}

test('pilot sees information and warning messages after redirect', function () {
    $user = User::factory()->create();
    $user->assignRole('PILOTE');

    $this->actingAs($user)
        ->withSession(['info' => 'Inscription en attente de paiement', 'warning' => 'Complétez votre profil'])
        ->get(route('pilot.dashboard'))
        ->assertOk()
        ->assertSee('Inscription en attente de paiement')
        ->assertSee('Complétez votre profil');
});

test('pilot cannot accept a regulation that is not publicly available', function () {
    $user = User::factory()->create();
    $user->assignRole('PILOTE');
    $pilot = Pilot::factory()->for($user)->create();
    $car = Car::factory()->for($pilot)->create();
    $race = Race::factory()->open()->create();

    createPublishedRegulation($race, $user, 'REGISTERED_ONLY');

    Livewire::actingAs($user)->test(Create::class, ['race' => $race])
        ->assertSee('Le règlement de cette course n’est pas encore disponible')
        ->set('selectedCarId', $car->id)
        ->set('confirmTerms', true)
        ->call('submit')
        ->assertSee('Le règlement de cette course n’est pas encore disponible');

    expect(RaceRegistration::count())->toBe(0);
});

test('race list explains why registration is unavailable until the regulation is published', function () {
    $user = User::factory()->create();
    $user->assignRole('PILOTE');
    Pilot::factory()->for($user)->create();
    $race = Race::factory()->open()->create();
    $race->season->update(['is_active' => true]);

    $this->actingAs($user)->get(route('pilot.races.index'))
        ->assertOk()
        ->assertSee('Règlement à publier avant inscription');

    createPublishedRegulation($race, $user);

    $this->get(route('pilot.races.index'))
        ->assertOk()
        ->assertSee(route('pilot.registrations.create', $race));
});

test('pilot can read the published regulation before accepting and registering', function () {
    $user = User::factory()->create();
    $user->assignRole('PILOTE');
    $pilot = Pilot::factory()->for($user)->create();
    $car = Car::factory()->for($pilot)->create();
    $race = Race::factory()->open()->create();
    $regulation = createPublishedRegulation($race, $user);

    $this->get(route('board.view', $regulation->slug))->assertOk();

    Livewire::actingAs($user)->test(Create::class, ['race' => $race])
        ->assertSee(route('board.view', $regulation->slug))
        ->set('selectedCarId', $car->id)
        ->call('submit')
        ->assertSee('Consultez le règlement de la course')
        ->set('confirmTerms', true)
        ->call('submit')
        ->assertRedirect();

    expect(RaceRegistration::where('race_id', $race->id)->where('car_id', $car->id)->exists())->toBeTrue();
});

test('technical details stay in logs while the user receives a support reference', function () {
    $message = UserFacingError::message(new RuntimeException('SQLSTATE private table name'), 'Impossible d’enregistrer votre inscription.');

    expect($message)
        ->toContain('Réessayez', 'contactez l’équipe', 'code ')
        ->not->toContain('SQLSTATE', 'private table name');
});
