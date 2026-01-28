<?php

declare(strict_types=1);

namespace App\Application\Payments\UseCases;

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
        // Validate registration status
        if ($registration->status !== 'ACCEPTED') {
            throw new InvalidArgumentException(
                'Le paiement ne peut être enregistré que pour une inscription acceptée.'
            );
        }

        // Check if there's already a paid payment
        $existingPaid = $registration->payments()
            ->where('status', 'paid')
            ->first();

        if ($existingPaid) {
            throw new InvalidArgumentException(
                'Cette inscription a déjà un paiement validé.'
            );
        }

        return DB::transaction(function () use ($registration, $recorder, $amount, $currency, $notes, $method) {
            // Cancel any pending payments
            $registration->payments()
                ->where('status', 'pending')
                ->update(['status' => 'cancelled']);

            // Create paid payment record
            $payment = Payment::create([
                'race_registration_id' => $registration->id,
                'user_id' => $recorder->id,
                'amount' => $amount,
                'amount_cents' => (int) round($amount * 100),
                'currency' => $currency,
                'method' => $method,
                'status' => 'paid',
                'paid_at' => now(),
                'metadata' => [
                    'notes' => $notes,
                    'recorded_by' => $recorder->id,
                    'recorded_at' => now()->toISOString(),
                ],
            ]);

            // Log activity
            Activity::performedOn($payment)
                ->causedBy($recorder)
                ->withProperties([
                    'registration_id' => $registration->id,
                    'amount' => $amount,
                    'currency' => $currency,
                    'notes' => $notes,
                ])
                ->log('payment.manual.recorded');

            // Dispatch event for email notification
            \App\Events\PaymentConfirmed::dispatch($payment);

            return $payment;
        });
    }
}
