<?php

declare(strict_types=1);

use App\Domain\Championship\Rules\PointsTable;
use App\Domain\Championship\Rules\StandingsRules;

describe('PointsTable Domain Rules', function () {

    it('returns correct default points for position 1', function () {
        expect(PointsTable::getDefaultPoints(1))->toBe(25);
    });

    it('returns correct default points for position 2', function () {
        expect(PointsTable::getDefaultPoints(2))->toBe(20);
    });

    it('returns correct default points for position 3', function () {
        expect(PointsTable::getDefaultPoints(3))->toBe(16);
    });

    it('returns correct default points for position 4', function () {
        expect(PointsTable::getDefaultPoints(4))->toBe(14);
    });

    it('returns correct default points for position 5', function () {
        expect(PointsTable::getDefaultPoints(5))->toBe(10);
    });

    it('returns correct default points for position 6', function () {
        expect(PointsTable::getDefaultPoints(6))->toBe(8);
    });

    it('returns 5 points for positions 7 and beyond', function () {
        expect(PointsTable::getDefaultPoints(7))->toBe(5);
        expect(PointsTable::getDefaultPoints(10))->toBe(5);
        expect(PointsTable::getDefaultPoints(100))->toBe(5);
    });

    it('returns 0 for invalid positions', function () {
        expect(PointsTable::getDefaultPoints(0))->toBe(0);
        expect(PointsTable::getDefaultPoints(-1))->toBe(0);
    });

    it('generates correct rules for seeding', function () {
        $rules = PointsTable::getDefaultRulesForSeeding();

        expect($rules)->toHaveCount(7); // 6 specific + 1 for "others"

        // First rule
        expect($rules[0])->toBe([
            'position_from' => 1,
            'position_to' => 1,
            'points' => 25,
        ]);

        // Last rule (others)
        expect($rules[6])->toBe([
            'position_from' => 7,
            'position_to' => 9999,
            'points' => 5,
        ]);
    });
});

describe('StandingsRules Domain Rules', function () {

    it('requires minimum 2 races for ranking', function () {
        expect(StandingsRules::MIN_RACES_REQUIRED)->toBe(2);
    });

    it('has bonus of 20 points for all races', function () {
        expect(StandingsRules::BONUS_ALL_RACES)->toBe(20);
    });

    it('marks pilot with 0 races as not eligible', function () {
        expect(StandingsRules::isEligibleForRanking(0))->toBeFalse();
    });

    it('marks pilot with 1 race as not eligible', function () {
        expect(StandingsRules::isEligibleForRanking(1))->toBeFalse();
    });

    it('marks pilot with 2 races as eligible', function () {
        expect(StandingsRules::isEligibleForRanking(2))->toBeTrue();
    });

    it('marks pilot with more than 2 races as eligible', function () {
        expect(StandingsRules::isEligibleForRanking(5))->toBeTrue();
    });

    it('returns no bonus when season has no races', function () {
        expect(StandingsRules::calculateBonus(0, 0))->toBe(0);
    });

    it('returns no bonus when pilot did not participate in all races', function () {
        expect(StandingsRules::calculateBonus(2, 3))->toBe(0);
        expect(StandingsRules::calculateBonus(4, 5))->toBe(0);
    });

    it('returns bonus when pilot participated in all races', function () {
        expect(StandingsRules::calculateBonus(3, 3))->toBe(20);
        expect(StandingsRules::calculateBonus(5, 5))->toBe(20);
    });

    it('returns bonus when pilot has more participations than total (edge case)', function () {
        // This shouldn't happen but handles edge case
        expect(StandingsRules::calculateBonus(6, 5))->toBe(20);
    });

    it('calculates total points correctly', function () {
        expect(StandingsRules::calculateTotalPoints(100, 0))->toBe(100);
        expect(StandingsRules::calculateTotalPoints(100, 20))->toBe(120);
        expect(StandingsRules::calculateTotalPoints(0, 20))->toBe(20);
    });

    it('provides correct ranking status label for eligible pilots', function () {
        $label = StandingsRules::getRankingStatusLabel(2);
        expect($label)->toBe('Classé');

        $label = StandingsRules::getRankingStatusLabel(5);
        expect($label)->toBe('Classé');
    });

    it('provides correct ranking status label for ineligible pilots', function () {
        $label = StandingsRules::getRankingStatusLabel(0);
        expect($label)->toContain('Non classé')
            ->toContain('2 courses manquantes');

        $label = StandingsRules::getRankingStatusLabel(1);
        expect($label)->toContain('Non classé')
            ->toContain('1 course manquante');
    });

    it('provides correct bonus status label when bonus earned', function () {
        $label = StandingsRules::getBonusStatusLabel(5, 5);
        expect($label)->toContain('Bonus')
            ->toContain('+20');
    });

    it('provides correct bonus status label when bonus not earned', function () {
        $label = StandingsRules::getBonusStatusLabel(3, 5);
        expect($label)->toContain('2 courses manquantes');

        $label = StandingsRules::getBonusStatusLabel(4, 5);
        expect($label)->toContain('1 course manquante');
    });
});
