<?php

declare(strict_types=1);

namespace App\Application\Payments\UseCases;

use App\Domain\Payment\Enums\PaymentMethod;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Infrastructure\Payments\Stripe\StripePaymentService;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
        $request = DB::transaction(function () use ($payment, $amountCents): array {
            $locked = Payment::query()->lockForUpdate()->findOrFail($payment->id);

            if (! in_array($locked->status, [PaymentStatus::PAID, PaymentStatus::PARTIALLY_REFUNDED], true)) {
                throw new InvalidArgumentException('Seuls les paiements effectués peuvent être remboursés.');
            }
            if ($locked->method !== PaymentMethod::STRIPE || ! $locked->stripe_payment_intent_id) {
                throw new InvalidArgumentException('Ce paiement ne peut pas être remboursé par Stripe.');
            }

            $alreadyRefunded = $locked->refund_amount_cents ?? 0;
            $remaining = $locked->amount_cents - $alreadyRefunded;
            $requested = $amountCents ?? $remaining;
            if ($requested <= 0 || $requested > $remaining) {
                throw new InvalidArgumentException(sprintf(
                    'Le montant du remboursement doit être compris entre 0,01 € et %s.',
                    StripePaymentService::formatAmount(max(0, $remaining))
                ));
            }

            $metadata = $locked->metadata ?? [];
            $pending = $metadata['refund_pending'] ?? null;
            if (is_array($pending)) {
                if ((int) ($pending['amount_cents'] ?? 0) !== $requested) {
                    throw new InvalidArgumentException('Un autre remboursement est déjà en cours pour ce paiement.');
                }
                if (empty($pending['key']) || empty($pending['created_at'])) {
                    throw new InvalidArgumentException('Le remboursement en cours doit être rapproché avec Stripe.');
                }
                // Stripe may forget its idempotency key after 24 h: stop for reconciliation.
                if (Carbon::parse($pending['created_at'])->lt(now()->subHours(23))) {
                    throw new InvalidArgumentException('Ce remboursement ancien doit être rapproché avec Stripe avant une nouvelle tentative.');
                }
            } else {
                $pending = [
                    'key' => (string) Str::uuid(),
                    'amount_cents' => $requested,
                    'already_refunded_cents' => $alreadyRefunded,
                    'created_at' => now()->toISOString(),
                ];
                $metadata['refund_pending'] = $pending;
                $locked->update(['metadata' => $metadata]);
            }

            return [
                'payment_intent_id' => $locked->stripe_payment_intent_id,
                'amount_cents' => $requested,
                'already_refunded_cents' => (int) $pending['already_refunded_cents'],
                'key' => $pending['key'],
            ];
        });

        // This request is retried with the same key if the process stops before persisting it.
        // Free-form staff notes are stored locally, not sent as Stripe's restricted reason code.
        $refund = $this->stripeService->refund(
            $request['payment_intent_id'],
            $request['amount_cents'],
            null,
            $request['key']
        );

        if (($refund->status ?? 'succeeded') !== 'succeeded') {
            throw new InvalidArgumentException('Stripe n’a pas encore confirmé le remboursement.');
        }

        return DB::transaction(function () use ($payment, $user, $reason, $request, $refund): Payment {
            $locked = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            $metadata = $locked->metadata ?? [];
            $refunds = $metadata['refunds'] ?? [];
            if (collect($refunds)->contains(fn (array $item): bool => ($item['refund_id'] ?? null) === $refund->id)) {
                return $locked;
            }

            $totalRefunded = min(
                $locked->amount_cents,
                max($locked->refund_amount_cents ?? 0, $request['already_refunded_cents'] + $request['amount_cents'])
            );
            $isFullRefund = $totalRefunded >= $locked->amount_cents;

            $refunds[] = [
                'refund_id' => $refund->id,
                'amount_cents' => $request['amount_cents'],
                'reason' => $reason,
                'refunded_at' => now()->toISOString(),
                'refunded_by' => $user->id,
            ];
            unset($metadata['refund_pending']);
            $metadata['refunds'] = $refunds;

            $locked->update([
                'status' => $isFullRefund ? PaymentStatus::REFUNDED : PaymentStatus::PARTIALLY_REFUNDED,
                'refunded_at' => now(),
                'refund_amount_cents' => $totalRefunded,
                'metadata' => $metadata,
            ]);

            Activity::performedOn($locked)
                ->causedBy($user)
                ->withProperties([
                    'registration_id' => $locked->race_registration_id,
                    'refund_amount_cents' => $request['amount_cents'],
                    'reason' => $reason,
                    'is_full_refund' => $isFullRefund,
                    'stripe_refund_id' => $refund->id,
                ])
                ->log('payment.stripe.refund_processed');

            return $locked->fresh();
        });
    }
}
