<?php

declare(strict_types=1);

namespace App\Application\Payments\UseCases;

use App\Infrastructure\Payments\Stripe\StripePaymentService;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Spatie\Activitylog\Facades\Activity;

/**
 * Use case for refunding a Stripe payment.
 */
final class RefundStripePayment
{
    public function __construct(
        private StripePaymentService $stripeService
    ) {}

    /**
     * Execute the refund.
     *
     * @param  Payment  $payment  The payment to refund
     * @param  User  $user  The user initiating the refund
     * @param  int|null  $amountCents  Amount to refund in cents (null = full refund)
     * @param  string|null  $reason  Reason for the refund
     *
     * @throws InvalidArgumentException
     */
    public function execute(
        Payment $payment,
        User $user,
        ?int $amountCents = null,
        ?string $reason = null
    ): Payment {
        // Validate payment can be refunded
        if (! in_array($payment->status, ['paid', 'partially_refunded'])) {
            throw new InvalidArgumentException(
                'Seuls les paiements effectués peuvent être remboursés.'
            );
        }

        if ($payment->method !== 'stripe') {
            throw new InvalidArgumentException(
                'Ce paiement n\'est pas un paiement Stripe.'
            );
        }

        if (! $payment->stripe_payment_intent_id) {
            throw new InvalidArgumentException(
                'Impossible de rembourser ce paiement (pas de payment intent).'
            );
        }

        // Calculate max refundable amount
        $alreadyRefunded = $payment->refund_amount_cents ?? 0;
        $maxRefundable = $payment->amount_cents - $alreadyRefunded;

        if ($amountCents !== null && $amountCents > $maxRefundable) {
            throw new InvalidArgumentException(
                sprintf(
                    'Le montant de remboursement (%s) dépasse le montant remboursable (%s).',
                    StripePaymentService::formatAmount($amountCents),
                    StripePaymentService::formatAmount($maxRefundable)
                )
            );
        }

        return DB::transaction(function () use ($payment, $user, $amountCents, $reason, $alreadyRefunded) {
            // Process refund via Stripe
            $refund = $this->stripeService->refund(
                $payment->stripe_payment_intent_id,
                $amountCents,
                $reason
            );

            $totalRefunded = $alreadyRefunded + ($amountCents ?? $payment->amount_cents);
            $isFullRefund = $totalRefunded >= $payment->amount_cents;

            // Update payment record
            $payment->update([
                'status' => $isFullRefund ? 'refunded' : 'partially_refunded',
                'refunded_at' => now(),
                'refund_amount_cents' => $totalRefunded,
                'metadata' => array_merge($payment->metadata ?? [], [
                    'refunds' => array_merge($payment->metadata['refunds'] ?? [], [
                        [
                            'refund_id' => $refund->id,
                            'amount_cents' => $amountCents ?? ($payment->amount_cents - $alreadyRefunded),
                            'reason' => $reason,
                            'refunded_at' => now()->toISOString(),
                            'refunded_by' => $user->id,
                        ],
                    ]),
                ]),
            ]);

            // Log activity
            Activity::performedOn($payment)
                ->causedBy($user)
                ->withProperties([
                    'registration_id' => $payment->race_registration_id,
                    'refund_amount_cents' => $amountCents ?? ($payment->amount_cents - $alreadyRefunded),
                    'reason' => $reason,
                    'is_full_refund' => $isFullRefund,
                    'stripe_refund_id' => $refund->id,
                ])
                ->log('payment.stripe.refund_processed');

            return $payment->fresh();
        });
    }
}
