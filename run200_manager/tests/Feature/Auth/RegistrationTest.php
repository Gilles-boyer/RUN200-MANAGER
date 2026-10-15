<?php

use App\Models\Pilot;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Ensure PILOTE role exists
    Role::firstOrCreate(['name' => 'PILOTE', 'guard_name' => 'web']);
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register as pilot', function () {
    $response = $this->post(route('register.store'), [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'test@example.com',
        'phone' => '+33 6 12 34 56 78',
        'license_number' => '123456',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => '1',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();

    // Verify user was created with correct name (formatted as Title Case)
    $user = User::where('email', 'test@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->name)->toBe('John Doe');

    // Verify user has PILOTE role
    expect($user->hasRole('PILOTE'))->toBeTrue();

    // Verify pilot profile was created with proper formatting
    $pilot = Pilot::where('user_id', $user->id)->first();
    expect($pilot)->not->toBeNull();
    expect($pilot->first_name)->toBe('John'); // Title Case
    expect($pilot->last_name)->toBe('DOE'); // Uppercase
    expect($pilot->phone)->toBe('06 12 34 56 78'); // Formatted display
    expect($pilot->getRawOriginal('phone'))->toBe('0612345678'); // Stored normalized
    expect($pilot->license_number)->toBe('123456');
});

test('registration requires all pilot fields', function () {
    $response = $this->post(route('register.store'), [
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors(['first_name', 'last_name', 'phone', 'license_number']);
});

test('registration validates unique license number', function () {
    // Create existing pilot with license
    $existingUser = User::factory()->create();
    Pilot::create([
        'user_id' => $existingUser->id,
        'first_name' => 'Existing',
        'last_name' => 'Pilot',
        'license_number' => '123456',
        'phone' => '+33 6 00 00 00 00',
        'birth_date' => '1990-01-01',
    ]);

    $response = $this->post(route('register.store'), [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'test@example.com',
        'phone' => '+33 6 12 34 56 78',
        'license_number' => '123456', // Same as existing
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors(['license_number']);
});

test('registration rejects the seventh attempt for the same email and IP with a clear JSON error', function () {
    $payload = ['email' => 'pilot@example.com'];

    for ($attempt = 0; $attempt < 6; $attempt++) {
        $this->postJson(route('register.store'), $payload)->assertUnprocessable();
    }

    $response = $this->postJson(route('register.store'), $payload);

    $response->assertStatus(429)
        ->assertJsonPath('message', 'Trop de tentatives d’inscription. Veuillez patienter une minute avant de réessayer.')
        ->assertHeader('Retry-After');

    $this->postJson(route('register.store'), ['email' => 'other@example.com'])
        ->assertUnprocessable();

    $this->assertDatabaseCount('users', 0);
});

test('registration shows a clear web error without repeating the password after too many attempts', function () {
    $payload = [
        'email' => 'pilot@example.com',
        'password' => 'unsecret',
        'password_confirmation' => 'unsecret',
    ];

    for ($attempt = 0; $attempt < 6; $attempt++) {
        $this->post(route('register.store'), $payload)->assertSessionHasErrors();
    }

    $response = $this->post(route('register.store'), $payload);

    $response->assertRedirect(route('register'))
        ->assertSessionHasErrors(['registration'])
        ->assertSessionHasInput('email', 'pilot@example.com')
        ->assertSessionMissing('_old_input.password')
        ->assertHeader('Retry-After');

    $this->get(route('register'))
        ->assertOk()
        ->assertSee('Trop de tentatives d’inscription. Veuillez patienter une minute avant de réessayer.');
});

test('registration also limits attempts when emails are changed', function () {
    for ($attempt = 0; $attempt < 30; $attempt++) {
        $this->postJson(route('register.store'), ['email' => "pilot{$attempt}@example.com"])
            ->assertUnprocessable();
    }

    $this->postJson(route('register.store'), ['email' => 'another@example.com'])
        ->assertStatus(429)
        ->assertHeader('Retry-After');
});

test('registration attempts from another IP are counted separately', function () {
    $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.10']);

    for ($attempt = 0; $attempt < 6; $attempt++) {
        $this->postJson(route('register.store'), ['email' => 'pilot@example.com'])
            ->assertUnprocessable();
    }

    $this->postJson(route('register.store'), ['email' => 'pilot@example.com'])
        ->assertStatus(429);

    $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.11'])
        ->postJson(route('register.store'), ['email' => 'pilot@example.com'])
        ->assertUnprocessable();
});

test('registration limiter handles a non-string email as a validation error', function () {
    $this->postJson(route('register.store'), ['email' => ['pilot@example.com']])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});
