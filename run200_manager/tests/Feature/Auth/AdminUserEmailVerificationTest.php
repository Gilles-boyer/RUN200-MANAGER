<?php

use App\Livewire\Admin\Users\Index;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

test('an admin changing an account email invalidates its previous verification', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('ADMIN');
    $pilot = User::factory()->create(['email_verified_at' => now()]);
    $pilot->assignRole('PILOTE');

    Livewire::actingAs($admin)->test(Index::class)
        ->call('editUser', $pilot->id)
        ->set('editEmail', 'new-pilot-address@example.com')
        ->call('updateUser')
        ->assertHasNoErrors();

    expect($pilot->fresh()->email)->toBe('new-pilot-address@example.com')
        ->and($pilot->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('an admin changing only an account name retains its email verification', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('ADMIN');
    $pilot = User::factory()->create(['email_verified_at' => now()]);
    $pilot->assignRole('PILOTE');

    Livewire::actingAs($admin)->test(Index::class)
        ->call('editUser', $pilot->id)
        ->set('editName', 'Nouveau Nom')
        ->call('updateUser')
        ->assertHasNoErrors();

    expect($pilot->fresh()->hasVerifiedEmail())->toBeTrue();
});
