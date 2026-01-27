<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

/**
 * Exception levée lorsqu'un pilote est déjà inscrit à une course.
 */
class PilotAlreadyRegisteredException extends DomainException
{
    public function __construct(int $pilotId, int $raceId, string $pilotName, string $raceName)
    {
        parent::__construct(
            message: "Le pilote '{$pilotName}' est déjà inscrit à la course '{$raceName}'",
            errorCode: 'REGISTRATION_PILOT_DUPLICATE',
            userMessageKey: 'exceptions.registration.pilot_already_registered',
            context: [
                'pilot_id' => $pilotId,
                'race_id' => $raceId,
                'pilot_name' => $pilotName,
                'race_name' => $raceName,
            ]
        );
    }

    public static function forPilotAndRace(int $pilotId, int $raceId, string $pilotName, string $raceName): self
    {
        return new self($pilotId, $raceId, $pilotName, $raceName);
    }
}
