<?php

declare(strict_types=1);

namespace App\Application\Championship\UseCases;

use App\Domain\Championship\Rules\PointsTable;
use App\Domain\Championship\Rules\StandingsRules;
use App\Models\Race;
use App\Models\Season;
use App\Models\SeasonCategoryStanding;
use App\Models\SeasonPointsRule;
use App\Models\SeasonStanding;
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
    public function execute(Season $season, ?\App\Models\User $triggeredBy = null): array
    {
        return DB::transaction(function () use ($season, $triggeredBy) {
            // Get published races for this season
            $publishedRaces = $season->races()
                ->where('status', 'PUBLISHED')
                ->with('results.registration.pilot', 'results.registration.car.category')
                ->get();

            $totalRacesInSeason = $publishedRaces->count();

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
            $generalStandings = $this->buildGeneralStandings($season, $pilotResults, $totalRacesInSeason);

            // Build category standings
            $categoryStandings = $this->buildCategoryStandings($season, $categoryResults, $totalRacesInSeason);

            // Log activity
            if ($triggeredBy) {
                $this->logActivity($season, $triggeredBy, $generalStandings, $categoryStandings);
            }

            return [
                'season_id' => $season->id,
                'total_races' => $totalRacesInSeason,
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
     * Returns: [pilot_id => ['races_count' => X, 'base_points' => Y]]
     */
    private function collectPilotResults(Collection $races, Collection $pointsRules): array
    {
        $pilotResults = [];

        foreach ($races as $race) {
            foreach ($race->results as $result) {
                $registration = $result->registration;
                if (! $registration || ! $registration->pilot_id) {
                    continue;
                }

                $pilotId = $registration->pilot_id;
                $points = $this->getPointsForPosition($result->position, $pointsRules);

                if (! isset($pilotResults[$pilotId])) {
                    $pilotResults[$pilotId] = [
                        'races_count' => 0,
                        'base_points' => 0,
                    ];
                }

                $pilotResults[$pilotId]['races_count']++;
                $pilotResults[$pilotId]['base_points'] += $points;
            }
        }

        return $pilotResults;
    }

    /**
     * Collect results by category.
     * Returns: [category_id => [pilot_id => ['races_count' => X, 'base_points' => Y]]]
     */
    private function collectCategoryResults(Collection $races, Collection $pointsRules): array
    {
        $categoryResults = [];

        foreach ($races as $race) {
            // Group results by category for this race
            $resultsByCategory = $race->results->groupBy(function ($result) {
                $registration = $result->registration;
                if (! $registration || ! $registration->car) {
                    return null;
                }

                return $registration->car->car_category_id;
            })->filter(fn ($group, $key) => $key !== null);

            foreach ($resultsByCategory as $categoryId => $results) {
                // Recompute positions within category
                $categoryPosition = 1;
                $sortedResults = $results->sortBy('position');

                foreach ($sortedResults as $result) {
                    $registration = $result->registration;
                    if (! $registration || ! $registration->pilot_id) {
                        continue;
                    }

                    $pilotId = $registration->pilot_id;
                    $points = $this->getPointsForPosition($categoryPosition, $pointsRules);

                    if (! isset($categoryResults[$categoryId])) {
                        $categoryResults[$categoryId] = [];
                    }

                    if (! isset($categoryResults[$categoryId][$pilotId])) {
                        $categoryResults[$categoryId][$pilotId] = [
                            'races_count' => 0,
                            'base_points' => 0,
                        ];
                    }

                    $categoryResults[$categoryId][$pilotId]['races_count']++;
                    $categoryResults[$categoryId][$pilotId]['base_points'] += $points;

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
                'is_eligible' => StandingsRules::isEligibleForRanking($data['races_count']),
            ];
        }

        // Sort by total points descending
        usort($standings, fn ($a, $b) => $b['total_points'] <=> $a['total_points']);

        // Assign ranks only to eligible pilots
        $rank = 1;
        foreach ($standings as &$standing) {
            $standing['rank'] = $standing['is_eligible'] ? $rank++ : null;

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
                    'is_eligible' => StandingsRules::isEligibleForRanking($data['races_count']),
                ];
            }

            // Sort by total points descending
            usort($categoryStandings, fn ($a, $b) => $b['total_points'] <=> $a['total_points']);

            // Assign ranks only to eligible pilots
            $rank = 1;
            foreach ($categoryStandings as &$standing) {
                $standing['rank'] = $standing['is_eligible'] ? $rank++ : null;

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

    /**
     * Log the rebuild activity.
     */
    private function logActivity(Season $season, \App\Models\User $user, array $generalStandings, array $categoryStandings): void
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
