<?php

namespace App\Domain\Registration\Rules;

/**
 * Defines the valid checkpoint transitions for a registration.
 * Each checkpoint requires certain previous checkpoints to be completed.
 */
class CheckpointTransitions
{
    /**
     * Map of checkpoint codes to their required previous checkpoints.
     * Empty array means no prerequisites.
     */
    private const PREREQUISITES = [
        // Admin check requires the registration to be ACCEPTED
        'ADMIN_CHECK' => [],

        // Tech check requires admin check to be done
        'TECH_CHECK' => ['ADMIN_CHECK'],

        // Entry requires both admin and tech checks
        'ENTRY' => ['ADMIN_CHECK', 'TECH_CHECK'],

        // Bracelet requires entry to be scanned
        'BRACELET' => ['ADMIN_CHECK', 'TECH_CHECK', 'ENTRY'],
    ];

    /**
     * Map of checkpoint codes to the required registration status.
     */
    private const REQUIRED_STATUS = [
        'ADMIN_CHECK' => ['ACCEPTED'],
        'TECH_CHECK' => ['ACCEPTED', 'ADMIN_CHECKED'],
        'ENTRY' => ['ACCEPTED', 'ADMIN_CHECKED', 'TECH_CHECKED_OK'],
        'BRACELET' => ['ACCEPTED', 'ADMIN_CHECKED', 'TECH_CHECKED_OK', 'ENTRY_SCANNED'],
    ];

    /**
     * Map of checkpoint codes to the new status after scanning.
     */
    private const STATUS_AFTER_SCAN = [
        'ADMIN_CHECK' => 'ADMIN_CHECKED',
        'TECH_CHECK' => 'TECH_CHECKED_OK',
        'ENTRY' => 'ENTRY_SCANNED',
        'BRACELET' => 'BRACELET_GIVEN',
    ];

    /**
     * Check if a checkpoint scan is allowed for the given registration.
     *
     * @param  string  $checkpointCode  The checkpoint to scan
     * @param  string  $currentStatus  The current registration status
     * @param  array  $passedCheckpoints  Array of checkpoint codes already passed
     */
    public static function canScan(string $checkpointCode, string $currentStatus, array $passedCheckpoints): bool
    {
        // Check if checkpoint exists in our rules
        if (! isset(self::PREREQUISITES[$checkpointCode])) {
            return false;
        }

        // Check if already scanned
        if (in_array($checkpointCode, $passedCheckpoints)) {
            return false;
        }

        // Check required status
        $allowedStatuses = self::REQUIRED_STATUS[$checkpointCode];
        if (! in_array($currentStatus, $allowedStatuses)) {
            return false;
        }

        // Check prerequisites
        $prerequisites = self::PREREQUISITES[$checkpointCode];
        foreach ($prerequisites as $prereq) {
            if (! in_array($prereq, $passedCheckpoints)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the new status after scanning a checkpoint.
     */
    public static function getStatusAfterScan(string $checkpointCode): ?string
    {
        return self::STATUS_AFTER_SCAN[$checkpointCode] ?? null;
    }

    /**
     * Get the prerequisites for a checkpoint.
     */
    public static function getPrerequisites(string $checkpointCode): array
    {
        return self::PREREQUISITES[$checkpointCode] ?? [];
    }

    /**
     * Get validation error message for a failed transition.
     */
    public static function getErrorMessage(string $checkpointCode, string $currentStatus, array $passedCheckpoints): string
    {
        if (! isset(self::PREREQUISITES[$checkpointCode])) {
            return "Checkpoint inconnu: {$checkpointCode}";
        }

        if (in_array($checkpointCode, $passedCheckpoints)) {
            return 'Ce checkpoint a déjà été scanné pour cette inscription';
        }

        $allowedStatuses = self::REQUIRED_STATUS[$checkpointCode];
        if (! in_array($currentStatus, $allowedStatuses)) {
            return "Statut d'inscription incompatible. Statut actuel: {$currentStatus}";
        }

        $prerequisites = self::PREREQUISITES[$checkpointCode];
        $missing = array_diff($prerequisites, $passedCheckpoints);
        if (! empty($missing)) {
            return 'Checkpoints préalables manquants: '.implode(', ', $missing);
        }

        return 'Transition non autorisée';
    }
}
