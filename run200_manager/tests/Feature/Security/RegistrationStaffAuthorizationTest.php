<?php

use App\Livewire\Staff\Registrations\Index;
use App\Models\RaceRegistration;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    Mail::fake();
});

test('entry staff cannot validate or assign paddock from registration list', function () {
    $entryStaff = User::factory()->create();
    $entryStaff->assignRole('STAFF_ENTREE');
    $this->actingAs($entryStaff);
    $registration = RaceRegistration::factory()->pending()->create();

    Livewire::test(Index::class)
        ->call('openValidationModal', $registration->id, 'accept')
        ->assertForbidden();

    Livewire::test(Index::class)
        ->set('selectedRegistration', $registration)
        ->set('validationAction', 'accept')
        ->call('confirmValidation')
        ->assertForbidden();

    Livewire::test(Index::class)
        ->set('selectedRegistration', $registration)
        ->set('paddockNumber', 'P12')
        ->call('assignPaddock')
        ->assertForbidden();

    expect($registration->fresh()->status)->toBe('PENDING_VALIDATION')
        ->and($registration->fresh()->paddock)->toBeNull();
});

test('administrative staff can only use accept or refuse decisions from registration list', function () {
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');
    $this->actingAs($staff);
    $registration = RaceRegistration::factory()->pending()->create();

    Livewire::test(Index::class)
        ->set('selectedRegistration', $registration)
        ->set('validationAction', 'publish')
        ->call('confirmValidation')
        ->assertHasErrors(['validationAction']);

    expect($registration->fresh()->status)->toBe('PENDING_VALIDATION');
});
