<?php

declare(strict_types=1);

namespace App\Application\Payments\UseCases;

use App\Domain\Payment\Enums\PaymentMethod;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class IssuePaymentReceipt
{
    public function issue(Payment $payment, bool $historical = false): PaymentReceipt
    {
        return DB::transaction(function () use ($payment, $historical): PaymentReceipt {
            $locked = Payment::query()->lockForUpdate()->findOrFail($payment->id);

            $existing = PaymentReceipt::query()->where('payment_id', $locked->id)->first();
            if ($existing !== null) {
                return $existing;
            }

            $receipt = PaymentReceipt::query()->create($this->snapshot($locked) + [
                'payment_id' => $locked->id,
                'issued_at' => now(),
                'email_status' => $historical ? 'not_requested' : 'pending',
            ]);

            // The database id is unique even when concurrent payments are confirmed.
            $receipt->update(['number' => sprintf('REC-%s-%08d', $receipt->issued_at->format('Y'), $receipt->id)]);

            return $receipt;
        });
    }

    /**
     * Validate the source and prepare immutable receipt data, including legacy amounts.
     *
     * @return array<string, mixed>
     */
    public function snapshot(Payment $payment): array
    {
        if (! $payment->canHaveReceipt()) {
            throw new InvalidArgumentException('Le paiement ne permet pas de créer un reçu.');
        }

        $payment->loadMissing(['registration.race', 'registration.pilot.user']);
        $registration = $payment->registration;
        $pilot = $registration?->pilot;
        $race = $registration?->race;
        if ($pilot === null || $race === null) {
            throw new InvalidArgumentException('L’inscription du paiement est incomplète.');
        }

        $payerName = trim($pilot->first_name.' '.$pilot->last_name);
        if ($payerName === '') {
            throw new InvalidArgumentException('Le nom du payeur est indisponible.');
        }

        $issuer = config('payment_receipts.issuer');
        foreach (['name', 'siret', 'address', 'email', 'phone'] as $field) {
            if (! is_array($issuer) || ! is_string($issuer[$field] ?? null) || trim($issuer[$field]) === '') {
                throw new InvalidArgumentException('L’identité de l’émetteur du reçu est incomplète.');
            }
        }
        if (! preg_match('/^\d{14}$/', $issuer['siret'])) {
            throw new InvalidArgumentException('Le SIRET de l’émetteur est invalide.');
        }

        $currency = strtoupper((string) ($payment->currency ?? 'EUR'));
        if (! preg_match('/^[A-Z]{3}$/', $currency)) {
            throw new InvalidArgumentException('La devise du paiement est invalide.');
        }

        $amountCents = $this->amountCents($payment);
        $refundedCents = (int) ($payment->refund_amount_cents ?? 0);
        if ($refundedCents < 0 || $refundedCents > $amountCents) {
            throw new InvalidArgumentException('Le montant remboursé du paiement est incohérent.');
        }
        if (($payment->status === PaymentStatus::PAID && $refundedCents !== 0)
            || ($payment->status === PaymentStatus::PARTIALLY_REFUNDED && ($refundedCents <= 0 || $refundedCents >= $amountCents))
            || ($payment->status === PaymentStatus::REFUNDED && $refundedCents !== $amountCents)) {
            throw new InvalidArgumentException('Le statut et le montant remboursé du paiement sont incohérents.');
        }

        return [
            'issuer_name' => trim($issuer['name']),
            'issuer_siret' => $issuer['siret'],
            'issuer_address' => trim($issuer['address']),
            'issuer_email' => trim($issuer['email']),
            'issuer_phone' => trim($issuer['phone']),
            'payer_name' => $payerName,
            'payer_email' => $pilot->user?->email,
            'race_name' => $race->name,
            'race_date' => $race->race_date,
            'amount_cents' => $amountCents,
            'payment_status_at_issue' => $payment->status->value,
            'refund_amount_cents_at_issue' => $refundedCents,
            'currency' => $currency,
            'payment_method' => $payment->method instanceof PaymentMethod
                ? $payment->method->value
                : (string) $payment->method,
            'paid_at' => $payment->paid_at,
        ];
    }

    private function amountCents(Payment $payment): int
    {
        if (($payment->amount_cents ?? 0) > 0) {
            if (is_numeric($payment->amount) && (float) $payment->amount > 0
                && $this->decimalAmountCents((string) $payment->amount) !== $payment->amount_cents) {
                throw new InvalidArgumentException('Les deux montants du paiement sont incohérents.');
            }

            return $payment->amount_cents;
        }

        // Old rows may have amount_cents=0 because the column was added with that default.
        return $this->decimalAmountCents((string) $payment->amount);
    }

    private function decimalAmountCents(string $amount): int
    {
        if (! preg_match('/^(\d{1,6})\.(\d{2})$/', $amount, $parts)) {
            throw new InvalidArgumentException('Le montant historique du paiement est invalide.');
        }

        $cents = ((int) $parts[1] * 100) + (int) $parts[2];
        if ($cents <= 0) {
            throw new InvalidArgumentException('Le montant du paiement doit être positif.');
        }

        return $cents;
    }
}
