<?php

declare(strict_types=1);

namespace App\Application\Results\UseCases;

use App\Models\Race;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Facades\Activity;

/**
 * Use case for publishing race results.
 *
 * Publishing makes results visible to pilots and triggers championship recalculation.
 */
final class PublishRaceResults
{
    /**
     * Publish race results.
     *
     * @throws \InvalidArgumentException If race cannot be published
     */
    public function execute(Race $race, User $publisher): Race
    {
        // Validate race can be published
        if (! $race->canPublishResults()) {
            throw new \InvalidArgumentException(
                "Seules les courses avec statut RESULTS_READY peuvent être publiées. Statut actuel: {$race->status}"
            );
        }

        // Verify race has results
        if ($race->results()->count() === 0) {
            throw new \InvalidArgumentException(
                "La course n'a pas de résultats à publier."
            );
        }

        return DB::transaction(function () use ($race, $publisher) {
            // Update race status to PUBLISHED
            $race->update(['status' => 'PUBLISHED']);

            // Log the activity
            $this->logActivity($race, $publisher);

            // Dispatch championship recalculation job if race is part of a championship
            $this->dispatchChampionshipRecalculation($race);

            $race->refresh();

            return $race;
        });
    }

    /**
     * Unpublish race results (revert to RESULTS_READY).
     *
     * @throws \InvalidArgumentException If race cannot be unpublished
     */
    public function unpublish(Race $race, User $user): Race
    {
        if ($race->status !== 'PUBLISHED') {
            throw new \InvalidArgumentException(
                "Seules les courses publiées peuvent être dépubliées. Statut actuel: {$race->status}"
            );
        }

        return DB::transaction(function () use ($race, $user) {
            $race->update(['status' => 'RESULTS_READY']);

            Activity::causedBy($user)
                ->performedOn($race)
                ->withProperties([
                    'race_id' => $race->id,
                    'race_name' => $race->name,
                ])
                ->log('results_unpublished');

            // Re-trigger championship recalculation to remove points
            $this->dispatchChampionshipRecalculation($race);

            $race->refresh();

            return $race;
        });
    }

    /**
     * Log publish activity.
     */
    private function logActivity(Race $race, User $publisher): void
    {
        $resultsCount = $race->results()->count();
        /** @var \Illuminate\Support\Collection<int, \App\Models\RaceResult> $topThreeResults */
        $topThreeResults = $race->results()
            ->orderBy('position')
            ->limit(3)
            ->get();
        $topThree = $topThreeResults
            ->map(fn (\App\Models\RaceResult $r) => "{$r->position}. {$r->pilot_name}")
            ->toArray();

        Activity::causedBy($publisher)
            ->performedOn($race)
            ->withProperties([
                'race_id' => $race->id,
                'race_name' => $race->name,
                'results_count' => $resultsCount,
                'podium' => $topThree,
            ])
            ->log('results_published');
    }

    /**
     * Dispatch championship recalculation job.
     */
    private function dispatchChampionshipRecalculation(Race $race): void
    {
        // Check if race is part of a season (championship)
        if ($race->season_id === null) {
            return;
        }

        // Dispatch job to recalculate championship standings
        \App\Jobs\RebuildSeasonStandingsJob::dispatch($race->season_id);
    }
}
