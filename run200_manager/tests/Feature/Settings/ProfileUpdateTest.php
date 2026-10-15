<?php

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

test('profile page is displayed', function () {
    $this->actingAs($user = User::factory()->create());

    $this->get(route('profile.edit'))->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toEqual('Test User');
    expect($user->email)->toEqual('test@example.com');
    expect($user->email_verified_at)->toBeNull();
});

test('email verification status is unchanged when email address is unchanged', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('name', 'Test User')
        ->set('email', $user->email)
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('profile verification resend is limited per account and recovers after the window', function () {
    Notification::fake();
    $user = User::factory()->unverified()->create();
    $component = Livewire::actingAs($user)->test('pages::settings.profile');

    for ($attempt = 0; $attempt < 3; $attempt++) {
        $component->call('resendVerificationNotification');
    }

    Notification::assertSentTo($user, VerifyEmail::class);
    Notification::assertCount(3);

    $component->call('resendVerificationNotification')
        ->assertSee('Trop de demandes de lien.');
    Notification::assertCount(3);

    $this->travel(11)->minutes();
    Livewire::actingAs($user)->test('pages::settings.profile')
        ->call('resendVerificationNotification');
    Notification::assertCount(4);
});

test('profile verification resend is also limited across accounts sharing an IP', function () {
    Notification::fake();
    $user = User::factory()->unverified()->create();
    $ipKey = 'verification-resend:ip:'.hash('sha256', '127.0.0.1');
    for ($attempt = 0; $attempt < 60; $attempt++) {
        RateLimiter::hit($ipKey, 600);
    }

    Livewire::actingAs($user)->test('pages::settings.profile')
        ->call('resendVerificationNotification')
        ->assertSee('Trop de demandes de lien.');

    Notification::assertNothingSent();
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.delete-user-form')
        ->set('password', 'password')
        ->call('deleteUser');

    $response
        ->assertHasNoErrors()
        ->assertRedirect('/');

    // User uses SoftDeletes, so check that user is soft deleted
    expect($user->fresh()->deleted_at)->not->toBeNull();
    expect(auth()->check())->toBeFalse();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.delete-user-form')
        ->set('password', 'wrong-password')
        ->call('deleteUser');

    $response->assertHasErrors(['password']);

    expect($user->fresh())->not->toBeNull();
});
