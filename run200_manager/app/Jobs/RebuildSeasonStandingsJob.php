<?php

namespace App\Jobs;

use App\Application\Championship\UseCases\RebuildSeasonStandings;
use App\Infrastructure\Cache\StandingsCacheService;
use App\Models\Season;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RebuildSeasonStandingsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $seasonId,
        public ?int $triggeredByUserId = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(RebuildSeasonStandings $useCase, StandingsCacheService $cacheService): void
    {
        $season = Season::find($this->seasonId);

        if (! $season) {
            Log::warning("RebuildSeasonStandingsJob: Season {$this->seasonId} not found");

            return;
        }

        $triggeredBy = $this->triggeredByUserId
            ? User::find($this->triggeredByUserId)
            : null;

        try {
            $result = $useCase->execute($season, $triggeredBy);

            Log::info("Championship standings rebuilt for season {$season->name}", $result);

            // Invalider le cache après le rebuild
            $cacheService->invalidateForSeason($this->seasonId);

            // Préchauffer le cache avec les nouvelles données
            $cacheService->warmupForSeason($this->seasonId);

            Log::info("Standings cache warmed up for season {$season->name}");
        } catch (\Exception $e) {
            Log::error("Failed to rebuild championship standings for season {$this->seasonId}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return ['championship', 'season:'.$this->seasonId];
    }
}
