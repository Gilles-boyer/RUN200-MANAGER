<?php

declare(strict_types=1);

namespace App\Application\Championship\UseCases;

use App\Domain\Championship\Rules\PointsTable;
use App\Domain\Championship\Rules\StandingsRules;
use App\Domain\Registration\Enums\RaceStatus;
use App\Models\Season;
use App\Models\SeasonCategoryStanding;
use App\Models\SeasonPointsRule;
use App\Models\SeasonStanding;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Facades\Activity;

/**
 * Use case for rebuilding season standings (general + by category).
 *
 * This recalculates all standings based on published race results.
 */
final class RebuildSeasonStandings
{
    /**
     * Execute the standings rebuild for a season.
     */
    public function execute(Season $season, ?User $triggeredBy = null): array
    {
        return DB::transaction(function () use ($season, $triggeredBy) {
            // Get published races for this season (PUBLISHED or COMPLETED status)
            $publishedRaces = $season->races()
                ->whereIn('status', RaceStatus::publishedResultsStatuses())
                ->with('results.registration.pilot')
                ->get();

            $totalRacesInSeason = $season->races()->where('status', '!=', RaceStatus::CANCELLED->value)->count();
            $seasonFinished = $season->end_date->isBefore(today())
                && $publishedRaces->count() === $totalRacesInSeason;

            // Load points rules (or use defaults)
            $pointsRules = $this->getPointsRules($season);

            // Collect all results grouped by pilot
            $pilotResults = $this->collectPilotResults($publishedRaces, $pointsRules);

            // Collect results by category
            $categoryResults = $this->collectCategoryResults($publishedRaces, $pointsRules);

            // Clear existing standings
            $season->standings()->delete();
            $season->categoryStandings()->delete();

            // Build general standings
            $generalStandings = $this->buildGeneralStandings($season, $pilotResults, $seasonFinished ? $totalRacesInSeason : 0);

            // Build category standings
            $categoryStandings = $this->buildCategoryStandings($season, $categoryResults, $seasonFinished ? $totalRacesInSeason : 0);

            // Log activity
            if ($triggeredBy) {
                $this->logActivity($season, $triggeredBy, $generalStandings, $categoryStandings);
            }

            return [
                'season_id' => $season->id,
                'total_races' => $publishedRaces->count(),
                'general_standings_count' => count($generalStandings),
                'category_standings_count' => count($categoryStandings),
                'ranked_pilots' => collect($generalStandings)->whereNotNull('rank')->count(),
            ];
        });
    }

    /**
     * Get points rules for the season (or create defaults).
     */
    private function getPointsRules(Season $season): Collection
    {
        $rules = $season->pointsRules;

        if ($rules->isEmpty()) {
            // Create default rules for this season
            foreach (PointsTable::getDefaultRulesForSeeding() as $rule) {
                SeasonPointsRule::create([
                    'season_id' => $season->id,
                    'position_from' => $rule['position_from'],
                    'position_to' => $rule['position_to'],
                    'points' => $rule['points'],
                ]);
            }

            $rules = $season->pointsRules()->get();
        }

        return $rules;
    }

    /**
     * Get points for a position using the rules.
     */
    private function getPointsForPosition(int $position, Collection $rules): int
    {
        foreach ($rules as $rule) {
            if ($position >= $rule->position_from && $position <= $rule->position_to) {
                return $rule->points;
            }
        }

        return PointsTable::DEFAULT_POINTS_OTHER;
    }

    /**
     * Collect all pilot results from published races.
     * For each race, only the best result (lowest position) per pilot is counted.
     * Returns: [pilot_id => ['races_count' => X, 'base_points' => Y]]
     */
    private function collectPilotResults(Collection $races, Collection $pointsRules): array
    {
        $pilotResults = [];

        foreach ($races as $race) {
            // Group results by pilot to find best result per pilot per race
            $resultsByPilot = [];

            foreach ($race->results as $result) {
                if ($result->excluded_from_championship) {
                    continue;
                }
                $registration = $result->registration;
                if (! $registration || ! $registration->pilot_id) {
                    continue;
                }

                $pilotId = $registration->pilot_id;

                // Keep only the best position (lowest number) for this pilot in this race
                if (! isset($resultsByPilot[$pilotId]) || $result->position < $resultsByPilot[$pilotId]->position) {
                    $resultsByPilot[$pilotId] = $result;
                }
            }

            // Now process only the best result per pilot
            foreach ($resultsByPilot as $pilotId => $result) {
                $points = $this->getPointsForPosition($result->position, $pointsRules);

                if (! isset($pilotResults[$pilotId])) {
                    $pilotResults[$pilotId] = [
                        'races_count' => 0,
                        'base_points' => 0,
                        'best_position' => PHP_INT_MAX,
                    ];
                }

                $pilotResults[$pilotId]['races_count']++;
                $pilotResults[$pilotId]['base_points'] += $points;
                $pilotResults[$pilotId]['best_position'] = min($pilotResults[$pilotId]['best_position'], $result->position);
            }
        }

        return $pilotResults;
    }

    /**
     * Collect results by category.
     * For each race and category, only the best result per pilot is counted.
     * Returns: [category_id => [pilot_id => ['races_count' => X, 'base_points' => Y]]]
     */
    private function collectCategoryResults(Collection $races, Collection $pointsRules): array
    {
        $categoryResults = [];

        foreach ($races as $race) {
            // Group results by category for this race
            $resultsByCategory = $race->results->groupBy(function ($result) {
                if ($result->excluded_from_championship) {
                    return null;
                }
                $registration = $result->registration;
                if (! $registration || ! $registration->car_category_id) {
                    return null;
                }

                return $registration->car_category_id;
            })->filter(fn ($group, $key) => $key !== null);

            foreach ($resultsByCategory as $categoryId => $results) {
                // First, find the best result per pilot in this category for this race
                $bestResultsByPilot = [];
                foreach ($results as $result) {
                    $registration = $result->registration;
                    if (! $registration || ! $registration->pilot_id) {
                        continue;
                    }

                    $pilotId = $registration->pilot_id;

                    // Keep only the best position (lowest number) for this pilot
                    if (! isset($bestResultsByPilot[$pilotId]) || $result->position < $bestResultsByPilot[$pilotId]->position) {
                        $bestResultsByPilot[$pilotId] = $result;
                    }
                }

                // Sort by position and assign category positions
                $sortedResults = collect($bestResultsByPilot)->sortBy('position');
                $categoryPosition = 1;

                foreach ($sortedResults as $pilotId => $result) {
                    $points = $this->getPointsForPosition($categoryPosition, $pointsRules);

                    if (! isset($categoryResults[$categoryId])) {
                        $categoryResults[$categoryId] = [];
                    }

                    if (! isset($categoryResults[$categoryId][$pilotId])) {
                        $categoryResults[$categoryId][$pilotId] = [
                            'races_count' => 0,
                            'base_points' => 0,
                            'best_position' => PHP_INT_MAX,
                        ];
                    }

                    $categoryResults[$categoryId][$pilotId]['races_count']++;
                    $categoryResults[$categoryId][$pilotId]['base_points'] += $points;
                    $categoryResults[$categoryId][$pilotId]['best_position'] = min($categoryResults[$categoryId][$pilotId]['best_position'], $categoryPosition);

                    $categoryPosition++;
                }
            }
        }

        return $categoryResults;
    }

    /**
     * Build and persist general standings.
     */
    private function buildGeneralStandings(Season $season, array $pilotResults, int $totalRaces): array
    {
        $standings = [];
        $now = now();

        foreach ($pilotResults as $pilotId => $data) {
            $bonusPoints = StandingsRules::calculateBonus($data['races_count'], $totalRaces);
            $totalPoints = StandingsRules::calculateTotalPoints($data['base_points'], $bonusPoints);

            $standings[] = [
                'pilot_id' => $pilotId,
                'races_count' => $data['races_count'],
                'base_points' => $data['base_points'],
                'bonus_points' => $bonusPoints,
                'total_points' => $totalPoints,
                'best_position' => $data['best_position'],
                'is_eligible' => StandingsRules::isEligibleForRanking($data['races_count']),
            ];
        }

        $this->sortStandings($standings);

        // Assign ranks only to eligible pilots
        $rank = 0;
        $lastEligible = null;
        $eligiblePosition = 0;
        foreach ($standings as &$standing) {
            $standing['rank'] = $this->rankFor($standing, $lastEligible, $rank, $eligiblePosition);

            // Persist
            SeasonStanding::create([
                'season_id' => $season->id,
                'pilot_id' => $standing['pilot_id'],
                'races_count' => $standing['races_count'],
                'base_points' => $standing['base_points'],
                'bonus_points' => $standing['bonus_points'],
                'total_points' => $standing['total_points'],
                'rank' => $standing['rank'],
                'computed_at' => $now,
            ]);
        }

        return $standings;
    }

    /**
     * Build and persist category standings.
     */
    private function buildCategoryStandings(Season $season, array $categoryResults, int $totalRaces): array
    {
        $allCategoryStandings = [];
        $now = now();

        foreach ($categoryResults as $categoryId => $pilots) {
            $categoryStandings = [];

            foreach ($pilots as $pilotId => $data) {
                $bonusPoints = StandingsRules::calculateBonus($data['races_count'], $totalRaces);
                $totalPoints = StandingsRules::calculateTotalPoints($data['base_points'], $bonusPoints);

                $categoryStandings[] = [
                    'category_id' => $categoryId,
                    'pilot_id' => $pilotId,
                    'races_count' => $data['races_count'],
                    'base_points' => $data['base_points'],
                    'bonus_points' => $bonusPoints,
                    'total_points' => $totalPoints,
                    'best_position' => $data['best_position'],
                    'is_eligible' => StandingsRules::isEligibleForRanking($data['races_count']),
                ];
            }

            $this->sortStandings($categoryStandings);

            // Assign ranks only to eligible pilots
            $rank = 0;
            $lastEligible = null;
            $eligiblePosition = 0;
            foreach ($categoryStandings as &$standing) {
                $standing['rank'] = $this->rankFor($standing, $lastEligible, $rank, $eligiblePosition);

                // Persist
                SeasonCategoryStanding::create([
                    'season_id' => $season->id,
                    'car_category_id' => $standing['category_id'],
                    'pilot_id' => $standing['pilot_id'],
                    'races_count' => $standing['races_count'],
                    'base_points' => $standing['base_points'],
                    'bonus_points' => $standing['bonus_points'],
                    'total_points' => $standing['total_points'],
                    'rank' => $standing['rank'],
                    'computed_at' => $now,
                ]);

                $allCategoryStandings[] = $standing;
            }
        }

        return $allCategoryStandings;
    }

    private function sortStandings(array &$standings): void
    {
        usort($standings, fn ($a, $b) => ($b['total_points'] <=> $a['total_points'])
            ?: ($b['races_count'] <=> $a['races_count'])
            ?: ($a['best_position'] <=> $b['best_position'])
            ?: ($a['pilot_id'] <=> $b['pilot_id']));
    }

    private function rankFor(array $standing, ?array &$lastEligible, int &$rank, int &$eligiblePosition): ?int
    {
        if (! $standing['is_eligible']) {
            return null;
        }

        $eligiblePosition++;
        if ($lastEligible === null
            || $standing['total_points'] !== $lastEligible['total_points']
            || $standing['races_count'] !== $lastEligible['races_count']
            || $standing['best_position'] !== $lastEligible['best_position']) {
            $rank = $eligiblePosition;
        }

        $lastEligible = $standing;

        return $rank;
    }

    /**
     * Log the rebuild activity.
     */
    private function logActivity(Season $season, User $user, array $generalStandings, array $categoryStandings): void
    {
        $rankedCount = collect($generalStandings)->whereNotNull('rank')->count();
        $topThree = collect($generalStandings)
            ->whereNotNull('rank')
            ->take(3)
            ->map(fn ($s) => "#{$s['rank']}: Pilot #{$s['pilot_id']} ({$s['total_points']} pts)")
            ->toArray();

        Activity::causedBy($user)
            ->performedOn($season)
            ->withProperties([
                'season_id' => $season->id,
                'season_name' => $season->name,
                'total_pilots' => count($generalStandings),
                'ranked_pilots' => $rankedCount,
                'top_three' => $topThree,
                'categories_count' => count(array_unique(array_column($categoryStandings, 'category_id'))),
            ])
            ->log('championship.rebuilt');
    }
}
