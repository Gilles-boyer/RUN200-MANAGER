<?php

use App\Models\Pilot;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Spatie\Permission\Models\Role;

test('email verification screen can be rendered', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get(route('verification.notice'));

    $response->assertOk();
});

test('unverified existing users retain dashboard and account security settings access', function () {
    $user = User::factory()->unverified()->create();
    Role::firstOrCreate(['name' => 'PILOTE', 'guard_name' => 'web']);
    $user->assignRole('PILOTE');
    Pilot::factory()->for($user)->create();

    $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('pilot.dashboard'));
    $this->get(route('pilot.dashboard'))->assertOk();
    $this->get(route('profile.edit'))->assertOk();
    $this->get(route('user-password.edit'))->assertOk();
    $this->get(route('two-factor.show'))->assertRedirect(route('password.confirm'));
});

test('an existing unverified user can request the verification notification', function () {
    Notification::fake();
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)->post(route('verification.send'))
        ->assertRedirect();

    Notification::assertSentTo($user, VerifyEmail::class);
    Notification::assertCount(1);
});

test('verification notification is in French and keeps the native signed link without receipt data', function () {
    $user = User::factory()->unverified()->create();
    $message = (new VerifyEmail)->toMail($user);

    expect($message->subject)->toBe('Confirmez votre adresse e-mail – RUN200 Manager')
        ->and($message->actionText)->toBe('Confirmer mon adresse e-mail')
        ->and(implode(' ', $message->introLines))->toContain('reçus de paiement')
        ->not->toContain('PDF', '100 €')
        ->and(URL::hasValidSignature(Request::create($message->actionUrl)))->toBeTrue();
});

test('email can be verified', function () {
    $user = User::factory()->unverified()->create();

    Event::fake();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = $this->actingAs($user)->get($verificationUrl);

    Event::assertDispatched(Verified::class);

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    $response->assertRedirect(route('dashboard', absolute: false).'?verified=1');
});

test('email is not verified with invalid hash', function () {
    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1('wrong-email')]
    );

    $this->actingAs($user)->get($verificationUrl);

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('already verified user visiting verification link is redirected without firing event again', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    Event::fake();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $this->actingAs($user)->get($verificationUrl)
        ->assertRedirect(route('dashboard', absolute: false).'?verified=1');

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    Event::assertNotDispatched(Verified::class);
});
