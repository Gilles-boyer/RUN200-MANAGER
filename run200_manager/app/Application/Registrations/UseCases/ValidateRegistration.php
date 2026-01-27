<?php

namespace App\Application\Registrations\UseCases;

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
    public function execute(RaceRegistration $registration, string $status, ?string $reason = null, ?User $user = null): RaceRegistration
    {
        // Vérifier que le statut est valide
        if (! in_array($status, ['ACCEPTED', 'REFUSED'])) {
            throw new InvalidArgumentException('Le statut doit être ACCEPTED ou REFUSED.');
        }

        // Vérifier que l'inscription est en attente
        if (! $registration->isPending()) {
            throw new InvalidArgumentException('Seules les inscriptions en attente peuvent être validées');
        }

        // Raison obligatoire si refus
        if ($status === 'REFUSED' && empty($reason)) {
            throw new InvalidArgumentException('Une raison est obligatoire pour un refus');
        }

        $validator = $user ?? Auth::user();

        return DB::transaction(function () use ($registration, $status, $reason, $validator) {
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
                ])
                ->log($status === 'ACCEPTED' ? 'registration.accepted' : 'registration.refused');

            // Dispatch events for email notifications
            if ($status === 'ACCEPTED') {
                \App\Events\RegistrationAccepted::dispatch($registration);
            } else {
                \App\Events\RegistrationRefused::dispatch($registration);
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

    /**
     * Refuser une inscription avec raison
     */
    public function refuse(RaceRegistration $registration, string $reason, ?User $user = null): RaceRegistration
    {
        return $this->execute($registration, 'REFUSED', $reason, $user);
    }
}
