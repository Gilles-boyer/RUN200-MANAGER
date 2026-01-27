<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

/**
 * Exception levée lorsqu'un numéro de licence est déjà utilisé.
 */
class DuplicateLicenseNumberException extends DomainException
{
    public function __construct(string $licenseNumber, ?int $existingPilotId = null)
    {
        parent::__construct(
            message: "Le numéro de licence '{$licenseNumber}' est déjà utilisé".($existingPilotId ? " par le pilote #{$existingPilotId}" : ''),
            errorCode: 'PILOT_LICENSE_DUPLICATE',
            userMessageKey: 'exceptions.pilot.license_duplicate',
            context: [
                'license_number' => $licenseNumber,
                'existing_pilot_id' => $existingPilotId,
            ]
        );
    }

    public static function forLicense(string $licenseNumber, ?int $existingPilotId = null): self
    {
        return new self($licenseNumber, $existingPilotId);
    }
}
