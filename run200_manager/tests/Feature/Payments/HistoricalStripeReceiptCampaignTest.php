<?php

use App\Application\Payments\UseCases\HistoricalReceiptEmailHash;
use App\Application\Payments\UseCases\IssuePaymentReceipt;
use App\Application\Payments\UseCases\SendVerifiedHistoricalStripeReceipts;
use App\Infrastructure\Payments\Stripe\HistoricalStripePaymentLookup;
use App\Mail\PaymentConfirmed;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use App\Models\Pilot;
use App\Models\RaceRegistration;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Stripe\Charge;
use Stripe\PaymentIntent;

function historicalStripePayment(): Payment
{
    $registration = RaceRegistration::factory()->create();
    $registration->pilot->user->forceFill(['email_verified_at' => null])->save();

    return Payment::create([
        'race_registration_id' => $registration->id,
        'user_id' => $registration->pilot->user_id,
        'amount' => '50.00',
        'amount_cents' => 5000,
        'currency' => 'EUR',
        'method' => 'stripe',
        'status' => 'paid',
        'stripe_payment_intent_id' => 'pi_historical_test',
        'paid_at' => now()->subDays(10),
    ]);
}

function stubLiveStripePayment(Payment $payment, array $changes = [], ?Closure $duringLookup = null): void
{
    $charge = Charge::constructFrom(array_merge([
        'id' => 'ch_historical_test',
        'object' => 'charge',
        'payment_intent' => $payment->stripe_payment_intent_id,
        'livemode' => true,
        'paid' => true,
        'captured' => true,
        'disputed' => false,
        'amount' => 5000,
        'amount_refunded' => 0,
        'currency' => 'eur',
    ], $changes['charge'] ?? []));
    $intent = PaymentIntent::constructFrom(array_merge([
        'id' => $payment->stripe_payment_intent_id,
        'object' => 'payment_intent',
        'livemode' => true,
        'status' => 'succeeded',
        'amount' => 5000,
        'amount_received' => 5000,
        'currency' => 'eur',
        'metadata' => [
            'registration_id' => (string) $payment->race_registration_id,
            'pilot_id' => (string) $payment->registration->pilot_id,
        ],
        'latest_charge' => $charge,
    ], $changes['intent'] ?? []));
    app()->instance(HistoricalStripePaymentLookup::class, Mockery::mock(HistoricalStripePaymentLookup::class)
        ->shouldReceive('retrieve')->with($payment->stripe_payment_intent_id)
        ->andReturnUsing(function () use ($duringLookup, $intent): PaymentIntent {
            $duringLookup?->__invoke();

            return $intent;
        })->getMock());
}

function markHistoricalVerificationSent(Payment $payment): void
{
    $user = $payment->registration->pilot->user;
    DB::table('payment_receipt_verification_requests')->insert([
        'user_id' => $user->id,
        'email_hash' => HistoricalReceiptEmailHash::of($user->email),
        'status' => 'sent',
        'claimed_at' => now(),
        'sent_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

test('campaign previews then sends a single verification notice without a receipt', function () {
    Notification::fake();
    $payment = historicalStripePayment();
    $user = $payment->registration->pilot->user;

    $this->artisan('payment-receipts:request-verification')->assertSuccessful();
    expect(DB::table('payment_receipt_verification_requests')->count())->toBe(0);
    Notification::assertNothingSent();

    $this->artisan('payment-receipts:request-verification --commit')->assertSuccessful();
    $this->artisan('payment-receipts:request-verification --commit')->assertSuccessful();
    expect(DB::table('payment_receipt_verification_requests')->where('user_id', $user->id)->value('status'))->toBe('sent')
        ->and(PaymentReceipt::count())->toBe(0);
    Notification::assertSentTo($user, VerifyEmail::class, 1);
    $mail = (new VerifyEmail)->toMail($user);
    expect($mail->subject)->toContain('Confirmez votre adresse e-mail')
        ->and($mail->actionText)->toBe('Confirmer mon adresse e-mail')
        ->and($mail->attachments)->toBe([]);
});

test('verification completed during notice delivery still triggers checked historical receipts', function () {
    Mail::fake();
    $payment = historicalStripePayment();
    $user = $payment->registration->pilot->user;
    stubLiveStripePayment($payment);
    $dispatcher = Mockery::mock(Dispatcher::class);
    $dispatcher->shouldReceive('send')->once()->andReturnUsing(function ($notifiable, $notification): void {
        expect($notification)->toBeInstanceOf(VerifyEmail::class);
        $notifiable->forceFill(['email_verified_at' => now()])->save();
    });
    app()->instance(Dispatcher::class, $dispatcher);

    $this->artisan('payment-receipts:request-verification --commit')->assertSuccessful();
    expect(DB::table('payment_receipt_verification_requests')->where('user_id', $user->id)->value('status'))->toBe('sent')
        ->and(PaymentReceipt::first()->email_status)->toBe('sent');
    Mail::assertSent(PaymentConfirmed::class, 1);
});

test('verified account receives a historical Stripe receipt once after live cross-check', function () {
    Mail::fake();
    $payment = historicalStripePayment();
    markHistoricalVerificationSent($payment);
    $user = $payment->registration->pilot->user;
    $user->forceFill(['email_verified_at' => now()])->save();
    stubLiveStripePayment($payment);

    $service = app(SendVerifiedHistoricalStripeReceipts::class);
    expect($service->forUser($user)['sent'])->toBe(1)
        ->and($service->forUser($user)['already_sent'])->toBe(1)
        ->and(PaymentReceipt::count())->toBe(1)
        ->and(PaymentReceipt::first()->email_status)->toBe('sent');
    Mail::assertSent(PaymentConfirmed::class, 1);
});

test('a Stripe mismatch, changed email, or uncertain receipt is held without a second mail', function () {
    Mail::fake();
    $payment = historicalStripePayment();
    markHistoricalVerificationSent($payment);
    $user = $payment->registration->pilot->user;
    $user->forceFill(['email_verified_at' => now()])->save();
    stubLiveStripePayment($payment, ['intent' => ['amount' => 7500]]);

    expect(app(SendVerifiedHistoricalStripeReceipts::class)->forUser($user)['held'])->toBe(1)
        ->and(PaymentReceipt::count())->toBe(0);
    Mail::assertNothingOutgoing();

    $user->forceFill(['email' => 'changed@example.test'])->save();
    expect(app(SendVerifiedHistoricalStripeReceipts::class)->forUser($user)['examined'])->toBe(0);
    Mail::assertNothingOutgoing();
});

test('a campaign claim with uncertain SMTP outcome is never retried automatically', function () {
    Notification::fake();
    $payment = historicalStripePayment();
    $user = $payment->registration->pilot->user;
    DB::table('payment_receipt_verification_requests')->insert([
        'user_id' => $user->id,
        'email_hash' => HistoricalReceiptEmailHash::of($user->email),
        'status' => 'sending',
        'claimed_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->artisan('payment-receipts:request-verification --commit')->assertSuccessful();
    Notification::assertNothingSent();
    expect(DB::table('payment_receipt_verification_requests')->where('user_id', $user->id)->value('status'))->toBe('sending');
});

test('a changed account address gets its own verification but never inherits the old address proof', function () {
    Notification::fake();
    Mail::fake();
    $payment = historicalStripePayment();
    $user = $payment->registration->pilot->user;
    $this->artisan('payment-receipts:request-verification --commit')->assertSuccessful();

    $user->forceFill(['email' => 'new-address@example.test'])->save();
    $this->artisan('payment-receipts:request-verification --commit')->assertSuccessful();
    expect(DB::table('payment_receipt_verification_requests')->where('user_id', $user->id)->count())->toBe(2);
    Notification::assertSentTo($user, VerifyEmail::class, 2);

    $user->forceFill(['email' => 'third-address@example.test', 'email_verified_at' => now()])->save();
    expect(app(SendVerifiedHistoricalStripeReceipts::class)->forUser($user)['examined'])->toBe(0);
    Mail::assertNothingOutgoing();
});

test('an existing uncertain receipt and a non Stripe payment are excluded from automatic historical mail', function () {
    Mail::fake();
    $payment = historicalStripePayment();
    markHistoricalVerificationSent($payment);
    $user = $payment->registration->pilot->user;
    $user->forceFill(['email_verified_at' => now()])->save();
    stubLiveStripePayment($payment);
    $receipt = app(IssuePaymentReceipt::class)->issue($payment, historical: true);
    $receipt->update(['email_status' => 'sending']);
    Payment::create([
        'race_registration_id' => $payment->race_registration_id,
        'user_id' => $user->id,
        'amount' => '20.00',
        'amount_cents' => 2000,
        'currency' => 'EUR',
        'method' => 'manual',
        'status' => 'paid',
        'paid_at' => now()->subDay(),
    ]);

    expect(app(SendVerifiedHistoricalStripeReceipts::class)->forUser($user))
        ->toBe(['examined' => 1, 'sent' => 0, 'held' => 1, 'already_sent' => 0])
        ->and(PaymentReceipt::count())->toBe(1);
    Mail::assertNothingOutgoing();
});

test('live Stripe identity, capture, dispute and refund checks fail closed', function () {
    Mail::fake();
    $payment = historicalStripePayment();
    markHistoricalVerificationSent($payment);
    $user = $payment->registration->pilot->user;
    $user->forceFill(['email_verified_at' => now()])->save();

    foreach ([
        ['intent' => ['livemode' => false]],
        ['intent' => ['metadata' => ['registration_id' => (string) $payment->race_registration_id, 'pilot_id' => '999999']]],
        ['charge' => ['captured' => false]],
        ['charge' => ['disputed' => true]],
        ['charge' => ['amount_refunded' => 1000]],
    ] as $mismatch) {
        stubLiveStripePayment($payment, $mismatch);
        expect(app(SendVerifiedHistoricalStripeReceipts::class)->forUser($user)['held'])->toBe(1);
    }
    expect(PaymentReceipt::count())->toBe(0);
    Mail::assertNothingOutgoing();
});

test('a duplicated local Stripe intent is held before issuing either receipt', function () {
    Mail::fake();
    $payment = historicalStripePayment();
    markHistoricalVerificationSent($payment);
    $user = $payment->registration->pilot->user;
    $user->forceFill(['email_verified_at' => now()])->save();
    Payment::create([
        'race_registration_id' => $payment->race_registration_id,
        'user_id' => $user->id,
        'amount' => '50.00',
        'amount_cents' => 5000,
        'currency' => 'EUR',
        'method' => 'stripe',
        'status' => 'paid',
        'stripe_payment_intent_id' => $payment->stripe_payment_intent_id,
        'paid_at' => now()->subDay(),
    ]);

    expect(app(SendVerifiedHistoricalStripeReceipts::class)->forUser($user)['held'])->toBe(2)
        ->and(PaymentReceipt::count())->toBe(0);
    Mail::assertNothingOutgoing();
});

test('a receipt snapshot made before a later refund is held for reconciliation', function () {
    Mail::fake();
    $payment = historicalStripePayment();
    markHistoricalVerificationSent($payment);
    $user = $payment->registration->pilot->user;
    $user->forceFill(['email_verified_at' => now()])->save();
    app(IssuePaymentReceipt::class)->issue($payment, historical: true);
    $payment->update(['status' => 'partially_refunded', 'refund_amount_cents' => 1000]);
    stubLiveStripePayment($payment, ['charge' => ['amount_refunded' => 1000]]);

    expect(app(SendVerifiedHistoricalStripeReceipts::class)->forUser($user)['held'])->toBe(1)
        ->and(PaymentReceipt::first()->email_status)->toBe('not_requested');
    Mail::assertNothingOutgoing();
});

test('a Stripe payment with a different recorded payer is held', function () {
    Mail::fake();
    $payment = historicalStripePayment();
    markHistoricalVerificationSent($payment);
    $user = $payment->registration->pilot->user;
    $user->forceFill(['email_verified_at' => now()])->save();
    stubLiveStripePayment($payment);

    $payment->update(['user_id' => User::factory()->create()->id]);
    expect(app(SendVerifiedHistoricalStripeReceipts::class)->forUser($user)['held'])->toBe(1)
        ->and(PaymentReceipt::count())->toBe(0);
    Mail::assertNothingOutgoing();
});

test('pilot reassignment during Stripe lookup prevents receipt issue and delivery', function () {
    Mail::fake();
    $payment = historicalStripePayment();
    markHistoricalVerificationSent($payment);
    $user = $payment->registration->pilot->user;
    $user->forceFill(['email_verified_at' => now()])->save();
    $otherPilot = Pilot::factory()->create();
    stubLiveStripePayment($payment, [], function () use ($payment, $otherPilot): void {
        $payment->registration->update(['pilot_id' => $otherPilot->id]);
    });

    expect(app(SendVerifiedHistoricalStripeReceipts::class)->forUser($user)['held'])->toBe(1)
        ->and(PaymentReceipt::count())->toBe(0);
    Mail::assertNothingOutgoing();
});

test('pilot account reassignment during Stripe lookup prevents receipt issue and delivery', function () {
    Mail::fake();
    $payment = historicalStripePayment();
    markHistoricalVerificationSent($payment);
    $user = $payment->registration->pilot->user;
    $user->forceFill(['email_verified_at' => now()])->save();
    $otherUser = User::factory()->create();
    stubLiveStripePayment($payment, [], function () use ($payment, $otherUser): void {
        $payment->registration->pilot->update(['user_id' => $otherUser->id]);
    });

    expect(app(SendVerifiedHistoricalStripeReceipts::class)->forUser($user)['held'])->toBe(1)
        ->and(PaymentReceipt::count())->toBe(0);
    Mail::assertNothingOutgoing();
});
