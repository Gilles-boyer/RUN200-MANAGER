<?php

namespace App\Domain\Pilot\ValueObjects;

use InvalidArgumentException;

final class LicenseNumber
{
    private function __construct(
        private readonly string $value
    ) {}

    /**
     * Create a LicenseNumber from string
     *
     * @throws InvalidArgumentException
     */
    public static function fromString(string $value): self
    {
        $value = trim($value);

        if (! ctype_digit($value)) {
            throw new InvalidArgumentException(
                'Le numéro de licence doit contenir uniquement des chiffres.'
            );
        }

        $length = strlen($value);
        if ($length === 0 || $length > 6) {
            throw new InvalidArgumentException(
                'Le numéro de licence doit contenir entre 1 et 6 chiffres.'
            );
        }

        return new self($value);
    }

    /**
     * Convert to string
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * Check equality
     */
    public function equals(LicenseNumber $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Magic method for string conversion
     */
    public function __toString(): string
    {
        return $this->value;
    }
}
