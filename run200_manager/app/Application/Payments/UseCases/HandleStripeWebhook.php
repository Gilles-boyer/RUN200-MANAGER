<?php

declare(strict_types=1);

namespace App\Application\Payments\UseCases;

use App\Infrastructure\Payments\Stripe\StripePaymentService;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Spatie\Activitylog\Facades\Activity;

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

        $payment = Payment::where('stripe_session_id', $sessionId)->first();

        if (! $payment) {
            throw new InvalidArgumentException(
                "Payment not found for session: {$sessionId}"
            );
        }

        // Already processed
        if ($payment->status === 'paid') {
            return $payment;
        }

        return DB::transaction(function () use ($payment, $paymentIntentId, $sessionData, $eventId) {
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

                // Dispatch event for email notification
                \App\Events\PaymentConfirmed::dispatch($payment);

                Activity::performedOn($registration)
                    ->withProperties([
                        'payment_id' => $payment->id,
                        'old_status' => 'PENDING_PAYMENT',
                        'new_status' => 'PENDING_VALIDATION',
                    ])
                    ->log('registration.payment_received');
            }

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
        if (in_array($payment->status, ['paid', 'cancelled'])) {
            return $payment;
        }

        return DB::transaction(function () use ($payment) {
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
        $amountReceived = $paymentIntentData['amount_received'] ?? $amount;

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
                $sessions = \Stripe\Checkout\Session::all([
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

        // Strategy 4: Find pending payment by amount (last resort - risky)
        if (! $payment && $amountReceived > 0) {
            $payment = Payment::where('amount_cents', $amountReceived)
                ->where('status', 'pending')
                ->where('method', 'stripe')
                ->whereNull('stripe_payment_intent_id')
                ->where('created_at', '>=', now()->subHours(24))
                ->latest()
                ->first();

            if ($payment) {
                Log::warning('Found payment by amount matching (fallback strategy)', [
                    'payment_id' => $payment->id,
                    'amount_cents' => $amountReceived,
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
        if ($payment->status === 'paid') {
            Log::info('Payment already marked as paid', ['payment_id' => $payment->id]);
            return $payment;
        }

        return DB::transaction(function () use ($payment, $paymentIntentId, $paymentIntentData, $eventId) {
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

                // Dispatch event for email notification
                \App\Events\PaymentConfirmed::dispatch($payment);

                Activity::performedOn($registration)
                    ->withProperties([
                        'payment_id' => $payment->id,
                        'old_status' => 'PENDING_PAYMENT',
                        'new_status' => 'PENDING_VALIDATION',
                    ])
                    ->log('registration.payment_received');
            }

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
                $sessions = \Stripe\Checkout\Session::all([
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

        return DB::transaction(function () use ($payment, $paymentIntentData, $paymentIntentId) {
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

        if (! $paymentIntentId) {
            return null;
        }

        $payment = Payment::where('stripe_payment_intent_id', $paymentIntentId)->first();

        if (! $payment) {
            return null;
        }

        return DB::transaction(function () use ($payment, $chargeData) {
            $amountRefunded = $chargeData['amount_refunded'] ?? 0;
            $amountTotal = $chargeData['amount'] ?? $payment->amount_cents;

            $isFullRefund = $amountRefunded >= $amountTotal;

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
}
