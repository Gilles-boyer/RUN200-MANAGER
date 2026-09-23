<?php

use App\Application\Championship\UseCases\RebuildSeasonStandings;
use App\Livewire\Pilot\Cars\Form;
use App\Livewire\Pilot\Cars\Index;
use App\Models\Car;
use App\Models\Payment;
use App\Models\Pilot;
use App\Models\Race;
use App\Models\RaceRegistration;
use App\Models\RaceResult;
use App\Models\Season;
use App\Models\SeasonStanding;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolesAndPermissionsSeeder']);
});

test('a car with a registration cannot be deleted and payment history remains intact', function () {
    $user = User::factory()->create();
    $user->assignRole('PILOTE');
    $pilot = Pilot::factory()->create(['user_id' => $user->id]);
    $car = Car::factory()->create(['pilot_id' => $pilot->id]);
    $season = Season::factory()->create();
    $race = Race::factory()->for($season)->create(['status' => 'PUBLISHED']);
    $registration = RaceRegistration::factory()->create([
        'race_id' => $race->id,
        'pilot_id' => $pilot->id,
        'car_id' => $car->id,
        'status' => 'PUBLISHED',
    ]);
    $result = RaceResult::factory()->create([
        'race_id' => $race->id,
        'race_registration_id' => $registration->id,
        'position' => 1,
    ]);
    $payment = Payment::create([
        'race_registration_id' => $registration->id,
        'user_id' => $user->id,
        'amount' => 50,
        'amount_cents' => 5000,
        'currency' => 'EUR',
        'method' => 'stripe',
        'status' => 'paid',
        'paid_at' => now(),
    ]);

    $this->actingAs($user);

    Livewire::test(Index::class)
        ->assertSee('Cette voiture est liée à une course')
        ->call('deleteCar', $car->id)
        ->assertSet('deleteError', Car::DELETION_BLOCKED_MESSAGE);

    Livewire::test(Form::class, ['car' => $car->id])
        ->call('delete')
        ->assertHasErrors(['delete']);

    $this->assertDatabaseHas('cars', ['id' => $car->id]);
    $this->assertDatabaseHas('race_registrations', ['id' => $registration->id, 'car_id' => $car->id]);
    $this->assertDatabaseHas('payments', ['id' => $payment->id, 'race_registration_id' => $registration->id]);
    $this->assertDatabaseHas('race_results', ['id' => $result->id, 'race_registration_id' => $registration->id]);

    expect(fn () => $car->delete())->toThrow(DomainException::class);

    (new RebuildSeasonStandings)->execute($season);
    expect(SeasonStanding::forSeason($season->id)->where('pilot_id', $pilot->id)->first()?->base_points)->toBe(25);
});

test('database foreign key prevents bypassing the car deletion guard', function () {
    $registration = RaceRegistration::factory()->create();

    expect(fn () => DB::table('cars')->where('id', $registration->car_id)->delete())
        ->toThrow(QueryException::class);

    $this->assertDatabaseHas('race_registrations', ['id' => $registration->id]);
});

test('an unused car can still be deleted', function () {
    $user = User::factory()->create();
    $user->assignRole('PILOTE');
    $pilot = Pilot::factory()->create(['user_id' => $user->id]);
    $car = Car::factory()->create(['pilot_id' => $pilot->id]);

    $this->actingAs($user);
    Livewire::test(Index::class)->call('deleteCar', $car->id);

    $this->assertDatabaseMissing('cars', ['id' => $car->id]);
});
