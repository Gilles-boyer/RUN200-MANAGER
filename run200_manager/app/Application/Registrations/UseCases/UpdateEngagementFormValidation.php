<?php

namespace App\Application\Registrations\UseCases;

use App\Models\EngagementForm;
use App\Models\RaceRegistration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateEngagementFormValidation
{
    /**
     * Met à jour la fiche d'engagement après validation technique
     */
    public function recordTechValidation(
        RaceRegistration $registration,
        User $techController,
        string $status,
        ?string $notes = null
    ): ?EngagementForm {
        $engagementForm = $registration->engagementForm;

        if (! $engagementForm) {
            Log::warning('No engagement form found for tech validation', [
                'registration_id' => $registration->id,
            ]);

            return null;
        }

        return DB::transaction(function () use ($engagementForm, $techController, $status, $notes) {
            $engagementForm->update([
                'tech_controller_name' => $techController->name,
                'tech_checked_at' => now(),
                'tech_notes' => $status === 'OK'
                    ? ($notes ?: 'Véhicule conforme')
                    : ($notes ?: 'Non conforme'),
            ]);

            Log::info('Engagement form tech validation recorded', [
                'engagement_id' => $engagementForm->id,
                'tech_controller' => $techController->name,
                'status' => $status,
            ]);

            /** @var EngagementForm|null */
            return $engagementForm->fresh();
        });
    }

    /**
     * Met à jour la fiche d'engagement après validation administrative
     */
    public function recordAdminValidation(
        RaceRegistration $registration,
        User $adminStaff,
        ?string $notes = null
    ): ?EngagementForm {
        /** @var EngagementForm|null $engagementForm */
        $engagementForm = $registration->engagementForm;

        if (! $engagementForm) {
            Log::warning('No engagement form found for admin validation', [
                'registration_id' => $registration->id,
            ]);

            return null;
        }

        return DB::transaction(function () use ($engagementForm, $adminStaff, $notes) {
            $engagementForm->update([
                'admin_validated_by' => $adminStaff->id,
                'admin_validated_at' => now(),
                'admin_notes' => $notes ?: 'Validé',
            ]);

            Log::info('Engagement form admin validation recorded', [
                'engagement_id' => $engagementForm->id,
                'admin_staff' => $adminStaff->name,
            ]);

            /** @var EngagementForm|null */
            return $engagementForm->fresh();
        });
    }

    /**
     * Vérifie si la fiche d'engagement est complètement validée
     */
    public function isFullyValidated(EngagementForm $engagementForm): bool
    {
        return $engagementForm->tech_checked_at !== null
            && $engagementForm->admin_validated_at !== null;
    }

    /**
     * Obtient le statut de validation de la fiche
     *
     * @return array<string, mixed>
     */
    public function getValidationStatus(EngagementForm $engagementForm): array
    {
        /** @var User|null $adminValidator */
        $adminValidator = $engagementForm->adminValidator;

        return [
            'is_signed' => $engagementForm->signed_at !== null,
            'is_tech_validated' => $engagementForm->tech_checked_at !== null,
            'is_admin_validated' => $engagementForm->admin_validated_at !== null,
            'is_fully_validated' => $this->isFullyValidated($engagementForm),
            'tech_controller' => $engagementForm->tech_controller_name,
            'tech_validated_at' => $engagementForm->tech_checked_at,
            'admin_validator' => $adminValidator?->name,
            'admin_validated_at' => $engagementForm->admin_validated_at,
        ];
    }
}
