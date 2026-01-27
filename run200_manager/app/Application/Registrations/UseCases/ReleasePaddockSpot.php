<?php

namespace App\Application\Registrations\UseCases;

use App\Events\PaddockSpotReleased;
use App\Models\PaddockSpot;
use App\Models\RaceRegistration;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Use Case: Libérer un emplacement de paddock pour une course spécifique
 *
 * IMPORTANT: Les emplacements sont réservés PAR COURSE.
 * Libérer un emplacement le rend disponible uniquement pour cette course.
 * Les réservations pour d'autres courses ne sont pas affectées.
 */
class ReleasePaddockSpot
{
    /**
     * Libérer l'emplacement de paddock d'une inscription
     *
     * @param  RaceRegistration  $registration  L'inscription dont on libère l'emplacement
     * @param  User  $releasedBy  L'utilisateur qui effectue la libération
     */
    public function execute(
        RaceRegistration $registration,
        User $releasedBy
    ): RaceRegistration {
        return DB::transaction(function () use ($registration, $releasedBy) {
            // Si pas d'emplacement assigné, rien à faire
            if (! $registration->paddock_spot_id) {
                return $registration;
            }

            /** @var PaddockSpot|null $spot */
            $spot = $registration->paddockSpot;

            // Libérer l'emplacement de cette inscription
            $registration->update([
                'paddock_spot_id' => null,
                'paddock' => null,
            ]);

            // Enregistrer l'activité
            if ($spot) {
                activity()
                    ->performedOn($registration)
                    ->causedBy($releasedBy)
                    ->withProperties([
                        'spot_number' => $spot->spot_number,
                        'zone' => $spot->zone,
                        'race_id' => $registration->race_id,
                        'race_name' => $registration->race->name ?? 'N/A',
                        'released_by_role' => $releasedBy->isAdmin() ? 'admin' : ($releasedBy->isStaff() ? 'staff' : 'pilot'),
                    ])
                    ->log('Emplacement de paddock libéré pour cette course');

                // Déclencher l'événement
                event(new PaddockSpotReleased($registration, $spot, $releasedBy));
            }

            return $registration->fresh(['paddockSpot', 'pilot', 'car', 'race']);
        });
    }
}
