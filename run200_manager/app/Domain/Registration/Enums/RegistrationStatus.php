<?php

namespace App\Domain\Registration\Enums;

enum RegistrationStatus: string
{
    case SUBMITTED = 'SUBMITTED';
    case PENDING_PAYMENT = 'PENDING_PAYMENT';
    case PENDING_VALIDATION = 'PENDING_VALIDATION';
    case ACCEPTED = 'ACCEPTED';
    case REFUSED = 'REFUSED';
    case CANCELLED = 'CANCELLED';
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
            self::PENDING_PAYMENT => 'En attente de paiement',
            self::PENDING_VALIDATION => 'En attente de validation',
            self::ACCEPTED => 'Acceptée',
            self::REFUSED => 'Refusée',
            self::CANCELLED => 'Annulée',
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
            self::SUBMITTED, self::PENDING_PAYMENT, self::PENDING_VALIDATION => 'yellow',
            self::ACCEPTED, self::TECH_CHECKED_OK, self::ENTRY_SCANNED, self::BRACELET_GIVEN => 'green',
            self::REFUSED, self::TECH_CHECKED_FAIL => 'red',
            self::CANCELLED => 'gray',
            self::ADMIN_CHECKED => 'blue',
            self::RESULTS_IMPORTED, self::PUBLISHED => 'purple',
        };
    }

    public function nextStep(): string
    {
        return match ($this) {
            self::SUBMITTED, self::PENDING_VALIDATION => 'Le staff vérifie votre inscription.',
            self::PENDING_PAYMENT => 'Réglez votre inscription pour qu’elle soit examinée.',
            self::ACCEPTED => 'Consultez votre E-Card et préparez le contrôle administratif.',
            self::REFUSED => 'Consultez le motif du refus ; vous pouvez réactiver l’inscription si la course est ouverte.',
            self::CANCELLED => 'Vous pouvez réactiver l’inscription si la course est ouverte.',
            self::ADMIN_CHECKED => 'Présentez-vous au contrôle technique.',
            self::TECH_CHECKED_OK => 'Votre voiture est validée ; présentez votre E-Card à l’entrée.',
            self::TECH_CHECKED_FAIL => 'Contactez le staff pour corriger les points du contrôle technique.',
            self::ENTRY_SCANNED => 'Présentez-vous pour récupérer votre bracelet.',
            self::BRACELET_GIVEN => 'Votre accès est validé. Consultez les informations de la course.',
            self::RESULTS_IMPORTED => 'Les résultats sont en cours de vérification.',
            self::PUBLISHED => 'Consultez les résultats publiés.',
        };
    }
}
