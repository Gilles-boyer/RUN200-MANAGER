<?php

namespace App\Application\Registrations\UseCases;

use App\Models\CarTechInspectionHistory;
use App\Models\RaceRegistration;
use App\Models\TechInspection;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RecordTechInspection
{
    /**
     * Record a technical inspection for a registration
     *
     * @param  string  $status  'OK' ou 'FAIL'
     * @param  string|null  $notes  Notes obligatoires si FAIL
     * @param  User  $inspector  L'utilisateur qui effectue le contrôle
     *
     * @throws InvalidArgumentException
     */
    public function execute(
        RaceRegistration $registration,
        string $status,
        ?string $notes,
        User $inspector
    ): TechInspection {
        // Valider le statut
        if (! in_array($status, ['OK', 'FAIL'])) {
            throw new InvalidArgumentException('Le statut doit être OK ou FAIL.');
        }

        // Notes obligatoires si FAIL
        if ($status === 'FAIL' && empty(trim($notes ?? ''))) {
            throw new InvalidArgumentException('Les notes sont obligatoires pour un contrôle technique échoué.');
        }

        // Vérifier que l'inscription est dans un état valide pour le contrôle technique
        $validStatuses = ['ACCEPTED', 'ADMIN_CHECKED'];
        if (! in_array($registration->status, $validStatuses)) {
            throw new InvalidArgumentException(
                'Le contrôle technique ne peut être effectué que sur une inscription acceptée ou ayant passé le contrôle administratif.'
            );
        }

        // Vérifier qu'il n'y a pas déjà une inspection
        if ($registration->techInspection()->exists()) {
            throw new InvalidArgumentException(
                'Un contrôle technique a déjà été enregistré pour cette inscription.'
            );
        }

        return DB::transaction(function () use ($registration, $status, $notes, $inspector) {
            // Créer l'inspection
            $inspection = TechInspection::create([
                'race_registration_id' => $registration->id,
                'status' => $status,
                'notes' => $notes ? trim($notes) : null,
                'inspected_by' => $inspector->id,
                'inspected_at' => now(),
            ]);

            // Créer l'entrée dans l'historique pour la voiture
            CarTechInspectionHistory::create([
                'car_id' => $registration->car_id,
                'race_registration_id' => $registration->id,
                'tech_inspection_id' => $inspection->id,
                'status' => $status,
                'notes' => $notes ? trim($notes) : null,
                'inspected_by' => $inspector->id,
                'inspected_at' => now(),
            ]);

            // Mettre à jour le statut de l'inscription
            $newStatus = $status === 'OK' ? 'TECH_CHECKED_OK' : 'TECH_CHECKED_FAIL';
            $registration->update(['status' => $newStatus]);

            // Mettre à jour la fiche d'engagement si elle existe
            $engagementValidation = new UpdateEngagementFormValidation;
            $engagementValidation->recordTechValidation($registration, $inspector, $status, $notes);

            // Log de l'activité
            activity()
                ->performedOn($registration)
                ->causedBy($inspector)
                ->withProperties([
                    'inspection_id' => $inspection->id,
                    'inspection_status' => $status,
                    'notes' => $notes,
                    'new_registration_status' => $newStatus,
                ])
                ->log($status === 'OK' ? 'tech.ok' : 'tech.fail');

            // Dispatch event for email notification
            \App\Events\TechInspectionCompleted::dispatch($inspection);

            return $inspection->load(['registration', 'inspector']);
        });
    }

    /**
     * Valider le contrôle technique (OK)
     */
    public function pass(RaceRegistration $registration, User $inspector, ?string $notes = null): TechInspection
    {
        return $this->execute($registration, 'OK', $notes, $inspector);
    }

    /**
     * Échouer le contrôle technique (FAIL)
     */
    public function fail(RaceRegistration $registration, User $inspector, string $notes): TechInspection
    {
        return $this->execute($registration, 'FAIL', $notes, $inspector);
    }

    /**
     * Réinitialiser le contrôle technique (permettre une nouvelle inspection)
     * Utilisé en cas de correction après FAIL
     */
    public function reset(RaceRegistration $registration, User $actor): void
    {
        if (! $registration->techInspection) {
            throw new InvalidArgumentException('Aucun contrôle technique à réinitialiser.');
        }

        // Seuls les échecs peuvent être réinitialisés
        if ($registration->status !== 'TECH_CHECKED_FAIL') {
            throw new InvalidArgumentException(
                'Seuls les contrôles techniques échoués peuvent être réinitialisés.'
            );
        }

        DB::transaction(function () use ($registration, $actor) {
            // Supprimer l'inspection existante
            $registration->techInspection->delete();

            // Remettre le statut à ADMIN_CHECKED (ou ACCEPTED si pas de checkpoint admin)
            $hasAdminCheck = $registration->hasPassedCheckpoint('ADMIN_CHECK');
            $newStatus = $hasAdminCheck ? 'ADMIN_CHECKED' : 'ACCEPTED';
            $registration->update(['status' => $newStatus]);

            activity()
                ->performedOn($registration)
                ->causedBy($actor)
                ->withProperties([
                    'action' => 'tech_inspection_reset',
                    'new_status' => $newStatus,
                ])
                ->log('tech.reset');
        });
    }
}
