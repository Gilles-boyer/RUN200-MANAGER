<?php

declare(strict_types=1);

namespace App\Application\Results\UseCases;

use App\Events\ResultsPublished;
use App\Models\Race;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

        $publishedRace = DB::transaction(function () use ($race, $publisher) {
            // Update race status to PUBLISHED
            $race->update(['status' => 'PUBLISHED']);

            // Log the activity
            $this->logActivity($race, $publisher);

            $race->refresh();

            return $race;
        });

        // Queue side effects only after the status is committed. Notification failures
        // must not turn a successful publication into an apparent failure.
        $this->dispatchPostPublicationActions($publishedRace);

        return $publishedRace;
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

        $unpublishedRace = DB::transaction(function () use ($race, $user) {
            $race->update(['status' => 'RESULTS_READY']);

            Activity::causedBy($user)
                ->performedOn($race)
                ->withProperties([
                    'race_id' => $race->id,
                    'race_name' => $race->name,
                ])
                ->log('results_unpublished');

            $race->refresh();

            return $race;
        });

        try {
            $this->dispatchChampionshipRecalculation($unpublishedRace);
        } catch (\Throwable $e) {
            Log::error('Unable to queue championship recalculation after unpublishing results', [
                'race_id' => $unpublishedRace->id,
                'exception' => $e,
            ]);
        }

        return $unpublishedRace;
    }

    private function dispatchPostPublicationActions(Race $race): void
    {
        try {
            $this->dispatchChampionshipRecalculation($race);
        } catch (\Throwable $e) {
            Log::error('Unable to queue championship recalculation after publishing results', [
                'race_id' => $race->id,
                'exception' => $e,
            ]);
        }

        try {
            ResultsPublished::dispatch($race);
        } catch (\Throwable $e) {
            Log::error('Unable to queue results publication notifications', [
                'race_id' => $race->id,
                'exception' => $e,
            ]);
        }
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
