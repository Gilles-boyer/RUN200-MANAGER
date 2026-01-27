<?php

declare(strict_types=1);

namespace App\Application\Payments\UseCases;

use App\Infrastructure\Payments\Stripe\StripePaymentService;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
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
     * Handle payment_intent.payment_failed event.
     */
    public function handlePaymentFailed(array $paymentIntentData): ?Payment
    {
        $paymentIntentId = $paymentIntentData['id'];

        $payment = Payment::where('stripe_payment_intent_id', $paymentIntentId)->first();

        if (! $payment) {
            // Try to find by metadata if available
            return null;
        }

        return DB::transaction(function () use ($payment, $paymentIntentData) {
            $lastError = $paymentIntentData['last_payment_error'] ?? null;

            $payment->update([
                'status' => 'failed',
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
