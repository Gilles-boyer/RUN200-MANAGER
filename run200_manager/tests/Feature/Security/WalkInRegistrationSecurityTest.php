<?php

use App\Events\RegistrationAccepted;
use App\Livewire\Staff\Registrations\WalkInRegistration;
use App\Models\CarCategory;
use App\Models\Payment;
use App\Models\Race;
use App\Models\RaceRegistration;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');
    $this->actingAs($staff);
    Mail::fake();
    Event::fake([RegistrationAccepted::class]);
});

test('walk-in account has an unpredictable password and no unproven email verification', function () {
    $race = Race::factory()->open()->create(['entry_fee_cents' => 5000]);
    $category = CarCategory::factory()->create();

    Livewire::test(WalkInRegistration::class)
        ->set('currentStep', 4)
        ->set('pilotMode', 'create')
        ->set('newPilotFirstName', 'Marie')
        ->set('newPilotLastName', 'Durand')
        ->set('newPilotEmail', 'walkin@example.test')
        ->set('newPilotLicense', '625319')
        ->set('newPilotBirthDate', '1990-01-01')
        ->set('newPilotBirthPlace', 'Saint-Denis')
        ->set('newPilotPhone', '0692000000')
        ->set('newPilotAddress', '1 rue du Circuit')
        ->set('carMode', 'create')
        ->set('newCarRaceNumber', 871)
        ->set('newCarMake', 'Renault')
        ->set('newCarModel', 'Clio')
        ->set('newCarCategoryId', $category->id)
        ->set('selectedRaceId', $race->id)
        ->set('paymentReceived', false)
        ->call('submit')
        ->assertSet('currentStep', 5);

    $pilotUser = User::where('email', 'walkin@example.test')->sole();
    $registration = RaceRegistration::where('pilot_id', $pilotUser->pilot->id)->sole();

    expect(Hash::check('password', $pilotUser->password))->toBeFalse()
        ->and($pilotUser->email_verified_at)->toBeNull()
        ->and($registration->status)->toBe('PENDING_PAYMENT')
        ->and($registration->validated_at)->toBeNull()
        ->and($registration->validated_by)->toBeNull()
        ->and(Payment::where('race_registration_id', $registration->id)->sole()->status->value)->toBe('pending');

    Event::assertNotDispatched(RegistrationAccepted::class);
});
