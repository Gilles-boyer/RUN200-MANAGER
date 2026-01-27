<?php

declare(strict_types=1);

namespace App\Domain\Payment\Enums;

/**
 * Payment methods supported by the application.
 */
enum PaymentMethod: string
{
    case MANUAL = 'manual';
    case STRIPE = 'stripe';
    case CASH = 'cash';
    case BANK_TRANSFER = 'bank_transfer';
    case CARD_ONSITE = 'card_onsite';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::MANUAL => 'Paiement manuel',
            self::STRIPE => 'Paiement en ligne (Carte)',
            self::CASH => 'Espèces',
            self::BANK_TRANSFER => 'Virement bancaire',
            self::CARD_ONSITE => 'CB sur place',
        };
    }

    /**
     * Get badge color for UI.
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::MANUAL => 'gray',
            self::STRIPE => 'blue',
            self::CASH => 'green',
            self::BANK_TRANSFER => 'purple',
            self::CARD_ONSITE => 'indigo',
        };
    }

    /**
     * Get all values as array.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get on-site payment methods (for walk-in registrations).
     */
    public static function onsiteMethods(): array
    {
        return [
            self::CASH,
            self::BANK_TRANSFER,
            self::CARD_ONSITE,
        ];
    }
}
