<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

/**
 * Exception levée lorsqu'une entité demandée n'est pas trouvée.
 */
class EntityNotFoundException extends DomainException
{
    public function __construct(string $entityType, int|string $identifier)
    {
        parent::__construct(
            message: "{$entityType} avec l'identifiant '{$identifier}' introuvable",
            errorCode: 'ENTITY_NOT_FOUND',
            userMessageKey: 'exceptions.entity.not_found',
            context: [
                'entity_type' => $entityType,
                'identifier' => $identifier,
            ]
        );
    }

    public static function pilot(int $id): self
    {
        return new self('Pilote', $id);
    }

    public static function car(int $id): self
    {
        return new self('Voiture', $id);
    }

    public static function race(int $id): self
    {
        return new self('Course', $id);
    }

    public static function registration(int $id): self
    {
        return new self('Inscription', $id);
    }

    public static function season(int $id): self
    {
        return new self('Saison', $id);
    }

    public static function checkpoint(int $id): self
    {
        return new self('Checkpoint', $id);
    }

    public static function user(int $id): self
    {
        return new self('Utilisateur', $id);
    }
}
