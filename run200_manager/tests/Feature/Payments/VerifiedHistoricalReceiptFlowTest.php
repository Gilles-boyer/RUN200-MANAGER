<?php

use App\Application\Payments\UseCases\HistoricalReceiptEmailHash;
use App\Application\Payments\UseCases\IssuePaymentReceipt;
use App\Application\Payments\UseCases\SendVerifiedHistoricalStripeReceipts;
use App\Infrastructure\Payments\Stripe\HistoricalStripePaymentLookup;
use App\Mail\PaymentConfirmed;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use App\Models\RaceRegistration;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Stripe\Charge;
use Stripe\PaymentIntent;

beforeEach(fn () => config()->set('payment_receipts.historical_delivery.mode', 'all'));

/** @return array{RaceRegistration, Payment} */
function verifiedFlowFixture(): array
{
    $registration = RaceRegistration::factory()->create();
    $user = $registration->pilot->user;
    $user->forceFill(['email_verified_at' => null])->save();
    $payment = Payment::create([
        'race_registration_id' => $registration->id,
        'user_id' => $user->id,
        'amount' => '50.00',
        'amount_cents' => 5000,
        'currency' => 'EUR',
        'method' => 'stripe',
        'status' => 'paid',
        'stripe_payment_intent_id' => 'pi_verified_flow_test',
        'paid_at' => now()->subDays(10),
    ]);

    return [$registration, $payment];
}

function fakeVerifiedFlowStripe(Payment $payment, RaceRegistration $registration): void
{
    $charge = Charge::constructFrom([
        'id' => 'ch_verified_flow_test',
        'object' => 'charge',
        'payment_intent' => $payment->stripe_payment_intent_id,
        'livemode' => true,
        'paid' => true,
        'captured' => true,
        'disputed' => false,
        'amount' => 5000,
        'amount_refunded' => 0,
        'currency' => 'eur',
    ]);
    $intent = PaymentIntent::constructFrom([
        'id' => $payment->stripe_payment_intent_id,
        'object' => 'payment_intent',
        'livemode' => true,
        'status' => 'succeeded',
        'amount' => 5000,
        'amount_received' => 5000,
        'currency' => 'eur',
        'metadata' => [
            'registration_id' => (string) $registration->id,
            'pilot_id' => (string) $registration->pilot_id,
        ],
        'latest_charge' => $charge,
    ]);
    app()->instance(HistoricalStripePaymentLookup::class, Mockery::mock(HistoricalStripePaymentLookup::class)
        ->shouldReceive('retrieve')->with($payment->stripe_payment_intent_id)
        ->once()->andReturn($intent)->getMock());
}

test('confirming a campaign address sends one financially verified historical Stripe receipt', function () {
    config()->set('payment_receipts.auto_send', true);
    Notification::fake();
    Mail::fake();

    [$registration, $payment] = verifiedFlowFixture();
    $user = $registration->pilot->user;
    $this->artisan('payment-receipts:request-verification --commit')->assertSuccessful();
    expect(PaymentReceipt::count())->toBe(0);
    fakeVerifiedFlowStripe($payment, $registration);

    $user->forceFill(['email_verified_at' => now()])->save();
    event(new Verified($user));
    event(new Verified($user));

    expect(PaymentReceipt::count())->toBe(1)
        ->and(PaymentReceipt::first()->email_status)->toBe('sent');
    Mail::assertSent(PaymentConfirmed::class, 1);
});

test('a pilot signed verification link releases historical Stripe receipts without a campaign', function () {
    config()->set('payment_receipts.auto_send', true);
    Mail::fake();

    [$registration, $payment] = verifiedFlowFixture();
    $user = $registration->pilot->user;
    config()->set('payment_receipts.historical_delivery', [
        'mode' => 'pilot',
        'pilot_user_id' => $user->id,
        'pilot_payment_id' => $payment->id,
    ]);
    fakeVerifiedFlowStripe($payment, $registration);
    $service = app(SendVerifiedHistoricalStripeReceipts::class);

    // Neither an administrative service call nor a synthetic event proves a
    // newly verified address without the signed verification request.
    expect($service->forUser($user)['examined'])->toBe(0);
    $user->forceFill(['email_verified_at' => now()])->save();
    event(new Verified($user));
    expect($service->forUser($user)['examined'])->toBe(0)
        ->and(PaymentReceipt::count())->toBe(0);
    $user->forceFill(['email_verified_at' => null])->save();

    $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
        'id' => $user->id,
        'hash' => sha1($user->email),
    ]);
    $this->actingAs($user)->get($url)->assertRedirect();
    $this->get($url)->assertRedirect();

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue()
        ->and(PaymentReceipt::count())->toBe(1)
        ->and(PaymentReceipt::first()->email_status)->toBe('sent')
        ->and($service->forUser($user)['examined'])->toBe(0);
    Mail::assertSent(PaymentConfirmed::class, 1);
});

test('historical off blocks a valid signed link before Stripe lookup and preserves verification', function () {
    config()->set('payment_receipts.auto_send', true);
    config()->set('payment_receipts.historical_delivery.mode', 'off');
    Mail::fake();
    [$registration, $payment] = verifiedFlowFixture();
    $user = $registration->pilot->user;
    app()->instance(HistoricalStripePaymentLookup::class, Mockery::mock(HistoricalStripePaymentLookup::class)
        ->shouldNotReceive('retrieve')->getMock());

    $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
        'id' => $user->id,
        'hash' => sha1($user->email),
    ]);
    $this->actingAs($user)->get($url)->assertRedirect();

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue()
        ->and(PaymentReceipt::count())->toBe(0)
        ->and(app(SendVerifiedHistoricalStripeReceipts::class)->forUser($user)['examined'])->toBe(0);
    Mail::assertNothingOutgoing();
});

test('pilot signed link checks and mails only one of four historical Stripe payments', function () {
    config()->set('payment_receipts.auto_send', true);
    Mail::fake();
    [$registration, $selected] = verifiedFlowFixture();
    $user = $registration->pilot->user;
    for ($index = 1; $index <= 3; $index++) {
        $otherRegistration = RaceRegistration::factory()->create(['pilot_id' => $registration->pilot_id]);
        Payment::create([
            'race_registration_id' => $otherRegistration->id,
            'user_id' => $user->id,
            'amount' => '50.00',
            'amount_cents' => 5000,
            'currency' => 'EUR',
            'method' => 'stripe',
            'status' => 'paid',
            'stripe_payment_intent_id' => 'pi_verified_other_'.$index,
            'paid_at' => now()->subDays(10),
        ]);
    }
    config()->set('payment_receipts.historical_delivery', [
        'mode' => 'pilot',
        'pilot_user_id' => $user->id,
        'pilot_payment_id' => $selected->id,
    ]);
    fakeVerifiedFlowStripe($selected, $registration);

    $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
        'id' => $user->id,
        'hash' => sha1($user->email),
    ]);
    $this->actingAs($user)->get($url)->assertRedirect();
    $this->get($url)->assertRedirect();

    expect(PaymentReceipt::count())->toBe(1)
        ->and(PaymentReceipt::first()->payment_id)->toBe($selected->id)
        ->and(app(SendVerifiedHistoricalStripeReceipts::class)->forUser($user)['examined'])->toBe(0);
    Mail::assertSent(PaymentConfirmed::class, 1);
});

test('pilot mode rejects a different account and an unconfigured payment', function () {
    config()->set('payment_receipts.auto_send', true);
    Mail::fake();
    [$registration, $payment] = verifiedFlowFixture();
    $user = $registration->pilot->user;
    [$otherRegistration, $otherPayment] = verifiedFlowFixture();
    $otherUser = $otherRegistration->pilot->user;
    config()->set('payment_receipts.historical_delivery', [
        'mode' => 'pilot',
        'pilot_user_id' => $user->id,
        'pilot_payment_id' => $otherPayment->id,
    ]);
    $user->forceFill(['email_verified_at' => now()])->save();
    $otherUser->forceFill(['email_verified_at' => now()])->save();

    expect(app(SendVerifiedHistoricalStripeReceipts::class)->forUser($user)['examined'])->toBe(0)
        ->and(app(SendVerifiedHistoricalStripeReceipts::class)->forUser($otherUser)['examined'])->toBe(0)
        ->and(PaymentReceipt::count())->toBe(0);
    Mail::assertNothingOutgoing();
});

test('all mode requires an accepted campaign for a signed profile verification', function () {
    config()->set('payment_receipts.auto_send', true);
    Mail::fake();
    [$registration, $payment] = verifiedFlowFixture();
    $user = $registration->pilot->user;
    app()->instance(HistoricalStripePaymentLookup::class, Mockery::mock(HistoricalStripePaymentLookup::class)
        ->shouldNotReceive('retrieve')->getMock());

    $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
        'id' => $user->id,
        'hash' => sha1($user->email),
    ]);
    $this->actingAs($user)->get($url)->assertRedirect();

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue()
        ->and(PaymentReceipt::count())->toBe(0);
    Mail::assertNothingOutgoing();
});

test('a prior pilot invitation never authorizes all historical receipts after a mode change', function () {
    config()->set('payment_receipts.auto_send', true);
    Mail::fake();
    [$registration, $payment] = verifiedFlowFixture();
    $user = $registration->pilot->user;
    DB::table('payment_receipt_verification_requests')->insert([
        'user_id' => $user->id,
        'email_hash' => HistoricalReceiptEmailHash::of($user->email),
        'status' => 'pilot_sent',
        'claimed_at' => now(),
        'sent_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    app()->instance(HistoricalStripePaymentLookup::class, Mockery::mock(HistoricalStripePaymentLookup::class)
        ->shouldNotReceive('retrieve')->getMock());

    $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
        'id' => $user->id,
        'hash' => sha1($user->email),
    ]);
    $this->actingAs($user)->get($url)->assertRedirect();

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue()
        ->and(app(SendVerifiedHistoricalStripeReceipts::class)->forUser($user)['examined'])->toBe(0)
        ->and(PaymentReceipt::count())->toBe(0);
    Mail::assertNothingOutgoing();
});

test('a campaign recipient using the signed link receives one historical receipt', function () {
    config()->set('payment_receipts.auto_send', true);
    Notification::fake();
    Mail::fake();

    [$registration, $payment] = verifiedFlowFixture();
    $user = $registration->pilot->user;
    $this->artisan('payment-receipts:request-verification --commit')->assertSuccessful();
    fakeVerifiedFlowStripe($payment, $registration);
    $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
        'id' => $user->id,
        'hash' => sha1($user->email),
    ]);

    $this->actingAs($user)->get($url)->assertRedirect();
    $this->get($url)->assertRedirect();

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue()
        ->and(PaymentReceipt::count())->toBe(1)
        ->and(PaymentReceipt::first()->email_status)->toBe('sent');
    Mail::assertSent(PaymentConfirmed::class, 1);
});

test('signed verification never retries an uncertain historical SMTP send', function () {
    config()->set('payment_receipts.auto_send', true);
    Mail::fake();

    [$registration, $payment] = verifiedFlowFixture();
    $user = $registration->pilot->user;
    $receipt = app(IssuePaymentReceipt::class)->issue($payment, historical: true);
    $receipt->update(['email_status' => 'sending']);
    $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
        'id' => $user->id,
        'hash' => sha1($user->email),
    ]);

    $this->actingAs($user)->get($url)->assertRedirect();

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue()
        ->and($receipt->fresh()->email_status)->toBe('sending');
    Mail::assertNothingOutgoing();
});

test('a temporary receipt lookup failure does not turn successful email verification into an error', function () {
    config()->set('payment_receipts.auto_send', true);
    $registration = RaceRegistration::factory()->create();
    $user = $registration->pilot->user;
    $user->forceFill(['email_verified_at' => now()])->save();

    Schema::dropIfExists('payment_receipts');

    event(new Verified($user));

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});
