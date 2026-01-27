<?php

namespace App\Domain\Registration\Enums;

enum RaceStatus: string
{
    case DRAFT = 'DRAFT';
    case OPEN = 'OPEN';
    case CLOSED = 'CLOSED';
    case RUNNING = 'RUNNING';
    case RESULTS_READY = 'RESULTS_READY';
    case PUBLISHED = 'PUBLISHED';
    case ARCHIVED = 'ARCHIVED';

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
            self::DRAFT => 'Brouillon',
            self::OPEN => 'Inscriptions ouvertes',
            self::CLOSED => 'Inscriptions fermées',
            self::RUNNING => 'En cours',
            self::RESULTS_READY => 'Résultats prêts',
            self::PUBLISHED => 'Résultats publiés',
            self::ARCHIVED => 'Archivée',
        };
    }

    /**
     * Check if registrations are allowed
     */
    public function allowsRegistrations(): bool
    {
        return $this === self::OPEN;
    }

    /**
     * Check if results can be imported
     */
    public function allowsResultsImport(): bool
    {
        return in_array($this, [self::CLOSED, self::RUNNING]);
    }

    /**
     * Check if results can be published
     */
    public function allowsResultsPublication(): bool
    {
        return $this === self::RESULTS_READY;
    }
}
