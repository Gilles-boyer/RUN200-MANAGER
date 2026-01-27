<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

/**
 * Exception levée lorsqu'un numéro de course est déjà pris.
 */
class RaceNumberAlreadyTakenException extends DomainException
{
    public function __construct(int $raceNumber, ?int $existingCarId = null)
    {
        parent::__construct(
            message: "Le numéro de course #{$raceNumber} est déjà attribué".($existingCarId ? " à la voiture #{$existingCarId}" : ''),
            errorCode: 'CAR_RACE_NUMBER_TAKEN',
            userMessageKey: 'exceptions.car.race_number_taken',
            context: [
                'race_number' => $raceNumber,
                'existing_car_id' => $existingCarId,
            ]
        );
    }

    public static function forNumber(int $raceNumber, ?int $existingCarId = null): self
    {
        return new self($raceNumber, $existingCarId);
    }
}
