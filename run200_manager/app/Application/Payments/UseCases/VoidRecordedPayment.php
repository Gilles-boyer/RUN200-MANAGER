<?php

declare(strict_types=1);

namespace App\Application\Payments\UseCases;

use App\Domain\Payment\Enums\PaymentMethod;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use App\Models\RaceRegistration;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Spatie\Activitylog\Facades\Activity;

/** Correct a local payment entered as paid when no money was received. */
final class VoidRecordedPayment
{
    /**
     * @param  array{payment_id:int,registration_id:int,pilot_id:int,status:string,method:string,amount_cents:int,receipt_id:int|null,receipt_email_status:string|null,receipt_email_sent_at:string|null}  $expectedSnapshot
     */
    public function execute(Payment $payment, User $actor, string $reason, array $expectedSnapshot): Payment
    {
        if (! $actor->can('payment.void')) {
            throw new AuthorizationException('Vous ne pouvez pas annuler un paiement enregistré.');
        }

        $reason = trim($reason);
        if (mb_strlen($reason) < 10 || mb_strlen($reason) > 500) {
            throw new InvalidArgumentException('Indiquez un motif de 10 à 500 caractères.');
        }

        if (($expectedSnapshot['payment_id'] ?? null) !== $payment->id
            || ! is_int($expectedSnapshot['registration_id'] ?? null)) {
            throw new InvalidArgumentException('Le paiement ou le pilote a changé. Rechargez la page avant de décider.');
        }

        return DB::transaction(function () use ($payment, $actor, $reason, $expectedSnapshot): Payment {
            // Match the lock order of manual payment recording: registration, then payment.
            $registration = RaceRegistration::query()->lockForUpdate()
                ->findOrFail($expectedSnapshot['registration_id']);
            $locked = Payment::query()->where('race_registration_id', $registration->id)
                ->lockForUpdate()->findOrFail($payment->id);

            $actual = [
                'payment_id' => $locked->id,
                'registration_id' => $locked->race_registration_id,
                'pilot_id' => $registration->pilot_id,
                'status' => $locked->status->value,
                'method' => $locked->method->value,
                'amount_cents' => $locked->amount_cents,
            ];
            foreach ($actual as $field => $value) {
                if (($expectedSnapshot[$field] ?? null) !== $value) {
                    throw new InvalidArgumentException('Le paiement ou le pilote a changé. Rechargez la page avant de décider.');
                }
            }

            if ($locked->status !== PaymentStatus::PAID
                || $locked->method === PaymentMethod::STRIPE
                || $locked->stripe_session_id !== null
                || $locked->stripe_payment_intent_id !== null
                || $locked->stripe_customer_id !== null
                || $locked->stripe_event_id !== null
                || $locked->refunded_at !== null
                || (int) $locked->refund_amount_cents > 0) {
                throw new InvalidArgumentException('Ce paiement ne peut pas être annulé comme une erreur de saisie. Vérifiez son encaissement.');
            }

            $receipt = PaymentReceipt::query()->where('payment_id', $locked->id)
                ->lockForUpdate()->first();
            $actualReceipt = [
                'receipt_id' => $receipt?->id,
                'receipt_email_status' => $receipt?->email_status,
                'receipt_email_sent_at' => $receipt?->email_sent_at?->toISOString(),
            ];
            foreach ($actualReceipt as $field => $value) {
                if (! array_key_exists($field, $expectedSnapshot) || $expectedSnapshot[$field] !== $value) {
                    throw new InvalidArgumentException('Le paiement, le pilote ou le reçu a changé. Rechargez la page avant de décider.');
                }
            }
            if ($receipt !== null && ! in_array($receipt->email_status, ['pending', 'not_requested', 'sent'], true)) {
                throw new InvalidArgumentException('L’envoi du reçu est incertain. Faites vérifier ce dossier avant de corriger le paiement.');
            }
            if ($receipt !== null
                && $receipt->email_status !== 'sent'
                && $receipt->email_sent_at !== null) {
                throw new InvalidArgumentException('L’envoi du reçu est incertain. Faites vérifier ce dossier avant de corriger le paiement.');
            }

            $metadata = $locked->metadata ?? [];
            $metadata['void_reason'] = $reason;
            $metadata['voided_by'] = $actor->id;
            $metadata['voided_at'] = now()->toISOString();
            $metadata['void_receipt_followup_required'] = $receipt?->email_status === 'sent';

            $locked->update(['status' => PaymentStatus::CANCELLED, 'metadata' => $metadata]);
            if ($receipt !== null && $receipt->email_status !== 'sent') {
                $receipt->update([
                    'email_status' => 'voided',
                    'email_last_error' => 'payment_voided_before_delivery',
                ]);
            }

            if ($registration->status === 'PENDING_VALIDATION' && ! $registration->isPaid()) {
                $registration->update(['status' => 'PENDING_PAYMENT']);
            }

            Activity::performedOn($locked)->causedBy($actor)->withProperties([
                'registration_id' => $registration->id,
                'payment_id' => $locked->id,
                'receipt_id' => $receipt?->id,
                'receipt_email_status' => $receipt?->email_status,
                'receipt_followup_required' => $metadata['void_receipt_followup_required'],
                'reason' => $reason,
            ])->log('payment.manual.voided');

            return $locked->refresh();
        });
    }
}
