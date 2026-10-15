<?php

declare(strict_types=1);

namespace App\Application\Payments\UseCases;

use App\Events\PaymentConfirmed;
use App\Infrastructure\Payments\Stripe\StripePaymentService;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Spatie\Activitylog\Facades\Activity;
use Stripe\Checkout\Session;

/**
 * Use case for handling Stripe webhook events.
 */
final class HandleStripeWebhook
{
    public function __construct(
        private StripePaymentService $stripeService
    ) {}

    /**
     * Handle checkout.session.completed event.
     */
    public function handleCheckoutCompleted(array $sessionData, ?string $eventId = null): Payment
    {
        $sessionId = $sessionData['id'];
        $paymentIntentId = $sessionData['payment_intent'] ?? null;
        $amountTotal = $sessionData['amount_total'] ?? null;
        $currency = $sessionData['currency'] ?? null;

        $payment = Payment::where('stripe_session_id', $sessionId)->first();

        if (! $payment) {
            throw new InvalidArgumentException(
                "Payment not found for session: {$sessionId}"
            );
        }

        if (($sessionData['payment_status'] ?? null) !== 'paid'
            || ! is_int($amountTotal)
            || $amountTotal <= 0
            || $amountTotal !== $payment->amount_cents
            || ! is_string($currency)
            || preg_match('/^[A-Za-z]{3}$/', $currency) !== 1
            || strtoupper($currency) !== $payment->currency
            || ! is_string($paymentIntentId)
            || $paymentIntentId === ''
            || ($payment->stripe_payment_intent_id !== null && $payment->stripe_payment_intent_id !== $paymentIntentId)) {
            Log::warning('Checkout session is not a confirmed payment or does not match the local amount', [
                'session_id' => $sessionId,
                'payment_id' => $payment->id,
            ]);

            return $payment;
        }

        // Already processed
        if ($this->isSettled($payment)) {
            return $payment;
        }

        return DB::transaction(function () use ($payment, $paymentIntentId, $amountTotal, $currency, $sessionData, $eventId) {
            $payment = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            if ($this->isSettled($payment)) {
                return $payment;
            }
            if ($payment->amount_cents !== $amountTotal
                || $payment->currency !== strtoupper($currency)
                || ($payment->stripe_payment_intent_id !== null && $payment->stripe_payment_intent_id !== $paymentIntentId)) {
                Log::warning('Checkout session no longer matches the local payment', [
                    'session_id' => $sessionData['id'],
                    'payment_id' => $payment->id,
                ]);

                return $payment;
            }

            $payment->update([
                'status' => 'paid',
                'stripe_payment_intent_id' => $paymentIntentId,
                'stripe_customer_id' => $sessionData['customer'] ?? null,
                'stripe_event_id' => $eventId,
                'paid_at' => now(),
                'metadata' => array_merge($payment->metadata ?? [], [
                    'completed_at' => now()->toISOString(),
                    'payment_status' => $sessionData['payment_status'] ?? null,
                ]),
            ]);

            // Update registration status from PENDING_PAYMENT to PENDING_VALIDATION
            $registration = $payment->registration;
            if ($registration && $registration->status === 'PENDING_PAYMENT') {
                $registration->update(['status' => 'PENDING_VALIDATION']);

                Activity::performedOn($registration)
                    ->withProperties([
                        'payment_id' => $payment->id,
                        'old_status' => 'PENDING_PAYMENT',
                        'new_status' => 'PENDING_VALIDATION',
                    ])
                    ->log('registration.payment_received');
            }

            // A paid payment needs a confirmation even when the registration
            // was already accepted (for example, an on-site registration).
            PaymentConfirmed::dispatch($payment);

            // Log activity
            Activity::performedOn($payment)
                ->withProperties([
                    'registration_id' => $payment->race_registration_id,
                    'amount_cents' => $payment->amount_cents,
                    'payment_intent_id' => $paymentIntentId,
                ])
                ->log('payment.stripe.completed');

            return $payment->fresh();
        });
    }

    /**
     * Handle checkout.session.expired event.
     */
    public function handleCheckoutExpired(array $sessionData): Payment
    {
        $sessionId = $sessionData['id'];

        $payment = Payment::where('stripe_session_id', $sessionId)->first();

        if (! $payment) {
            throw new InvalidArgumentException(
                "Payment not found for session: {$sessionId}"
            );
        }

        // Already processed
        if ($this->isSettled($payment) || $payment->isCancelled()) {
            return $payment;
        }

        return DB::transaction(function () use ($payment) {
            $payment = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            if ($this->isSettled($payment) || $payment->isCancelled()) {
                return $payment;
            }

            $payment->update([
                'status' => 'cancelled',
                'failure_reason' => 'Session expirée',
                'metadata' => array_merge($payment->metadata ?? [], [
                    'expired_at' => now()->toISOString(),
                ]),
            ]);

            Activity::performedOn($payment)
                ->withProperties([
                    'registration_id' => $payment->race_registration_id,
                ])
                ->log('payment.stripe.expired');

            return $payment->fresh();
        });
    }

    /**
     * Handle payment_intent.succeeded event.
     *
     * This handles direct PaymentIntent completions that may not go through Checkout Session.
     * It tries multiple strategies to find the associated payment record.
     */
    public function handlePaymentIntentSucceeded(array $paymentIntentData, ?string $eventId = null): ?Payment
    {
        $paymentIntentId = $paymentIntentData['id'];
        $metadata = $paymentIntentData['metadata'] ?? [];
        $amount = $paymentIntentData['amount'] ?? 0;
        $amountReceived = $paymentIntentData['amount_received'] ?? null;
        $currency = $paymentIntentData['currency'] ?? null;

        if (! is_int($amountReceived) || $amountReceived <= 0 || ! is_string($currency) || preg_match('/^[A-Za-z]{3}$/', $currency) !== 1) {
            Log::warning('Payment intent is missing a positive received amount or currency', [
                'payment_intent_id' => $paymentIntentId,
            ]);

            return null;
        }

        Log::info('Processing payment_intent.succeeded', [
            'payment_intent_id' => $paymentIntentId,
            'amount' => $amount,
            'amount_received' => $amountReceived,
            'metadata' => $metadata,
            'latest_charge' => $paymentIntentData['latest_charge'] ?? null,
        ]);

        // Strategy 1: Find by payment_intent_id (already linked)
        $payment = Payment::where('stripe_payment_intent_id', $paymentIntentId)->first();

        // Strategy 2: Find by metadata registration_id if present
        if (! $payment && ! empty($metadata['registration_id'])) {
            $payment = Payment::where('race_registration_id', $metadata['registration_id'])
                ->where('status', 'pending')
                ->where('method', 'stripe')
                ->where('amount_cents', $amountReceived)
                ->where('currency', strtoupper($currency))
                ->where(fn ($query) => $query->whereNull('stripe_payment_intent_id')
                    ->orWhere('stripe_payment_intent_id', $paymentIntentId))
                ->latest()
                ->first();

            Log::info('Searching payment by metadata registration_id', [
                'registration_id' => $metadata['registration_id'],
                'found' => $payment ? true : false,
            ]);
        }

        // Strategy 3: Try to find via Stripe API - get session from payment_intent
        if (! $payment) {
            try {
                // Get all checkout sessions and find the one with this payment_intent
                $sessions = Session::all([
                    'payment_intent' => $paymentIntentId,
                    'limit' => 1,
                ]);

                if ($sessions->data && count($sessions->data) > 0) {
                    $sessionId = $sessions->data[0]->id;
                    $payment = Payment::where('stripe_session_id', $sessionId)->first();

                    Log::info('Found payment via Stripe session lookup', [
                        'session_id' => $sessionId,
                        'found' => $payment ? true : false,
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('Could not retrieve session from Stripe', [
                    'payment_intent_id' => $paymentIntentId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if (! $payment) {
            Log::warning('Payment not found for payment_intent.succeeded', [
                'payment_intent_id' => $paymentIntentId,
                'metadata' => $metadata,
                'amount' => $amount,
            ]);

            return null;
        }

        // Already processed
        if ($this->isSettled($payment)) {
            Log::info('Payment already settled', ['payment_id' => $payment->id]);

            return $payment;
        }

        if (($payment->stripe_payment_intent_id !== null && $payment->stripe_payment_intent_id !== $paymentIntentId)
            || $payment->amount_cents !== $amountReceived
            || strtoupper($currency) !== $payment->currency) {
            Log::warning('Payment intent does not match the local payment amount', [
                'payment_intent_id' => $paymentIntentId,
                'payment_id' => $payment->id,
            ]);

            return null;
        }

        return DB::transaction(function () use ($payment, $paymentIntentId, $amountReceived, $currency, $paymentIntentData, $eventId) {
            $payment = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            if ($this->isSettled($payment)) {
                return $payment;
            }
            if (($payment->stripe_payment_intent_id !== null && $payment->stripe_payment_intent_id !== $paymentIntentId)
                || $payment->amount_cents !== $amountReceived
                || $payment->currency !== strtoupper($currency)) {
                Log::warning('Payment intent no longer matches the local payment', [
                    'payment_intent_id' => $paymentIntentId,
                    'payment_id' => $payment->id,
                ]);

                return null;
            }

            $payment->update([
                'status' => 'paid',
                'stripe_payment_intent_id' => $paymentIntentId,
                'stripe_event_id' => $eventId,
                'paid_at' => now(),
                'metadata' => array_merge($payment->metadata ?? [], [
                    'completed_at' => now()->toISOString(),
                    'completed_via' => 'payment_intent.succeeded',
                    'payment_status' => $paymentIntentData['status'] ?? null,
                    'latest_charge' => $paymentIntentData['latest_charge'] ?? null,
                ]),
            ]);

            // Update registration status from PENDING_PAYMENT to PENDING_VALIDATION
            $registration = $payment->registration;
            if ($registration && $registration->status === 'PENDING_PAYMENT') {
                $registration->update(['status' => 'PENDING_VALIDATION']);

                Activity::performedOn($registration)
                    ->withProperties([
                        'payment_id' => $payment->id,
                        'old_status' => 'PENDING_PAYMENT',
                        'new_status' => 'PENDING_VALIDATION',
                    ])
                    ->log('registration.payment_received');
            }

            PaymentConfirmed::dispatch($payment);

            // Log activity
            Activity::performedOn($payment)
                ->withProperties([
                    'registration_id' => $payment->race_registration_id,
                    'amount_cents' => $payment->amount_cents,
                    'payment_intent_id' => $paymentIntentId,
                    'event_type' => 'payment_intent.succeeded',
                ])
                ->log('payment.stripe.completed');

            Log::info('Payment successfully marked as paid via payment_intent.succeeded', [
                'payment_id' => $payment->id,
                'registration_id' => $payment->race_registration_id,
            ]);

            return $payment->fresh();
        });
    }

    /**
     * Handle payment_intent.payment_failed event.
     */
    public function handlePaymentFailed(array $paymentIntentData): ?Payment
    {
        $paymentIntentId = $paymentIntentData['id'];
        $metadata = $paymentIntentData['metadata'] ?? [];

        Log::info('Processing payment_intent.payment_failed', [
            'payment_intent_id' => $paymentIntentId,
            'metadata' => $metadata,
        ]);

        // Strategy 1: Find by payment_intent_id
        $payment = Payment::where('stripe_payment_intent_id', $paymentIntentId)->first();

        // Strategy 2: Find by metadata registration_id
        if (! $payment && ! empty($metadata['registration_id'])) {
            $payment = Payment::where('race_registration_id', $metadata['registration_id'])
                ->where('status', 'pending')
                ->where('method', 'stripe')
                ->latest()
                ->first();
        }

        // Strategy 3: Try to find via Stripe session lookup
        if (! $payment) {
            try {
                $sessions = Session::all([
                    'payment_intent' => $paymentIntentId,
                    'limit' => 1,
                ]);

                if ($sessions->data && count($sessions->data) > 0) {
                    $sessionId = $sessions->data[0]->id;
                    $payment = Payment::where('stripe_session_id', $sessionId)->first();
                }
            } catch (\Exception $e) {
                Log::warning('Could not retrieve session from Stripe for failed payment', [
                    'payment_intent_id' => $paymentIntentId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if (! $payment) {
            Log::warning('Payment not found for payment_intent.payment_failed', [
                'payment_intent_id' => $paymentIntentId,
                'metadata' => $metadata,
            ]);

            return null;
        }

        if ($this->isSettled($payment)) {
            return $payment;
        }

        return DB::transaction(function () use ($payment, $paymentIntentData, $paymentIntentId) {
            $payment = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            if ($this->isSettled($payment)) {
                return $payment;
            }

            $lastError = $paymentIntentData['last_payment_error'] ?? null;

            $payment->update([
                'status' => 'failed',
                'stripe_payment_intent_id' => $paymentIntentId,
                'failure_reason' => $lastError['message'] ?? 'Paiement refusé',
                'metadata' => array_merge($payment->metadata ?? [], [
                    'failed_at' => now()->toISOString(),
                    'error_code' => $lastError['code'] ?? null,
                    'error_type' => $lastError['type'] ?? null,
                ]),
            ]);

            Activity::performedOn($payment)
                ->withProperties([
                    'registration_id' => $payment->race_registration_id,
                    'error' => $lastError['message'] ?? 'Unknown error',
                ])
                ->log('payment.stripe.failed');

            Log::info('Payment marked as failed', [
                'payment_id' => $payment->id,
                'registration_id' => $payment->race_registration_id,
            ]);

            return $payment->fresh();
        });
    }

    /**
     * Handle charge.refunded event.
     */
    public function handleRefund(array $chargeData): ?Payment
    {
        $paymentIntentId = $chargeData['payment_intent'] ?? null;

        if (! is_string($paymentIntentId) || $paymentIntentId === '') {
            return null;
        }

        $payment = Payment::where('stripe_payment_intent_id', $paymentIntentId)->first();

        if (! $payment) {
            return null;
        }

        return DB::transaction(function () use ($payment, $chargeData, $paymentIntentId) {
            $payment = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            $amountRefunded = $chargeData['amount_refunded'] ?? null;
            $amountTotal = $chargeData['amount'] ?? null;
            $currency = $chargeData['currency'] ?? null;

            if ($payment->stripe_payment_intent_id !== $paymentIntentId
                || ! is_int($amountTotal) || $amountTotal <= 0 || $amountTotal !== $payment->amount_cents
                || ! is_int($amountRefunded) || $amountRefunded <= 0 || $amountRefunded > $amountTotal
                || ! is_string($currency) || preg_match('/^[A-Za-z]{3}$/', $currency) !== 1
                || strtoupper($currency) !== $payment->currency) {
                Log::warning('Refund charge does not match the local payment amount or currency', [
                    'payment_id' => $payment->id,
                ]);

                return $payment;
            }

            // Stripe reports the cumulative refunded amount. Older and duplicate
            // events must never reduce that total or revert a full refund.
            if ($payment->isRefunded() || $amountRefunded <= ($payment->refund_amount_cents ?? 0)) {
                return $payment;
            }

            $isFullRefund = $amountRefunded === $payment->amount_cents;

            $payment->update([
                'status' => $isFullRefund ? 'refunded' : 'partially_refunded',
                'refunded_at' => now(),
                'refund_amount_cents' => $amountRefunded,
                'metadata' => array_merge($payment->metadata ?? [], [
                    'refunded_at' => now()->toISOString(),
                    'refund_amount_cents' => $amountRefunded,
                ]),
            ]);

            Activity::performedOn($payment)
                ->withProperties([
                    'registration_id' => $payment->race_registration_id,
                    'refund_amount_cents' => $amountRefunded,
                    'is_full_refund' => $isFullRefund,
                ])
                ->log('payment.stripe.refunded');

            return $payment->fresh();
        });
    }

    private function isSettled(Payment $payment): bool
    {
        return $payment->isPaid() || $payment->isPartiallyRefunded() || $payment->isRefunded();
    }
}
