<?php

use App\Livewire\Admin\Races\Form;
use App\Livewire\Admin\Races\Index;
use App\Models\Race;
use App\Models\RaceResult;
use App\Models\Season;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('completed races retain public results access', function () {
    $race = Race::factory()->create(['status' => 'COMPLETED']);

    expect($race->isPublished())->toBeTrue();
    $this->get(route('public.results.race', $race))->assertOk();
});

test('the calendar links to results of completed races', function () {
    $season = Season::factory()->create(['is_active' => true]);
    $race = Race::factory()->for($season)->create([
        'status' => 'COMPLETED',
        'race_date' => now()->subDay(),
    ]);
    RaceResult::factory()->forRace($race)->create();

    $this->get(route('public.calendar'))
        ->assertOk()
        ->assertSee(route('public.results.race', $race));
});

test('editing a published race keeps its status unless results are unpublished', function () {
    $season = Season::factory()->create();
    $race = Race::factory()->for($season)->create(['status' => 'PUBLISHED']);

    Livewire::test(Form::class, ['race' => $race])
        ->set('name', 'Course mise à jour')
        ->call('save')
        ->assertHasNoErrors();
    expect($race->fresh()->status)->toBe('PUBLISHED');

    Livewire::test(Index::class)
        ->call('updateStatus', $race->id, 'CLOSED');
    expect($race->fresh()->status)->toBe('PUBLISHED');

    Livewire::test(Index::class)
        ->call('updateStatus', $race->id, 'COMPLETED');
    expect($race->fresh()->status)->toBe('COMPLETED');
});
