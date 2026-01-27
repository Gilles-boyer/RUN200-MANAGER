<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

/**
 * Exception levée lorsqu'une voiture est déjà inscrite à une course.
 */
class CarAlreadyRegisteredException extends DomainException
{
    public function __construct(int $carId, int $raceId, string $carName, string $raceName)
    {
        parent::__construct(
            message: "La voiture '{$carName}' est déjà inscrite à la course '{$raceName}'",
            errorCode: 'REGISTRATION_CAR_DUPLICATE',
            userMessageKey: 'exceptions.registration.car_already_registered',
            context: [
                'car_id' => $carId,
                'race_id' => $raceId,
                'car_name' => $carName,
                'race_name' => $raceName,
            ]
        );
    }

    public static function forCarAndRace(int $carId, int $raceId, string $carName, string $raceName): self
    {
        return new self($carId, $raceId, $carName, $raceName);
    }
}
