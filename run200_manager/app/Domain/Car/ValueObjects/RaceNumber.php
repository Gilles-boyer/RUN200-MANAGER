<?php

namespace App\Domain\Car\ValueObjects;

use InvalidArgumentException;

final class RaceNumber
{
    private const MIN_VALUE = 0;

    private const MAX_VALUE = 999;

    private function __construct(
        private readonly int $value
    ) {}

    /**
     * Create a RaceNumber from integer
     *
     * @throws InvalidArgumentException
     */
    public static function fromInt(int $value): self
    {
        if ($value < self::MIN_VALUE || $value > self::MAX_VALUE) {
            throw new InvalidArgumentException(
                sprintf(
                    'Le numéro de course doit être entre %d et %d. Valeur reçue: %d',
                    self::MIN_VALUE,
                    self::MAX_VALUE,
                    $value
                )
            );
        }

        return new self($value);
    }

    /**
     * Convert to integer
     */
    public function toInt(): int
    {
        return $this->value;
    }

    /**
     * Convert to string
     */
    public function toString(): string
    {
        return (string) $this->value;
    }

    /**
     * Check equality
     */
    public function equals(RaceNumber $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Magic method for string conversion
     */
    public function __toString(): string
    {
        return (string) $this->value;
    }

    /**
     * Get minimum allowed value
     */
    public static function min(): int
    {
        return self::MIN_VALUE;
    }

    /**
     * Get maximum allowed value
     */
    public static function max(): int
    {
        return self::MAX_VALUE;
    }
}
