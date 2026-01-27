<?php

namespace App\Domain\Registration\Enums;

enum RegistrationStatus: string
{
    case SUBMITTED = 'SUBMITTED';
    case PENDING_VALIDATION = 'PENDING_VALIDATION';
    case ACCEPTED = 'ACCEPTED';
    case REFUSED = 'REFUSED';
    case ADMIN_CHECKED = 'ADMIN_CHECKED';
    case TECH_CHECKED_OK = 'TECH_CHECKED_OK';
    case TECH_CHECKED_FAIL = 'TECH_CHECKED_FAIL';
    case ENTRY_SCANNED = 'ENTRY_SCANNED';
    case BRACELET_GIVEN = 'BRACELET_GIVEN';
    case RESULTS_IMPORTED = 'RESULTS_IMPORTED';
    case PUBLISHED = 'PUBLISHED';

    /**
     * Get all possible statuses as array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get human-readable label for the status
     */
    public function label(): string
    {
        return match ($this) {
            self::SUBMITTED => 'Soumise',
            self::PENDING_VALIDATION => 'En attente de validation',
            self::ACCEPTED => 'Acceptée',
            self::REFUSED => 'Refusée',
            self::ADMIN_CHECKED => 'Validation administrative',
            self::TECH_CHECKED_OK => 'Contrôle technique OK',
            self::TECH_CHECKED_FAIL => 'Contrôle technique échoué',
            self::ENTRY_SCANNED => 'Entrée effectuée',
            self::BRACELET_GIVEN => 'Bracelet remis',
            self::RESULTS_IMPORTED => 'Résultats importés',
            self::PUBLISHED => 'Résultats publiés',
        };
    }

    /**
     * Get badge color for UI display
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::SUBMITTED, self::PENDING_VALIDATION => 'yellow',
            self::ACCEPTED, self::TECH_CHECKED_OK, self::ENTRY_SCANNED, self::BRACELET_GIVEN => 'green',
            self::REFUSED, self::TECH_CHECKED_FAIL => 'red',
            self::ADMIN_CHECKED => 'blue',
            self::RESULTS_IMPORTED, self::PUBLISHED => 'purple',
        };
    }
}
