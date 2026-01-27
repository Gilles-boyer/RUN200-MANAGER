<?php

namespace App\Application\Registrations\UseCases;

use App\Models\RaceRegistration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AssignPaddock
{
    /**
     * Assigner un paddock à une inscription
     *
     * @param  string  $paddock  Numéro/Nom du paddock
     * @param  User|null  $user  L'utilisateur qui effectue l'assignation
     */
    public function execute(RaceRegistration $registration, string $paddock, ?User $user = null): RaceRegistration
    {
        $paddock = trim($paddock);

        // Vérifier que le paddock n'est pas vide
        if (empty($paddock)) {
            throw new InvalidArgumentException('Le numéro de paddock est obligatoire');
        }

        // Vérifier que l'inscription est acceptée
        if (! $registration->isAccepted()) {
            throw new InvalidArgumentException('Seules les inscriptions acceptées peuvent recevoir un paddock');
        }

        // Vérifier que le paddock n'est pas déjà assigné à une autre inscription pour cette course
        $existingPaddock = RaceRegistration::where('race_id', $registration->race_id)
            ->where('id', '!=', $registration->id)
            ->where('paddock', $paddock)
            ->exists();

        if ($existingPaddock) {
            throw new InvalidArgumentException('Ce paddock est déjà assigné à une autre inscription pour cette course');
        }

        $actor = $user ?? auth()->user();

        return DB::transaction(function () use ($registration, $paddock, $actor) {
            $oldPaddock = $registration->paddock;

            $registration->update([
                'paddock' => $paddock,
            ]);

            activity()
                ->performedOn($registration)
                ->causedBy($actor)
                ->withProperties([
                    'old_paddock' => $oldPaddock,
                    'new_paddock' => $paddock,
                ])
                ->log('registration.paddock_assigned');

            return $registration->fresh();
        });
    }
}
