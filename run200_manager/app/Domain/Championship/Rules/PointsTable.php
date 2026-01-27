<?php

declare(strict_types=1);

namespace App\Domain\Championship\Rules;

/**
 * Defines the points table for championship standings.
 *
 * Default barème:
 * - 1er: 25 points
 * - 2ème: 20 points
 * - 3ème: 16 points
 * - 4ème: 14 points
 * - 5ème: 10 points
 * - 6ème: 8 points
 * - 7ème et +: 5 points
 */
final class PointsTable
{
    /**
     * Default points by position
     */
    private const DEFAULT_POINTS = [
        1 => 25,
        2 => 20,
        3 => 16,
        4 => 14,
        5 => 10,
        6 => 8,
    ];

    /**
     * Default points for positions 7 and beyond
     */
    public const DEFAULT_POINTS_OTHER = 5;

    /**
     * Get points for a given position using default rules
     */
    public static function getDefaultPoints(int $position): int
    {
        if ($position < 1) {
            return 0;
        }

        return self::DEFAULT_POINTS[$position] ?? self::DEFAULT_POINTS_OTHER;
    }

    /**
     * Get the default points rules as an array suitable for seeding
     * Format: [['position_from' => X, 'position_to' => Y, 'points' => Z], ...]
     */
    public static function getDefaultRulesForSeeding(): array
    {
        $rules = [];

        foreach (self::DEFAULT_POINTS as $position => $points) {
            $rules[] = [
                'position_from' => $position,
                'position_to' => $position,
                'points' => $points,
            ];
        }

        // Rule for positions 7 and beyond (up to 9999)
        $rules[] = [
            'position_from' => 7,
            'position_to' => 9999,
            'points' => self::DEFAULT_POINTS_OTHER,
        ];

        return $rules;
    }

    /**
     * Get all default positions with their points
     */
    public static function getAllDefaultPoints(): array
    {
        return self::DEFAULT_POINTS;
    }

    /**
     * Get the maximum position that has specific points (not "other")
     */
    public static function getLastSpecificPosition(): int
    {
        return max(array_keys(self::DEFAULT_POINTS));
    }
}
