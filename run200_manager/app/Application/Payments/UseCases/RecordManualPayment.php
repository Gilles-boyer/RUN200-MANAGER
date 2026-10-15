<?php

declare(strict_types=1);

namespace App\Application\Payments\UseCases;

use App\Domain\Payment\Enums\PaymentMethod;
use App\Events\PaymentConfirmed;
use App\Models\Payment;
use App\Models\RaceRegistration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Spatie\Activitylog\Facades\Activity;

/**
 * Use case for recording a manual payment.
 */
final class RecordManualPayment
{
    /**
     * Execute the use case.
     *
     * @throws InvalidArgumentException
     */
    public function execute(
        RaceRegistration $registration,
        User $recorder,
        float $amount,
        string $currency = 'EUR',
        ?string $notes = null,
        string $method = 'manual'
    ): Payment {
        $amountCents = (int) round($amount * 100);
        if ($amountCents < 1 || ! is_finite($amount) || ! preg_match('/^[A-Z]{3}$/', $currency)
            || ! in_array($method, array_column([
                PaymentMethod::MANUAL,
                ...PaymentMethod::onsiteMethods(),
            ], 'value'), true)) {
            throw new InvalidArgumentException('Le montant, la devise ou le mode de paiement est invalide.');
        }

        return DB::transaction(function () use ($registration, $recorder, $amountCents, $currency, $notes, $method): Payment {
            $registration = RaceRegistration::query()->lockForUpdate()->findOrFail($registration->id);
            $existingPaid = Payment::query()
                ->where('race_registration_id', $registration->id)
                ->where('status', 'paid')
                ->first();
            if ($existingPaid !== null) {
                if (($existingPaid->metadata['manual_confirmation'] ?? false)
                    && $existingPaid->amount_cents === $amountCents
                    && $existingPaid->currency === $currency
                    && $existingPaid->method === PaymentMethod::from($method)) {
                    return $existingPaid;
                }

                throw new InvalidArgumentException('Cette inscription a déjà un paiement validé.');
            }

            if ($registration->status !== 'PENDING_PAYMENT'
                && ! in_array($registration->status, RaceRegistration::engagedStatuses(), true)) {
                throw new InvalidArgumentException('Le paiement ne peut être enregistré que pour une inscription active.');
            }

            if ($registration->payments()->whereIn('status', ['pending', 'processing'])
                ->where('method', PaymentMethod::STRIPE->value)->exists()) {
                throw new InvalidArgumentException('Un paiement Stripe est en cours. Vérifiez son état avant un encaissement manuel.');
            }

            $pending = Payment::query()
                ->where('race_registration_id', $registration->id)
                ->where('status', 'pending')
                ->whereIn('method', array_column([
                    PaymentMethod::MANUAL,
                    ...PaymentMethod::onsiteMethods(),
                ], 'value'))
                ->latest('id')
                ->first();

            $metadata = array_merge($pending?->metadata ?? [], [
                'notes' => $notes,
                'recorded_by' => $recorder->id,
                'recorded_at' => now()->toISOString(),
                'manual_confirmation' => true,
            ]);

            if ($pending !== null) {
                $pending->update([
                    'amount' => $amountCents / 100,
                    'amount_cents' => $amountCents,
                    'currency' => $currency,
                    'method' => $method,
                    'status' => 'paid',
                    'paid_at' => now(),
                    'metadata' => $metadata,
                ]);
                $payment = $pending;
            } else {
                $payment = Payment::create([
                    'race_registration_id' => $registration->id,
                    'user_id' => $recorder->id,
                    'amount' => $amountCents / 100,
                    'amount_cents' => $amountCents,
                    'currency' => $currency,
                    'method' => $method,
                    'status' => 'paid',
                    'paid_at' => now(),
                    'metadata' => $metadata,
                ]);
            }

            $registration->payments()
                ->where('status', 'pending')
                ->where('id', '!=', $payment->id)
                ->where('method', '!=', PaymentMethod::STRIPE->value)
                ->update(['status' => 'cancelled']);

            if ($registration->status === 'PENDING_PAYMENT') {
                $registration->update(['status' => 'PENDING_VALIDATION']);
            }

            // Log activity
            Activity::performedOn($payment)
                ->causedBy($recorder)
                ->withProperties([
                    'registration_id' => $registration->id,
                    'amount_cents' => $amountCents,
                    'currency' => $currency,
                    'notes' => $notes,
                ])
                ->log('payment.manual.recorded');

            // Dispatch event for email notification
            PaymentConfirmed::dispatch($payment);

            return $payment;
        });
    }
}
