<?php

declare(strict_types=1);

namespace App\Domain\Payment\Enums;

/**
 * Payment statuses.
 */
enum PaymentStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case PAID = 'paid';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';
    case PARTIALLY_REFUNDED = 'partially_refunded';
    case CANCELLED = 'cancelled';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::PROCESSING => 'En cours',
            self::PAID => 'Payé',
            self::FAILED => 'Échoué',
            self::REFUNDED => 'Remboursé',
            self::PARTIALLY_REFUNDED => 'Partiellement remboursé',
            self::CANCELLED => 'Annulé',
        };
    }

    /**
     * Get badge color for UI.
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::PENDING => 'yellow',
            self::PROCESSING => 'blue',
            self::PAID => 'green',
            self::FAILED => 'red',
            self::REFUNDED, self::PARTIALLY_REFUNDED => 'purple',
            self::CANCELLED => 'gray',
        };
    }

    /**
     * Check if payment is completed (successful).
     */
    public function isCompleted(): bool
    {
        return $this === self::PAID;
    }

    /**
     * Check if payment can be refunded.
     */
    public function canBeRefunded(): bool
    {
        return in_array($this, [self::PAID, self::PARTIALLY_REFUNDED]);
    }

    /**
     * Get all values as array.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
