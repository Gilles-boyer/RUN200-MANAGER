<?php

declare(strict_types=1);

namespace App\Application\Payments\UseCases;

use App\Domain\Payment\Enums\PaymentMethod;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Infrastructure\Payments\Stripe\HistoricalStripePaymentLookup;
use App\Models\Payment;
use Stripe\Charge;
use Throwable;

final class VerifyHistoricalStripePayment
{
    public function __construct(private HistoricalStripePaymentLookup $stripe) {}

    /** Returns a short hold reason, or null when the live Stripe record matches. */
    public function holdReason(Payment $payment): ?string
    {
        if ($payment->method !== PaymentMethod::STRIPE
            || ! in_array($payment->status, [PaymentStatus::PAID, PaymentStatus::PARTIALLY_REFUNDED, PaymentStatus::REFUNDED], true)
            || ! is_string($payment->stripe_payment_intent_id)
            || ! preg_match('/^pi_[A-Za-z0-9_]+$/', $payment->stripe_payment_intent_id)
            || $payment->registration?->pilot === null
            || $payment->user_id === null
            || $payment->user_id !== $payment->registration->pilot->user_id
            || $payment->amount_cents === null
            || $payment->amount_cents <= 0) {
            return 'local_data_incomplete';
        }

        if (Payment::query()->where('stripe_payment_intent_id', $payment->stripe_payment_intent_id)
            ->whereKeyNot($payment->id)->exists()) {
            return 'duplicate_local_intent';
        }

        try {
            $intent = $this->stripe->retrieve($payment->stripe_payment_intent_id);
        } catch (Throwable) {
            return 'stripe_lookup_failed';
        }

        if ($intent->id !== $payment->stripe_payment_intent_id
            || $intent->livemode !== true || $intent->status !== 'succeeded'
            || $intent->amount !== $payment->amount_cents
            || $intent->amount_received !== $payment->amount_cents
            || strtoupper((string) $intent->currency) !== strtoupper((string) $payment->currency)
            || (string) ($intent->metadata['registration_id'] ?? '') !== (string) $payment->race_registration_id
            || (string) ($intent->metadata['pilot_id'] ?? '') !== (string) $payment->registration->pilot_id) {
            return 'stripe_intent_mismatch';
        }

        $charge = $intent->latest_charge;
        if (! $charge instanceof Charge
            || $charge->payment_intent !== $intent->id
            || $charge->livemode !== true
            || $charge->paid !== true
            || $charge->captured !== true
            || $charge->disputed !== false
            || $charge->amount !== $payment->amount_cents
            || strtoupper((string) $charge->currency) !== strtoupper((string) $payment->currency)) {
            return 'stripe_charge_mismatch';
        }

        $refunded = (int) ($payment->refund_amount_cents ?? 0);
        if (! is_int($charge->amount_refunded) || $charge->amount_refunded !== $refunded
            || ($payment->status === PaymentStatus::PAID && $refunded !== 0)
            || ($payment->status === PaymentStatus::PARTIALLY_REFUNDED && ($refunded <= 0 || $refunded >= $payment->amount_cents))
            || ($payment->status === PaymentStatus::REFUNDED && $refunded !== $payment->amount_cents)) {
            return 'stripe_refund_mismatch';
        }

        return null;
    }
}
