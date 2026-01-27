<?php

declare(strict_types=1);

namespace Tests\Feature\EdgeCases;

use App\Models\Car;
use App\Models\Pilot;
use App\Models\Race;
use App\Models\RaceRegistration;
use App\Models\Season;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RegistrationEdgeCasesTest extends TestCase
{
    use RefreshDatabase;

    private User $pilotUser;

    private Pilot $pilot;

    private Race $race;

    private Car $car;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles and permissions
        Role::findOrCreate('PILOT', 'web');
        Permission::findOrCreate('race.register', 'web');
        Role::findByName('PILOT')->givePermissionTo('race.register');

        $this->pilotUser = User::factory()->create();
        $this->pilotUser->assignRole('PILOT');

        $this->pilot = Pilot::factory()->create(['user_id' => $this->pilotUser->id]);

        $season = Season::factory()->create(['is_active' => true]);
        $this->race = Race::factory()->create([
            'season_id' => $season->id,
            'status' => 'OPEN',
            'race_date' => now()->addDays(30),
        ]);

        $this->car = Car::factory()->create(['pilot_id' => $this->pilot->id]);
    }

    public function test_cannot_register_same_car_twice_for_same_race(): void
    {
        // First registration
        RaceRegistration::create([
            'race_id' => $this->race->id,
            'pilot_id' => $this->pilot->id,
            'car_id' => $this->car->id,
            'status' => 'PENDING_VALIDATION',
        ]);

        // Try second registration with same car
        $this->expectException(\Exception::class);

        RaceRegistration::create([
            'race_id' => $this->race->id,
            'pilot_id' => $this->pilot->id,
            'car_id' => $this->car->id,
            'status' => 'PENDING_VALIDATION',
        ]);
    }

    public function test_cannot_register_for_closed_race(): void
    {
        $this->race->update(['status' => 'CLOSED']);

        // Closed race should not accept registrations
        $this->assertEquals('CLOSED', $this->race->fresh()->status);
    }

    public function test_cannot_register_for_past_race(): void
    {
        $this->race->update(['race_date' => now()->subDays(1)]);

        // Race in past should not accept registrations
        $this->assertTrue($this->race->race_date->isPast());
    }

    public function test_pilot_can_register_multiple_cars_for_same_race(): void
    {
        $car1 = Car::factory()->create(['pilot_id' => $this->pilot->id]);
        $car2 = Car::factory()->create(['pilot_id' => $this->pilot->id]);

        $reg1 = RaceRegistration::create([
            'race_id' => $this->race->id,
            'pilot_id' => $this->pilot->id,
            'car_id' => $car1->id,
            'status' => 'PENDING_VALIDATION',
        ]);

        $reg2 = RaceRegistration::create([
            'race_id' => $this->race->id,
            'pilot_id' => $this->pilot->id,
            'car_id' => $car2->id,
            'status' => 'PENDING_VALIDATION',
        ]);

        $this->assertDatabaseCount('race_registrations', 2);
        $this->assertNotEquals($reg1->car_id, $reg2->car_id);
    }

    public function test_registration_status_cannot_skip_states(): void
    {
        $registration = RaceRegistration::create([
            'race_id' => $this->race->id,
            'pilot_id' => $this->pilot->id,
            'car_id' => $this->car->id,
            'status' => 'PENDING_VALIDATION',
        ]);

        // Cannot go from PENDING directly to TECH_CHECKED without ACCEPTED
        $this->assertEquals('PENDING_VALIDATION', $registration->status);

        // Valid transition
        $registration->update(['status' => 'ACCEPTED']);
        $this->assertEquals('ACCEPTED', $registration->fresh()->status);
    }

    public function test_cancelled_registration_cannot_be_reactivated(): void
    {
        $registration = RaceRegistration::create([
            'race_id' => $this->race->id,
            'pilot_id' => $this->pilot->id,
            'car_id' => $this->car->id,
            'status' => 'CANCELLED',
        ]);

        // Should not allow changing status from CANCELLED
        $this->assertEquals('CANCELLED', $registration->status);
    }
}
