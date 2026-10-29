<?php

declare(strict_types=1);

namespace App\Application\Payments\UseCases;

use App\Infrastructure\Payments\Stripe\StripePaymentService;
use App\Models\Payment;
use App\Models\RaceRegistration;
use Illuminate\Support\Facades\Log;

/**
 * Reconcile a pilot's existing Checkout attempt before offering another one.
 * Stripe is queried before the webhook handler opens its short DB transaction.
 */
final class ReconcileStripeCheckout
{
    public function __construct(
        private StripePaymentService $stripeService,
        private HandleStripeWebhook $webhookHandler,
    ) {}

    /** @return array{state: 'open'|'expired'|'paid'|'confirmation_pending'|'waiting', url?: string} */
    public function execute(Payment $payment, RaceRegistration $registration): array
    {
        $payment = $payment->fresh();

        if (! $payment || $payment->race_registration_id !== $registration->id) {
            return $this->waiting($payment, 'registration_mismatch');
        }

        if ($payment->isPaid()) {
            return ['state' => 'paid'];
        }

        if (! in_array($payment->status->value, ['pending', 'processing'], true)
            || ! is_string($payment->stripe_session_id)
            || $payment->stripe_session_id === '') {
            return $this->waiting($payment, 'attempt_unavailable');
        }

        // The database does not yet enforce uniqueness of stripe_session_id.
        if (Payment::where('stripe_session_id', $payment->stripe_session_id)->count() !== 1) {
            return $this->waiting($payment, 'session_not_unique');
        }

        $session = $this->stripeService->retrieveSession($payment->stripe_session_id);

        if ($session->id !== $payment->stripe_session_id
            || ! is_int($session->amount_total)
            || $session->amount_total !== $payment->amount_cents
            || ! is_string($session->currency)
            || strtoupper($session->currency) !== strtoupper($payment->currency)) {
            return $this->waiting($payment, 'stripe_data_mismatch');
        }

        if ($session->status === 'open' && $session->payment_status === 'unpaid') {
            if ($registration->payments()->where('status', 'paid')->exists()) {
                return ['state' => 'paid'];
            }

            return is_string($session->url) && str_starts_with($session->url, 'https://')
                ? ['state' => 'open', 'url' => $session->url]
                : $this->waiting($payment, 'session_url_unavailable');
        }

        if ($session->status === 'expired' && $session->payment_status === 'unpaid'
            && ! $session->payment_intent && $payment->stripe_payment_intent_id === null) {
            $closed = $this->webhookHandler->handleCheckoutExpired(['id' => $session->id]);

            return $closed->isCancelled() ? ['state' => 'expired'] : $this->waiting($closed, 'expiry_not_confirmed');
        }

        if ($session->status === 'complete' && $session->payment_status === 'paid'
            && is_string($session->payment_intent) && $session->payment_intent !== ''
            && ($payment->stripe_payment_intent_id === null
                || $payment->stripe_payment_intent_id === $session->payment_intent)
            && ! $registration->payments()->where('status', 'paid')->whereKeyNot($payment->id)->exists()) {
            // Do not use the webhook handler for historical reconciliation: it
            // would set paid_at=now() instead of Stripe's actual payment date.
            return ['state' => 'confirmation_pending'];
        }

        return $this->waiting($payment, 'stripe_payment_in_progress_or_ambiguous');
    }

    /** @return array{state: 'waiting'} */
    private function waiting(?Payment $payment, string $reason): array
    {
        Log::warning('Stripe Checkout reprise en attente de rapprochement', [
            'payment_id' => $payment?->id,
            'reason' => $reason,
        ]);

        return ['state' => 'waiting'];
    }
}
