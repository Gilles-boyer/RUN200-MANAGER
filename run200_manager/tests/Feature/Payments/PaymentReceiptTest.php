<?php

use App\Application\Payments\UseCases\IssuePaymentReceipt;
use App\Application\Payments\UseCases\SendPaymentReceipt;
use App\Events\PaymentConfirmed;
use App\Infrastructure\Pdf\PaymentReceiptPdfService;
use App\Listeners\SendPaymentConfirmation;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use App\Models\Pilot;
use App\Models\RaceRegistration;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;

function paidReceiptPayment(array $attributes = []): Payment
{
    $registration = RaceRegistration::factory()->create();

    return Payment::create(array_merge([
        'race_registration_id' => $registration->id,
        'user_id' => $registration->pilot->user_id,
        'amount' => '50.00',
        'amount_cents' => 5000,
        'currency' => 'EUR',
        'method' => 'stripe',
        'status' => 'paid',
        'paid_at' => now()->subDay(),
    ], $attributes));
}

test('a historical receipt keeps one stable number and a frozen snapshot after a refund', function () {
    $payment = paidReceiptPayment([
        'amount' => '50.75',
        'amount_cents' => 0,
        'status' => 'refunded',
        'refund_amount_cents' => 5075,
    ]);

    $issuer = app(IssuePaymentReceipt::class);
    $receipt = $issuer->issue($payment, historical: true);
    $payment->registration->race->update(['name' => 'Course modifiée']);

    expect($receipt->amount_cents)->toBe(5075)
        ->and($receipt->number)->toMatch('/^REC-\d{4}-\d{8}$/')
        ->and($receipt->email_status)->toBe('not_requested')
        ->and($issuer->issue($payment)->id)->toBe($receipt->id)
        ->and($receipt->fresh()->race_name)->not->toBe('Course modifiée')
        ->and(PaymentReceipt::count())->toBe(1);

    expect(app(PaymentReceiptPdfService::class)->bytes($receipt))->toStartWith('%PDF-');
});

test('pending and zero amount payments cannot receive a receipt', function () {
    $pending = paidReceiptPayment(['status' => 'pending']);
    $zero = paidReceiptPayment(['amount' => '0.00', 'amount_cents' => 0]);
    $issuer = app(IssuePaymentReceipt::class);

    expect($pending->canHaveReceipt())->toBeFalse()
        ->and($zero->canHaveReceipt())->toBeFalse();

    expect(fn () => $issuer->issue($pending))->toThrow(InvalidArgumentException::class);
    expect(fn () => $issuer->issue($zero))->toThrow(InvalidArgumentException::class);
    expect(PaymentReceipt::count())->toBe(0);
});

test('conflicting recorded amounts are reported instead of issuing a receipt', function () {
    $payment = paidReceiptPayment(['amount' => '50.00', 'amount_cents' => 7500]);

    expect(fn () => app(IssuePaymentReceipt::class)->issue($payment))
        ->toThrow(InvalidArgumentException::class, 'incohérents');
    expect(PaymentReceipt::count())->toBe(0);
});

test('refunded payments with missing refund history are held for manual review', function () {
    $payment = paidReceiptPayment(['status' => 'refunded', 'refund_amount_cents' => null]);
    expect(fn () => app(IssuePaymentReceipt::class)->issue($payment))
        ->toThrow(InvalidArgumentException::class, 'statut et le montant remboursé');
    expect(PaymentReceipt::count())->toBe(0);
});

test('backfill audits then sends historical email once and holds an ambiguous delivery', function () {
    Mail::fake();
    config()->set('payment_receipts.auto_send', false);
    paidReceiptPayment(['amount' => '25.00', 'amount_cents' => 0]);

    $this->artisan('payment-receipts:backfill')->assertSuccessful();
    expect(PaymentReceipt::count())->toBe(0);

    $this->artisan('payment-receipts:backfill --commit')->assertSuccessful();
    $this->artisan('payment-receipts:backfill --commit')->assertSuccessful();
    expect(PaymentReceipt::count())->toBe(1)
        ->and(PaymentReceipt::first()->email_status)->toBe('sent');
    Mail::assertSent(App\Mail\PaymentConfirmed::class, 1);
    Mail::assertSent(App\Mail\PaymentConfirmed::class, fn ($mail) => $mail->historical
        && str_contains($mail->render(), 'Votre reçu de paiement')
        && ! str_contains($mail->render(), 'Rendez-vous vérifications'));

    PaymentReceipt::first()->update(['email_status' => 'sending']);
    $this->artisan('payment-receipts:backfill --commit')->assertFailed();
    Mail::assertSent(App\Mail\PaymentConfirmed::class, 1);
});

test('recipient failure before SMTP can be retried after its address is corrected', function () {
    Mail::fake();
    $payment = paidReceiptPayment();
    $receipt = app(IssuePaymentReceipt::class)->issue($payment, historical: true);
    $pilotUser = $payment->registration->pilot->user;
    $pilotUser->update(['email' => 'invalide']);

    expect(app(SendPaymentReceipt::class)->send($receipt, historical: true))->toBeFalse();
    expect($receipt->fresh()->email_status)->toBe('failed')
        ->and($receipt->fresh()->email_last_error)->toBe('recipient_unavailable');
    Mail::assertNothingOutgoing();

    $pilotUser->update(['email' => 'corrigee@example.test']);
    expect(app(SendPaymentReceipt::class)->send($receipt, historical: true))->toBeTrue();
    expect($receipt->fresh()->email_status)->toBe('sent');
    Mail::assertSent(App\Mail\PaymentConfirmed::class, 1);
});

test('historical audit flags inconsistent amounts and missing recipient without mailing', function () {
    Mail::fake();
    paidReceiptPayment(['amount' => '50.00', 'amount_cents' => 7500]);
    $missing = paidReceiptPayment();
    $missing->registration->pilot->user->update(['email' => 'invalide']);

    $this->artisan('payment-receipts:backfill')->assertFailed();
    $this->artisan('payment-receipts:backfill --commit')->assertFailed();
    expect(PaymentReceipt::count())->toBe(0);
    Mail::assertNothingOutgoing();
});

test('a corrected pilot address allows historical delivery once without using the old address', function () {
    Mail::fake();
    $payment = paidReceiptPayment();
    $pilotUser = $payment->registration->pilot->user;
    $pilotUser->update(['email' => 'adresse-invalide']);

    $this->artisan('payment-receipts:backfill --commit')->assertFailed();
    expect(PaymentReceipt::count())->toBe(0);
    Mail::assertNothingOutgoing();

    $pilotUser->update(['email' => 'corrigee@example.test']);
    $this->artisan('payment-receipts:backfill --commit')->assertSuccessful();
    $this->artisan('payment-receipts:backfill --commit')->assertSuccessful();
    expect(PaymentReceipt::count())->toBe(1)
        ->and(PaymentReceipt::first()->payer_email)->toBe('corrigee@example.test')
        ->and(PaymentReceipt::first()->delivery_email)->toBe('corrigee@example.test');
    Mail::assertSent(App\Mail\PaymentConfirmed::class, fn ($mail) => $mail->hasTo('corrigee@example.test'));
    Mail::assertSent(App\Mail\PaymentConfirmed::class, 1);
});

test('a previously issued receipt keeps its snapshot and uses the current valid address', function () {
    Mail::fake();
    $payment = paidReceiptPayment();
    $pilotUser = $payment->registration->pilot->user;
    $pilotUser->update(['email' => 'adresse-invalide']);
    $receipt = app(IssuePaymentReceipt::class)->issue($payment, historical: true);
    $pdfContentBefore = view('pdf.payment-receipt', ['receipt' => $receipt])->render();

    $this->artisan('payment-receipts:backfill --commit')->assertFailed();
    Mail::assertNothingOutgoing();

    $pilotUser->update(['email' => 'corrigee@example.test']);
    $this->artisan('payment-receipts:backfill --commit')->assertSuccessful();
    expect($receipt->fresh()->payer_email)->toBe('adresse-invalide')
        ->and($receipt->fresh()->delivery_email)->toBe('corrigee@example.test')
        ->and($receipt->fresh()->email_status)->toBe('sent')
        ->and(view('pdf.payment-receipt', ['receipt' => $receipt->fresh()])->render())->toBe($pdfContentBefore);
    Mail::assertSent(App\Mail\PaymentConfirmed::class, fn ($mail) => $mail->hasTo('corrigee@example.test'));
    Mail::assertSent(App\Mail\PaymentConfirmed::class, 1);
});

test('a valid old receipt address is never used after the pilot changes address', function () {
    Mail::fake();
    $payment = paidReceiptPayment();
    $receipt = app(IssuePaymentReceipt::class)->issue($payment, historical: true);
    $oldAddress = $receipt->payer_email;
    $pdfContentBefore = view('pdf.payment-receipt', ['receipt' => $receipt])->render();
    $payment->registration->pilot->user->update(['email' => 'adresse-actuelle@example.test']);

    $this->artisan('payment-receipts:backfill --commit')->assertSuccessful();
    expect($receipt->fresh()->payer_email)->toBe($oldAddress)
        ->and($receipt->fresh()->delivery_email)->toBe('adresse-actuelle@example.test')
        ->and(view('pdf.payment-receipt', ['receipt' => $receipt->fresh()])->render())->toBe($pdfContentBefore);
    Mail::assertSent(App\Mail\PaymentConfirmed::class, fn ($mail) => $mail->hasTo('adresse-actuelle@example.test') && ! $mail->hasTo($oldAddress));
    Mail::assertSent(App\Mail\PaymentConfirmed::class, 1);
});

test('backfill resumes from an explicit payment ID in controlled batches', function () {
    Mail::fake();
    $first = paidReceiptPayment();
    $second = paidReceiptPayment();

    $this->artisan('payment-receipts:backfill --commit --limit=1')
        ->expectsOutputToContain('lot suivant avec --after-id='.$first->id)
        ->assertSuccessful();
    expect(PaymentReceipt::count())->toBe(1);

    $this->artisan('payment-receipts:backfill --commit --limit=1 --after-id='.$first->id)
        ->expectsOutputToContain('lot suivant avec --after-id='.$second->id)
        ->assertSuccessful();
    expect(PaymentReceipt::count())->toBe(2);
    Mail::assertSent(App\Mail\PaymentConfirmed::class, 2);
});

test('historical stripe batch has the same eligible scope in simulation and commit', function () {
    Mail::fake();
    $cash = paidReceiptPayment(['method' => 'cash']);
    $verified = paidReceiptPayment();
    $unverified = paidReceiptPayment();
    $unverified->registration->pilot->user->update(['email_verified_at' => null]);
    $later = paidReceiptPayment();

    $options = '--stripe-only --verified-email-only --limit=2';
    $scope = '2 examiné(s), 0 reçu(s) existant(s), 2 donnée(s) valide(s), 1 éligible(s) à l’envoi';
    expect(Artisan::call('payment-receipts:backfill '.$options))->toBe(1);
    $simulationOutput = Artisan::output();
    expect($simulationOutput)->toContain($scope, '1 adresse(s) non vérifiée(s)', 'lot suivant avec --after-id='.$unverified->id);
    expect(PaymentReceipt::count())->toBe(0);
    Mail::assertNothingOutgoing();

    expect(Artisan::call('payment-receipts:backfill --commit '.$options))->toBe(1);
    $commitOutput = Artisan::output();
    expect($commitOutput)->toContain($scope, '1 adresse(s) non vérifiée(s)', 'lot suivant avec --after-id='.$unverified->id);
    expect(PaymentReceipt::count())->toBe(1)
        ->and(PaymentReceipt::first()->payment_id)->toBe($verified->id)
        ->and(PaymentReceipt::first()->email_status)->toBe('sent')
        ->and($cash->receipt()->exists())->toBeFalse()
        ->and($unverified->receipt()->exists())->toBeFalse()
        ->and($later->receipt()->exists())->toBeFalse();
    Mail::assertSent(App\Mail\PaymentConfirmed::class, 1);

    $this->artisan('payment-receipts:backfill --commit --stripe-only --verified-email-only --after-id='.$unverified->id)
        ->expectsOutputToContain('lot suivant avec --after-id='.$later->id)
        ->assertSuccessful();
    expect(PaymentReceipt::count())->toBe(2)
        ->and($cash->receipt()->exists())->toBeFalse()
        ->and($unverified->receipt()->exists())->toBeFalse();
    Mail::assertSent(App\Mail\PaymentConfirmed::class, 2);
});

test('verified stripe historical receipt is not sent twice on a repeated batch', function () {
    Mail::fake();
    $payment = paidReceiptPayment();
    $options = '--commit --stripe-only --verified-email-only';

    $this->artisan('payment-receipts:backfill '.$options)->assertSuccessful();
    $this->artisan('payment-receipts:backfill '.$options)
        ->expectsOutputToContain('1 déjà envoyé(s)')
        ->assertSuccessful();

    expect(PaymentReceipt::count())->toBe(1)
        ->and($payment->receipt()->first()->email_status)->toBe('sent');
    Mail::assertSent(App\Mail\PaymentConfirmed::class, 1);
});

test('verified recipient is checked again after receipt issuance before historical delivery', function () {
    Mail::fake();
    $payment = paidReceiptPayment();
    $pilotUser = $payment->registration->pilot->user;
    $event = 'eloquent.created: '.PaymentReceipt::class;
    Event::listen($event, function () use ($pilotUser): void {
        $pilotUser->update(['email_verified_at' => null]);
    });

    try {
        expect(Artisan::call('payment-receipts:backfill --commit --stripe-only --verified-email-only'))->toBe(1);
    } finally {
        Event::forget($event);
    }

    expect(PaymentReceipt::count())->toBe(1)
        ->and(PaymentReceipt::first()->email_status)->toBe('failed')
        ->and(PaymentReceipt::first()->email_last_error)->toBe('recipient_unverified_or_changed');
    Mail::assertNothingOutgoing();
});

test('uncertain SMTP outcome stays in sending and cannot be retried automatically', function () {
    $payment = paidReceiptPayment();
    $receipt = app(IssuePaymentReceipt::class)->issue($payment, historical: true);
    Mail::shouldReceive('to')->once()->andThrow(new RuntimeException('SMTP outcome unknown'));

    expect(app(SendPaymentReceipt::class)->send($receipt, historical: true))->toBeFalse()
        ->and($receipt->fresh()->email_status)->toBe('sending');
    expect(app(SendPaymentReceipt::class)->send($receipt, historical: true))->toBeFalse()
        ->and($receipt->fresh()->email_status)->toBe('sending');
});

test('failed sent-status persistence holds receipt for manual reconciliation', function () {
    Mail::fake();
    $payment = paidReceiptPayment();
    $receipt = app(IssuePaymentReceipt::class)->issue($payment, historical: true);
    $event = 'eloquent.updating: '.PaymentReceipt::class;
    Event::listen($event, function (PaymentReceipt $updating): void {
        if ($updating->email_status === 'sent') {
            throw new RuntimeException('status write failed');
        }
    });

    try {
        expect(app(SendPaymentReceipt::class)->send($receipt, historical: true))->toBeFalse();
    } finally {
        Event::forget($event);
    }

    expect($receipt->fresh()->email_status)->toBe('sending');
    expect(app(SendPaymentReceipt::class)->send($receipt, historical: true))->toBeFalse();
    Mail::assertSent(App\Mail\PaymentConfirmed::class, 1);
});

test('refund state is frozen in receipt and shown on historical PDF', function () {
    $payment = paidReceiptPayment([
        'status' => 'partially_refunded',
        'refund_amount_cents' => 2000,
    ]);
    $receipt = app(IssuePaymentReceipt::class)->issue($payment, historical: true);
    $payment->update(['status' => 'refunded', 'refund_amount_cents' => 5000]);

    expect($receipt->payment_status_at_issue)->toBe('partially_refunded')
        ->and($receipt->refund_amount_cents_at_issue)->toBe(2000)
        ->and($receipt->fresh()->refund_amount_cents_at_issue)->toBe(2000);
    $html = view('pdf.payment-receipt', ['receipt' => $receipt])->render();
    expect($html)->toContain('Pilote concerné', "Remboursé à l'émission", '20,00 EUR', '30,00 EUR', 'remboursement ultérieur')
        ->not->toContain('<span class="label">Payeur</span>');
});

test('legacy zero cent amount displays decimal fallback', function () {
    $payment = paidReceiptPayment(['amount' => '50.75', 'amount_cents' => 0]);
    expect($payment->formatted_amount)->toContain('50,75');
});

test('automatic confirmation is disabled until enabled, then sends once despite replay', function () {
    Mail::fake();
    $payment = paidReceiptPayment();
    $listener = app(SendPaymentConfirmation::class);

    config()->set('payment_receipts.auto_send', false);
    $listener->handle(new PaymentConfirmed($payment));
    expect(PaymentReceipt::count())->toBe(0);
    Mail::assertNothingOutgoing();

    config()->set('payment_receipts.auto_send', true);
    $listener->handle(new PaymentConfirmed($payment));
    $listener->handle(new PaymentConfirmed($payment));

    expect(PaymentReceipt::count())->toBe(1)
        ->and(PaymentReceipt::first()->email_status)->toBe('sent')
        ->and(PaymentReceipt::first()->email_sent_at)->not->toBeNull();
    Mail::assertSent(App\Mail\PaymentConfirmed::class, 1);
});

test('missing recipient is recorded without cancelling a confirmed payment', function () {
    Mail::fake();
    config()->set('payment_receipts.auto_send', true);
    $payment = paidReceiptPayment();
    $payment->registration->pilot->user->update(['email' => 'adresse-invalide']);

    app(SendPaymentConfirmation::class)->handle(new PaymentConfirmed($payment));

    expect($payment->fresh()->isPaid())->toBeTrue()
        ->and(PaymentReceipt::first()->email_status)->toBe('failed')
        ->and(PaymentReceipt::first()->email_last_error)->toBe('recipient_unavailable');
    Mail::assertNothingOutgoing();
});

test('receipt download allows its pilot and staff with payment view but rejects others', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $payment = paidReceiptPayment();
    $owner = $payment->registration->pilot->user;
    $other = User::factory()->create();
    Pilot::factory()->for($other)->create();
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');

    $this->actingAs($other)->get(route('payments.receipt', $payment))->assertForbidden();
    $this->actingAs($owner)->get(route('payments.receipt', $payment))->assertNotFound();
    expect(PaymentReceipt::count())->toBe(0);

    app(IssuePaymentReceipt::class)->issue($payment, historical: true);
    $this->actingAs($owner)->get(route('payments.receipt', $payment))
        ->assertOk()->assertHeader('Content-Type', 'application/pdf');
    $this->actingAs($staff)->get(route('payments.receipt', $payment))->assertOk();

    $pending = paidReceiptPayment(['status' => 'pending']);
    $this->actingAs($pending->registration->pilot->user)
        ->get(route('payments.receipt', $pending))->assertNotFound();

    expect(PaymentReceipt::count())->toBe(1);
});
