<?php

declare(strict_types=1);

namespace App\Application\Payments\UseCases;

use App\Domain\Payment\Enums\PaymentMethod;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/** Checks a recorded payment before a deliberately selected historical campaign. */
final class ReviewRecordedPaymentReceipt
{
    public function __construct(
        private IssuePaymentReceipt $issuer,
        private VerifyHistoricalStripePayment $stripeVerifier,
    ) {}

    public function holdReason(Payment $payment): ?string
    {
        $reason = $this->localHoldReason($payment);
        if ($reason !== null) {
            return $reason;
        }

        return $payment->method === PaymentMethod::STRIPE
            ? $this->stripeVerifier->holdReason($payment)
            : null;
    }

    public function issue(Payment $payment, int $expectedUserId, string $expectedEmail, string $expectedSnapshotFingerprint): PaymentReceipt
    {
        $reason = $this->holdReason($payment);
        if ($reason !== null) {
            throw new InvalidArgumentException('Paiement retenu : '.$reason);
        }

        return DB::transaction(function () use ($payment, $expectedUserId, $expectedEmail, $expectedSnapshotFingerprint): PaymentReceipt {
            $locked = Payment::query()
                ->with(['registration.race', 'registration.pilot.user'])
                ->lockForUpdate()
                ->findOrFail($payment->id);

            if ($this->localHoldReason($locked) !== null
                || $locked->status !== $payment->status
                || $locked->method !== $payment->method
                || $locked->amount_cents !== $payment->amount_cents
                || $locked->amount !== $payment->amount
                || $locked->currency !== $payment->currency
                || $locked->refund_amount_cents !== $payment->refund_amount_cents
                || $locked->paid_at?->getTimestamp() !== $payment->paid_at?->getTimestamp()
                || $locked->race_registration_id !== $payment->race_registration_id
                || $locked->user_id !== $payment->user_id
                || $locked->stripe_payment_intent_id !== $payment->stripe_payment_intent_id
                || $locked->registration?->pilot?->user_id !== $expectedUserId
                || $locked->registration?->pilot?->user?->email !== $expectedEmail
                || ! hash_equals($expectedSnapshotFingerprint, $this->reviewFingerprint($locked))) {
                throw new InvalidArgumentException('Le dossier a changé depuis sa vérification.');
            }

            return $this->issuer->issue($locked, historical: true);
        });
    }

    /** Hash of every source PDF field and any immutable receipt already issued. */
    public function reviewFingerprint(Payment $payment): string
    {
        $snapshot = $this->issuer->snapshot($payment);
        foreach ($snapshot as $key => $value) {
            if ($value instanceof DateTimeInterface) {
                $snapshot[$key] = $value->format('Y-m-d H:i:s.uP');
            }
        }

        $receipt = PaymentReceipt::query()->where('payment_id', $payment->id)->first();

        return hash('sha256', json_encode([
            $snapshot,
            $receipt === null ? null : $this->receiptFields($receipt),
        ], JSON_THROW_ON_ERROR));
    }

    /** A sent receipt is skipped, but its existing PDF contents still belong to the reviewed lot. */
    public function receiptFingerprint(PaymentReceipt $receipt): string
    {
        return hash('sha256', json_encode($this->receiptFields($receipt), JSON_THROW_ON_ERROR));
    }

    /** @return array<string, mixed> */
    private function receiptFields(PaymentReceipt $receipt): array
    {
        $keys = [
            'number', 'issued_at',
            'issuer_name', 'issuer_siret', 'issuer_address', 'issuer_email', 'issuer_phone',
            'payer_name', 'payer_email', 'race_name', 'race_date',
            'amount_cents', 'payment_status_at_issue', 'refund_amount_cents_at_issue',
            'currency', 'payment_method', 'paid_at',
        ];

        return array_intersect_key($receipt->getAttributes(), array_flip($keys));
    }

    private function localHoldReason(Payment $payment): ?string
    {
        if (! in_array($payment->status, [
            PaymentStatus::PAID,
            PaymentStatus::PARTIALLY_REFUNDED,
            PaymentStatus::REFUNDED,
        ], true)) {
            return 'status_not_collected';
        }
        if ($payment->paid_at === null || $payment->paid_at->isFuture()) {
            return 'paid_at_missing_or_future';
        }
        if (($payment->amount_cents ?? 0) <= 0) {
            return 'legacy_amount_requires_review';
        }
        $decimalAmount = (string) $payment->amount;
        if (! preg_match('/^(\d{1,6})\.(\d{2})$/', $decimalAmount, $amountParts)
            || ((int) $amountParts[1] * 100) + (int) $amountParts[2] !== $payment->amount_cents) {
            return 'amount_mismatch';
        }
        if (Payment::query()
            ->where('race_registration_id', $payment->race_registration_id)
            ->whereKeyNot($payment->id)
            ->whereIn('status', [
                PaymentStatus::PAID->value,
                PaymentStatus::PARTIALLY_REFUNDED->value,
                PaymentStatus::REFUNDED->value,
            ])
            ->exists()) {
            return 'multiple_collected_payments';
        }

        $payment->loadMissing(['registration.race', 'registration.pilot.user']);
        $user = $payment->registration?->pilot?->user;
        if (! $user instanceof User || ! filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            return 'pilot_or_recorded_address_missing';
        }

        try {
            $snapshot = $this->issuer->snapshot($payment);
        } catch (InvalidArgumentException) {
            return 'payment_snapshot_invalid';
        }

        $receipt = PaymentReceipt::query()->where('payment_id', $payment->id)->first();
        if ($receipt !== null && (
            $receipt->amount_cents !== $snapshot['amount_cents']
            || $receipt->currency !== $snapshot['currency']
            || $receipt->payment_method !== $snapshot['payment_method']
            || $receipt->payment_status_at_issue !== $snapshot['payment_status_at_issue']
            || $receipt->refund_amount_cents_at_issue !== $snapshot['refund_amount_cents_at_issue']
            || $receipt->paid_at?->getTimestamp() !== $payment->paid_at->getTimestamp()
        )) {
            return 'receipt_snapshot_mismatch';
        }

        return null;
    }
}
