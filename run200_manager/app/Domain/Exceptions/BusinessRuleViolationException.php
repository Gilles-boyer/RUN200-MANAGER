<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

/**
 * Exception levée lors d'une violation de règle métier.
 */
class BusinessRuleViolationException extends DomainException
{
    public function __construct(string $rule, string $message, array $context = [])
    {
        parent::__construct(
            message: $message,
            errorCode: 'BUSINESS_RULE_VIOLATION',
            userMessageKey: "exceptions.business.{$rule}",
            context: array_merge(['rule' => $rule], $context)
        );
    }

    public static function maxCarsPerPilot(int $currentCount, int $maxAllowed): self
    {
        return new self(
            rule: 'max_cars_per_pilot',
            message: "Un pilote ne peut pas avoir plus de {$maxAllowed} voitures (actuellement: {$currentCount})",
            context: ['current_count' => $currentCount, 'max_allowed' => $maxAllowed]
        );
    }

    public static function raceCapacityReached(int $currentCount, int $maxCapacity): self
    {
        return new self(
            rule: 'race_capacity_reached',
            message: "La course est complète ({$currentCount}/{$maxCapacity} inscriptions)",
            context: ['current_count' => $currentCount, 'max_capacity' => $maxCapacity]
        );
    }

    public static function seasonNotActive(string $seasonName): self
    {
        return new self(
            rule: 'season_not_active',
            message: "La saison '{$seasonName}' n'est pas active",
            context: ['season_name' => $seasonName]
        );
    }

    public static function invalidStatusTransition(string $from, string $to): self
    {
        return new self(
            rule: 'invalid_status_transition',
            message: "Transition de statut invalide: {$from} → {$to}",
            context: ['from' => $from, 'to' => $to]
        );
    }

    public static function registrationDeadlinePassed(string $deadline): self
    {
        return new self(
            rule: 'registration_deadline_passed',
            message: "La date limite d'inscription est dépassée ({$deadline})",
            context: ['deadline' => $deadline]
        );
    }

    public static function pilotNotVerified(): self
    {
        return new self(
            rule: 'pilot_not_verified',
            message: "Le profil pilote doit être vérifié avant de pouvoir s'inscrire"
        );
    }

    public static function carNotApproved(): self
    {
        return new self(
            rule: 'car_not_approved',
            message: 'La voiture doit être approuvée avant de pouvoir être inscrite'
        );
    }
}
