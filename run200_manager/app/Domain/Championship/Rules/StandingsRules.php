<?php

declare(strict_types=1);

namespace App\Domain\Championship\Rules;

/**
 * Defines the rules for championship standings calculation.
 *
 * Rules:
 * - Minimum 2 races to be ranked
 * - Bonus +20 points if participated in ALL races of the season
 */
final class StandingsRules
{
    /**
     * Minimum number of races required to be ranked
     */
    public const MIN_RACES_REQUIRED = 2;

    /**
     * Bonus points for participating in all races
     */
    public const BONUS_ALL_RACES = 20;

    /**
     * Check if a pilot is eligible for ranking
     */
    public static function isEligibleForRanking(int $racesCount): bool
    {
        return $racesCount >= self::MIN_RACES_REQUIRED;
    }

    /**
     * Calculate bonus points
     *
     * @param  int  $pilotRacesCount  Number of races the pilot participated in
     * @param  int  $totalSeasonRaces  Total number of races in the season
     * @return int Bonus points (0 or BONUS_ALL_RACES)
     */
    public static function calculateBonus(int $pilotRacesCount, int $totalSeasonRaces): int
    {
        // No bonus if season has no races
        if ($totalSeasonRaces === 0) {
            return 0;
        }

        // Bonus only if pilot participated in ALL races
        if ($pilotRacesCount >= $totalSeasonRaces) {
            return self::BONUS_ALL_RACES;
        }

        return 0;
    }

    /**
     * Calculate total points including bonus
     */
    public static function calculateTotalPoints(int $basePoints, int $bonusPoints): int
    {
        return $basePoints + $bonusPoints;
    }

    /**
     * Get ranking status label
     */
    public static function getRankingStatusLabel(int $racesCount): string
    {
        if (self::isEligibleForRanking($racesCount)) {
            return 'Classé';
        }

        $remaining = self::MIN_RACES_REQUIRED - $racesCount;
        if ($remaining === 1) {
            return 'Non classé (1 course manquante)';
        }

        return "Non classé ({$remaining} courses manquantes)";
    }

    /**
     * Get bonus status label
     */
    public static function getBonusStatusLabel(int $pilotRacesCount, int $totalSeasonRaces): string
    {
        if ($totalSeasonRaces === 0) {
            return 'Aucune course';
        }

        if ($pilotRacesCount >= $totalSeasonRaces) {
            return 'Bonus +'.self::BONUS_ALL_RACES.' pts (toutes courses)';
        }

        $remaining = $totalSeasonRaces - $pilotRacesCount;
        if ($remaining === 1) {
            return '1 course manquante pour le bonus';
        }

        return "{$remaining} courses manquantes pour le bonus";
    }
}
