<?php

use App\Livewire\Pilot\Profile\Edit;
use App\Models\Pilot;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolesAndPermissionsSeeder']);
});

test('pilot profile show redirects to profile edit when no profile exists', function () {
    $user = User::factory()->create();
    $user->assignRole('PILOTE');

    $this->actingAs($user)
        ->get(route('pilot.profile.show'))
        ->assertRedirect(route('pilot.profile.edit'))
        ->assertSessionHas('info');
});

test('pilot profile edit page renders', function () {
    $user = User::factory()->create();
    $user->assignRole('PILOTE');

    $this->actingAs($user)
        ->get(route('pilot.profile.edit'))
        ->assertOk()
        ->assertSee('profil');
});

test('pilot can create profile via livewire', function () {
    $user = User::factory()->create();
    $user->assignRole('PILOTE');

    $this->actingAs($user);

    Livewire::test(Edit::class)
        ->set('first_name', 'Jean')
        ->set('last_name', 'Dupont')
        ->set('license_number', '123456')
        ->set('birth_date', '1990-01-01')
        ->set('birth_place', 'Paris')
        ->set('phone', '0600000000')
        ->set('address', '1 rue de Paris')
        ->set('city', 'Paris')
        ->set('postal_code', '75001')
        ->set('emergency_contact_name', 'Marie Dupont')
        ->set('emergency_contact_phone', '0601020304')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('pilot.profile.show'));

    // Vérifier avec les données formatées (uppercase pour last_name et city)
    $this->assertDatabaseHas('pilots', [
        'user_id' => $user->id,
        'first_name' => 'Jean',
        'last_name' => 'DUPONT', // Uppercase
        'license_number' => '123456',
        'birth_place' => 'Paris',
        'phone' => '0600000000',
        'city' => 'PARIS', // Uppercase
        'postal_code' => '75001',
        'emergency_contact_name' => 'Marie Dupont',
        'emergency_contact_phone' => '0601020304',
    ]);

    $pilot = Pilot::where('user_id', $user->id)->firstOrFail();
    expect($pilot->photo_path)->toBeNull();
});

test('pilot profile requires guardian fields when minor', function () {
    $user = User::factory()->create();
    $user->assignRole('PILOTE');

    $this->actingAs($user);

    Livewire::test(Edit::class)
        ->set('first_name', 'Alice')
        ->set('last_name', 'Martin')
        ->set('license_number', '111111')
        ->set('birth_date', '2015-01-01')
        ->set('birth_place', 'Lyon')
        ->set('phone', '0600000001')
        ->set('address', '2 rue de Lyon')
        ->set('city', 'Lyon')
        ->set('postal_code', '69001')
        ->set('emergency_contact_name', 'Contact Urgence')
        ->set('emergency_contact_phone', '0601020304')
        ->set('is_minor', true)
        ->call('save')
        ->assertHasErrors([
            'guardian_first_name',
            'guardian_last_name',
        ]);
});

test('pilot profile rejects duplicate license number', function () {
    Pilot::factory()->create(['license_number' => '222222']);

    $user = User::factory()->create();
    $user->assignRole('PILOTE');

    $this->actingAs($user);

    Livewire::test(Edit::class)
        ->set('first_name', 'Bob')
        ->set('last_name', 'Durand')
        ->set('license_number', '222222')
        ->set('birth_date', '1990-01-01')
        ->set('birth_place', 'Nice')
        ->set('phone', '0600000002')
        ->set('address', '3 rue de Nice')
        ->set('city', 'Nice')
        ->set('postal_code', '06000')
        ->set('emergency_contact_name', 'Contact Urgence')
        ->set('emergency_contact_phone', '0601020304')
        ->call('save')
        ->assertHasErrors(['license_number']);
});
