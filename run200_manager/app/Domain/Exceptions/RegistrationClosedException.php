<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

/**
 * Exception levée lorsqu'une course est fermée aux inscriptions.
 */
class RegistrationClosedException extends DomainException
{
    public function __construct(int $raceId, string $raceName, string $status)
    {
        parent::__construct(
            message: "Les inscriptions pour la course '{$raceName}' sont fermées (statut: {$status})",
            errorCode: 'REGISTRATION_CLOSED',
            userMessageKey: 'exceptions.registration.closed',
            context: [
                'race_id' => $raceId,
                'race_name' => $raceName,
                'status' => $status,
            ]
        );
    }

    public static function forRace(int $raceId, string $raceName, string $status): self
    {
        return new self($raceId, $raceName, $status);
    }
}
