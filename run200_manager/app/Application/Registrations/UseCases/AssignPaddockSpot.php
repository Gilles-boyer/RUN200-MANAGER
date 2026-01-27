<?php

namespace App\Application\Registrations\UseCases;

use App\Events\PaddockSpotAssigned;
use App\Models\PaddockSpot;
use App\Models\RaceRegistration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Use Case: Réserver ou assigner un emplacement de paddock pour une course spécifique
 *
 * IMPORTANT: Les emplacements de paddock sont réservés PAR COURSE.
 * Un pilote doit réserver un emplacement pour chaque course à laquelle il participe.
 * Le même emplacement peut être réservé par différents pilotes pour différentes courses.
 *
 * Peut être utilisé par:
 * - Pilote (après inscription acceptée) pour choisir un emplacement disponible
 * - Staff/Admin pour assigner manuellement un emplacement à un pilote
 */
class AssignPaddockSpot
{
    /**
     * Assigner un emplacement de paddock à une inscription (pour une course spécifique)
     *
     * @param  RaceRegistration  $registration  L'inscription à laquelle assigner l'emplacement
     * @param  PaddockSpot  $spot  L'emplacement à assigner
     * @param  User  $assignedBy  L'utilisateur qui effectue l'assignation
     * @param  bool  $force  Forcer l'assignation même si l'emplacement est occupé (Admin uniquement)
     *
     * @throws ValidationException
     */
    public function execute(
        RaceRegistration $registration,
        PaddockSpot $spot,
        User $assignedBy,
        bool $force = false
    ): RaceRegistration {
        return DB::transaction(function () use ($registration, $spot, $assignedBy, $force) {
            // Vérifications préalables
            $this->validateAssignment($registration, $spot, $assignedBy, $force);

            // Libérer l'ancien emplacement de cette inscription si présent
            if ($registration->paddock_spot_id) {
                $registration->update([
                    'paddock_spot_id' => null,
                    'paddock' => null,
                ]);

                activity()
                    ->performedOn($registration)
                    ->causedBy($assignedBy)
                    ->log('Ancien emplacement de paddock libéré pour cette course');
            }

            // Si force admin et l'emplacement est pris pour cette course, libérer l'autre inscription
            if ($force && $spot->isOccupiedForRace($registration->race_id)) {
                $existingRegistration = $spot->registrationForRace($registration->race_id);

                if ($existingRegistration) {
                    $existingRegistration->update([
                        'paddock_spot_id' => null,
                        'paddock' => null,
                    ]);

                    activity()
                        ->performedOn($existingRegistration)
                        ->causedBy($assignedBy)
                        ->withProperties([
                            'forced_by_admin' => true,
                            'new_registration_id' => $registration->id,
                        ])
                        ->log('Emplacement de paddock retiré par admin (réassignation forcée)');
                }
            }

            // Assigner le nouvel emplacement
            $registration->update([
                'paddock_spot_id' => $spot->id,
                'paddock' => $spot->spot_number, // Maintenir la compatibilité avec l'ancien champ
            ]);

            // Enregistrer l'activité
            activity()
                ->performedOn($registration)
                ->causedBy($assignedBy)
                ->withProperties([
                    'spot_number' => $spot->spot_number,
                    'zone' => $spot->zone,
                    'race_id' => $registration->race_id,
                    'race_name' => $registration->race->name ?? 'N/A',
                    'assigned_by_role' => $assignedBy->isAdmin() ? 'admin' : ($assignedBy->isStaff() ? 'staff' : 'pilot'),
                ])
                ->log('Emplacement de paddock assigné pour cette course');

            // Déclencher l'événement
            event(new PaddockSpotAssigned($registration, $spot, $assignedBy));

            return $registration->fresh(['paddockSpot', 'pilot', 'car', 'race']);
        });
    }

    /**
     * Valider l'assignation d'un emplacement
     *
     * @throws ValidationException
     */
    protected function validateAssignment(
        RaceRegistration $registration,
        PaddockSpot $spot,
        User $assignedBy,
        bool $force
    ): void {
        // L'inscription doit être acceptée (sauf si admin force)
        if (! $registration->isAccepted() && ! $force) {
            throw ValidationException::withMessages([
                'registration' => 'L\'inscription doit être acceptée pour réserver un emplacement.',
            ]);
        }

        // L'emplacement doit être en service
        if ($spot->isOutOfService()) {
            throw ValidationException::withMessages([
                'spot' => 'Cet emplacement est actuellement hors service.',
            ]);
        }

        // L'emplacement doit être disponible pour cette course (sauf si admin force)
        if ($spot->isOccupiedForRace($registration->race_id) && ! $force) {
            $currentPilot = $spot->getPilotForRace($registration->race_id);
            $pilotName = $currentPilot
                ? "{$currentPilot->first_name} {$currentPilot->last_name}"
                : 'un autre pilote';

            throw ValidationException::withMessages([
                'spot' => "Cet emplacement est déjà réservé par {$pilotName} pour cette course.",
            ]);
        }

        // Seul admin peut forcer
        if ($force && ! $assignedBy->isAdmin()) {
            throw ValidationException::withMessages([
                'authorization' => 'Seul un administrateur peut forcer l\'assignation d\'un emplacement occupé.',
            ]);
        }
    }
}
