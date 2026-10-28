<?php

namespace App\Application\Registrations\UseCases;

use App\Events\RegistrationAccepted;
use App\Events\RegistrationRefused;
use App\Models\RaceRegistration;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ValidateRegistration
{
    /**
     * Valider (accepter ou refuser) une inscription
     *
     * @param  string  $status  'ACCEPTED' ou 'REFUSED'
     * @param  string|null  $reason  Raison obligatoire si refus
     * @param  User|null  $user  L'utilisateur qui effectue la validation
     */
    public function execute(
        RaceRegistration $registration,
        string $status,
        ?string $reason = null,
        ?User $user = null,
        bool $allowPendingPaymentAcceptance = false
    ): RaceRegistration {
        // Vérifier que le statut est valide
        if (! in_array($status, ['ACCEPTED', 'REFUSED'])) {
            throw new InvalidArgumentException('Le statut doit être ACCEPTED ou REFUSED.');
        }

        // Raison obligatoire si refus
        if ($status === 'REFUSED' && empty($reason)) {
            throw new InvalidArgumentException('Une raison est obligatoire pour un refus');
        }

        $validator = $user ?? Auth::user();

        return DB::transaction(function () use ($registration, $status, $reason, $validator, $allowPendingPaymentAcceptance) {
            $registration = RaceRegistration::query()->lockForUpdate()->findOrFail($registration->id);
            $previousStatus = $registration->status;
            $acceptingBeforePayment = $status === 'ACCEPTED'
                && $allowPendingPaymentAcceptance
                && $registration->isPendingPayment();

            if (! $registration->isPending() && ! $acceptingBeforePayment) {
                throw new InvalidArgumentException('Seules les inscriptions en attente peuvent être validées');
            }

            $paymentConfirmed = $registration->isPaid();

            $registration->update([
                'status' => $status,
                'reason' => $reason,
                'validated_at' => now(),
                'validated_by' => $validator?->id,
            ]);

            activity()
                ->performedOn($registration)
                ->causedBy($validator)
                ->withProperties([
                    'status' => $status,
                    'reason' => $reason,
                    'previous_status' => $previousStatus,
                    'payment_confirmed_at_validation' => $paymentConfirmed,
                    'accepted_before_payment' => $acceptingBeforePayment && ! $paymentConfirmed,
                ])
                ->log($status === 'ACCEPTED' ? 'registration.accepted' : 'registration.refused');

            // Dispatch events for email notifications
            if ($status === 'ACCEPTED') {
                RegistrationAccepted::dispatch($registration);
            } else {
                RegistrationRefused::dispatch($registration);
            }

            return $registration->fresh();
        });
    }

    /**
     * Accepter une inscription
     */
    public function accept(RaceRegistration $registration, ?User $user = null): RaceRegistration
    {
        return $this->execute($registration, 'ACCEPTED', null, $user);
    }

    /** Accept an unpaid registration by an explicit staff decision. */
    public function acceptBeforePayment(RaceRegistration $registration, ?User $user = null): RaceRegistration
    {
        return $this->execute($registration, 'ACCEPTED', null, $user, true);
    }

    /**
     * Refuser une inscription avec raison
     */
    public function refuse(RaceRegistration $registration, string $reason, ?User $user = null): RaceRegistration
    {
        return $this->execute($registration, 'REFUSED', $reason, $user);
    }
}
