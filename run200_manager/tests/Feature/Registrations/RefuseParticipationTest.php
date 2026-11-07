<?php

use App\Application\Payments\UseCases\HandleStripeWebhook;
use App\Application\Registrations\UseCases\ReopenRefusedRegistration;
use App\Application\Registrations\UseCases\ScanCheckpoint;
use App\Application\Registrations\UseCases\SubmitRegistration;
use App\Application\Registrations\UseCases\ValidateRegistration;
use App\Domain\Payment\Enums\PaymentMethod;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Events\PaymentConfirmed;
use App\Events\RegistrationRefused as RegistrationRefusedEvent;
use App\Infrastructure\Qr\QrTokenService;
use App\Listeners\SendECardAfterPayment;
use App\Listeners\SendRegistrationRefusedNotification;
use App\Livewire\Staff\Registrations\CheckpointsManager;
use App\Mail\ECardMail;
use App\Mail\RegistrationRefused as RegistrationRefusedMail;
use App\Models\Checkpoint;
use App\Models\CheckpointPassage;
use App\Models\PaddockSpot;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use App\Models\RaceRegistration;
use App\Models\TechInspection;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    Mail::fake();
});

function participationPayment(RaceRegistration $registration, PaymentStatus $status): Payment
{
    return Payment::create([
        'race_registration_id' => $registration->id,
        'user_id' => $registration->pilot->user_id,
        'amount' => 100,
        'amount_cents' => 10000,
        'currency' => 'EUR',
        'method' => PaymentMethod::STRIPE,
        'status' => $status,
        'stripe_session_id' => 'cs_test_'.uniqid(),
        'stripe_payment_intent_id' => $status === PaymentStatus::PAID ? 'pi_test_'.uniqid() : null,
        'paid_at' => $status === PaymentStatus::PAID ? now() : null,
    ]);
}

test('refusal changes only sporting state and keeps an open Stripe payment', function () {
    expect(app()->basePath())->toBe(getcwd());
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');
    $this->actingAs($staff);
    $registration = RaceRegistration::factory()->create([
        'status' => 'ACCEPTED', 'paddock' => 'P12',
    ]);
    $payment = participationPayment($registration, PaymentStatus::PENDING);

    $refused = app(ValidateRegistration::class)->refuse($registration, 'Participation non autorisée', $staff);

    expect($refused->status)->toBe('REFUSED')
        ->and($refused->paddock)->toBeNull()
        ->and($payment->fresh()->status)->toBe(PaymentStatus::PENDING)
        ->and($payment->fresh()->stripe_session_id)->toBe($payment->stripe_session_id);
    expect((new RegistrationRefusedMail($refused))->render())->not->toContain('automatiquement remboursé');

    expect(fn () => app(ValidateRegistration::class)->refuse($refused, 'Participation non autorisée', $staff))
        ->toThrow(InvalidArgumentException::class);
    expect(Activity::query()->where('subject_type', RaceRegistration::class)
        ->where('subject_id', $registration->id)->where('description', 'registration.refused')->count())->toBe(1);
});

test('refusal blocks a stale scan and reactivation by the pilot', function () {
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');
    $this->actingAs($staff);
    $registration = RaceRegistration::factory()->accepted()->create();
    $checkpoint = Checkpoint::factory()->adminCheck()->create();
    app(ValidateRegistration::class)->refuse($registration, 'Participation non autorisée', $staff);

    expect(fn () => app(ScanCheckpoint::class)->scanWithRegistration($registration, $checkpoint->code, $staff))
        ->toThrow(InvalidArgumentException::class);
    expect(CheckpointPassage::where('race_registration_id', $registration->id)->count())->toBe(0);
    expect($registration->fresh()->status)->toBe('REFUSED');

    expect(fn () => app(SubmitRegistration::class)->execute($registration->race, $registration->pilot, $registration->car))
        ->toThrow(InvalidArgumentException::class);
    expect($registration->fresh()->status)->toBe('REFUSED');
});

test('staff reopens the same refused registration after confirming payment review', function () {
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');
    $this->actingAs($staff);
    $registration = RaceRegistration::factory()->refused()->create();
    $reopen = app(ReopenRefusedRegistration::class);

    expect($reopen->previewStatus($registration))->toBe('PENDING_PAYMENT');
    expect(fn () => $reopen->execute($registration, 'Refus saisi par erreur', $staff, false))
        ->toThrow(InvalidArgumentException::class);

    $updated = $reopen->execute($registration, 'Refus saisi par erreur', $staff, true);
    expect($updated->id)->toBe($registration->id)
        ->and($updated->status)->toBe('PENDING_PAYMENT');
});

test('historical admin and technical passages restore a coherent sporting state', function () {
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');
    $this->actingAs($staff);
    $registration = RaceRegistration::factory()->refused()->create();
    $admin = Checkpoint::factory()->adminCheck()->create();
    $tech = Checkpoint::factory()->techCheck()->create();
    CheckpointPassage::factory()->create([
        'race_registration_id' => $registration->id, 'checkpoint_id' => $admin->id,
        'scanned_at' => now()->subHour(),
    ]);
    CheckpointPassage::factory()->create([
        'race_registration_id' => $registration->id, 'checkpoint_id' => $tech->id,
        'scanned_at' => now(),
    ]);
    TechInspection::factory()->failed()->forRegistration($registration)->create();

    $reopen = app(ReopenRefusedRegistration::class);
    expect($reopen->previewStatus($registration))->toBe('TECH_CHECKED_FAIL');
    expect($reopen->execute($registration, 'Correction du refus saisi', $staff, true)->status)->toBe('TECH_CHECKED_FAIL');
});

test('payment confirmation does not email an E-Card for a refused registration', function () {
    $registration = RaceRegistration::factory()->refused()->create();
    $payment = participationPayment($registration, PaymentStatus::PAID);

    app(SendECardAfterPayment::class)->handle(new PaymentConfirmed($payment));

    Mail::assertNotSent(ECardMail::class);
});

test('a late Stripe webhook records payment and preserves refusal', function () {
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');
    $this->actingAs($staff);
    $registration = RaceRegistration::factory()->create(['status' => 'PENDING_PAYMENT']);
    $payment = participationPayment($registration, PaymentStatus::PENDING);
    app(ValidateRegistration::class)->refuse($registration, 'Participation non autorisée', $staff);

    app(HandleStripeWebhook::class)->handleCheckoutCompleted([
        'id' => $payment->stripe_session_id,
        'payment_intent' => 'pi_test_late',
        'amount_total' => 10000,
        'currency' => 'eur',
        'payment_status' => 'paid',
    ], 'evt_test_late');

    expect($payment->fresh()->status)->toBe(PaymentStatus::PAID)
        ->and($registration->fresh()->status)->toBe('REFUSED');
});

test('an unauthorized user cannot refuse or reopen a registration', function () {
    $pilotUser = User::factory()->create();
    $pilotUser->assignRole('PILOTE');
    $this->actingAs($pilotUser);
    $pending = RaceRegistration::factory()->create();
    $refused = RaceRegistration::factory()->refused()->create();

    expect(fn () => app(ValidateRegistration::class)->refuse($pending, 'Participation non autorisée', $pilotUser))
        ->toThrow(AuthorizationException::class);
    expect(fn () => app(ReopenRefusedRegistration::class)->execute($refused, 'Correction du refus saisi', $pilotUser, true))
        ->toThrow(AuthorizationException::class);
    expect($pending->fresh()->status)->toBe('PENDING_VALIDATION')
        ->and($refused->fresh()->status)->toBe('REFUSED');
});

test('an administrator can correct a historical passage without clearing refusal', function () {
    $adminUser = User::factory()->create();
    $adminUser->assignRole('ADMIN');
    $this->actingAs($adminUser);
    $registration = RaceRegistration::factory()->refused()->create();
    $checkpoint = Checkpoint::factory()->adminCheck()->create();
    $passage = CheckpointPassage::factory()->create([
        'race_registration_id' => $registration->id,
        'checkpoint_id' => $checkpoint->id,
    ]);

    Livewire::test(CheckpointsManager::class, ['registration' => $registration])
        ->call('confirmDeletePassage', $passage->id)
        ->call('deletePassage');

    expect($registration->fresh()->status)->toBe('REFUSED')
        ->and(CheckpointPassage::whereKey($passage->id)->exists())->toBeFalse();
});

test('non-admin staff sees a clear reason when refused historical passage cannot be deleted', function () {
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');
    $this->actingAs($staff);
    $registration = RaceRegistration::factory()->refused()->create();
    $checkpoint = Checkpoint::factory()->adminCheck()->create();
    $passage = CheckpointPassage::factory()->create([
        'race_registration_id' => $registration->id,
        'checkpoint_id' => $checkpoint->id,
    ]);

    Livewire::test(CheckpointsManager::class, ['registration' => $registration])
        ->call('confirmDeletePassage', $passage->id)
        ->call('deletePassage')
        ->assertSee('Seul un administrateur peut corriger ce passage historique.');

    expect($registration->fresh()->status)->toBe('REFUSED')
        ->and(CheckpointPassage::whereKey($passage->id)->exists())->toBeTrue();
});

test('refusal preserves a paid payment and its receipt while releasing paddock and QR', function () {
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');
    $this->actingAs($staff);
    $spot = PaddockSpot::create(['spot_number' => 'A12', 'zone' => 'A']);
    $registration = RaceRegistration::factory()->accepted()->create([
        'paddock' => 'A12', 'paddock_spot_id' => $spot->id,
    ]);
    $payment = participationPayment($registration, PaymentStatus::PAID);
    $receipt = PaymentReceipt::create([
        'payment_id' => $payment->id, 'issued_at' => now(),
        'issuer_name' => 'Association test', 'issuer_siret' => '12345678901234',
        'issuer_address' => 'Adresse test', 'issuer_email' => 'test@example.test',
        'issuer_phone' => '0000000000', 'payer_name' => 'Pilote test',
        'race_name' => 'Course test', 'amount_cents' => 10000,
        'payment_status_at_issue' => 'paid', 'currency' => 'EUR',
        'payment_method' => 'stripe', 'email_status' => 'sent',
    ]);
    app(QrTokenService::class)->generate($registration);
    expect($registration->qrToken()->exists())->toBeTrue();

    app(ValidateRegistration::class)->refuse($registration, 'Participation non autorisée', $staff);

    expect($registration->fresh()->status)->toBe('REFUSED')
        ->and($registration->fresh()->paddock_spot_id)->toBeNull()
        ->and($registration->qrToken()->exists())->toBeFalse()
        ->and($payment->fresh()->status)->toBe(PaymentStatus::PAID)
        ->and($receipt->fresh()->email_status)->toBe('sent');
    expect(Activity::query()->where('subject_type', RaceRegistration::class)
        ->where('subject_id', $registration->id)->where('description', 'registration.refused')->count())->toBe(1);
});

test('an existing entry passage prevents a late refusal even when status is stale', function () {
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');
    $this->actingAs($staff);
    $registration = RaceRegistration::factory()->accepted()->create();
    $entry = Checkpoint::factory()->entry()->create();
    CheckpointPassage::factory()->create([
        'race_registration_id' => $registration->id, 'checkpoint_id' => $entry->id,
    ]);

    expect(fn () => app(ValidateRegistration::class)->refuse($registration, 'Participation non autorisée', $staff))
        ->toThrow(InvalidArgumentException::class);
    expect($registration->fresh()->status)->toBe('ACCEPTED');
});

test('every pre-entry sporting status can be refused', function (string $status) {
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');
    $this->actingAs($staff);
    $registration = RaceRegistration::factory()->create(['status' => $status]);

    expect(app(ValidateRegistration::class)->refuse($registration, 'Participation non autorisée', $staff)->status)
        ->toBe('REFUSED');
})->with(['PENDING_VALIDATION', 'PENDING_PAYMENT', 'ACCEPTED', 'ADMIN_CHECKED', 'TECH_CHECKED_OK', 'TECH_CHECKED_FAIL']);

test('entry and final statuses cannot be refused', function (string $status) {
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');
    $this->actingAs($staff);
    $registration = RaceRegistration::factory()->create(['status' => $status]);

    expect(fn () => app(ValidateRegistration::class)->refuse($registration, 'Participation non autorisée', $staff))
        ->toThrow(InvalidArgumentException::class);
    expect($registration->fresh()->status)->toBe($status);
})->with(['ENTRY_SCANNED', 'BRACELET_GIVEN', 'RESULTS_IMPORTED', 'PUBLISHED', 'REFUSED', 'CANCELLED']);

test('a queued refusal email is skipped after reopen and a second refusal gets only its own mail', function () {
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');
    $this->actingAs($staff);
    $registration = RaceRegistration::factory()->create();
    app(ValidateRegistration::class)->refuse($registration, 'Premier refus saisi', $staff);
    $firstActivityId = Activity::query()->where('subject_type', RaceRegistration::class)
        ->where('subject_id', $registration->id)->where('description', 'registration.refused')
        ->latest('id')->value('id');
    $oldQueuedEvent = new RegistrationRefusedEvent($registration, $firstActivityId);

    app(ReopenRefusedRegistration::class)->execute($registration, 'Correction du refus saisi', $staff, true);
    $listener = app(SendRegistrationRefusedNotification::class);
    Mail::fake();
    $listener->handle($oldQueuedEvent);
    Mail::assertNotSent(RegistrationRefusedMail::class);

    app(ValidateRegistration::class)->refuse($registration, 'Second refus motivé', $staff);
    $secondActivityId = Activity::query()->where('subject_type', RaceRegistration::class)
        ->where('subject_id', $registration->id)->where('description', 'registration.refused')
        ->latest('id')->value('id');
    Mail::fake();
    $listener->handle($oldQueuedEvent);
    Mail::assertNotSent(RegistrationRefusedMail::class);
    $listener->handle(new RegistrationRefusedEvent($registration, $secondActivityId));
    Mail::assertSent(RegistrationRefusedMail::class, 1);
});
