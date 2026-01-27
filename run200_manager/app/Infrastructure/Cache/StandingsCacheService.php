<?php

declare(strict_types=1);

namespace App\Infrastructure\Cache;

use App\Models\CarCategory;
use App\Models\Season;
use App\Models\SeasonCategoryStanding;
use App\Models\SeasonStanding;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Service de cache pour les standings du championnat.
 * Améliore les performances en cachant les classements fréquemment consultés.
 */
class StandingsCacheService
{
    public const CACHE_TTL_SECONDS = 3600; // 1 heure

    public const CACHE_PREFIX = 'standings';

    /**
     * Récupère les standings généraux d'une saison (avec cache).
     */
    public function getGeneralStandings(int $seasonId): Collection
    {
        $key = $this->buildKey('general', $seasonId);

        return Cache::remember($key, self::CACHE_TTL_SECONDS, function () use ($seasonId) {
            Log::debug("StandingsCache MISS: general standings for season {$seasonId}");

            return SeasonStanding::forSeason($seasonId)
                ->with('pilot.user')
                ->orderByRaw('rank IS NULL, rank ASC')
                ->orderByDesc('total_points')
                ->get();
        });
    }

    /**
     * Récupère les standings d'une catégorie (avec cache).
     */
    public function getCategoryStandings(int $seasonId, int $categoryId): Collection
    {
        $key = $this->buildKey('category', $seasonId, $categoryId);

        return Cache::remember($key, self::CACHE_TTL_SECONDS, function () use ($seasonId, $categoryId) {
            Log::debug("StandingsCache MISS: category {$categoryId} standings for season {$seasonId}");

            return SeasonCategoryStanding::forSeason($seasonId)
                ->forCategory($categoryId)
                ->with(['pilot.user', 'category'])
                ->orderByRaw('rank IS NULL, rank ASC')
                ->orderByDesc('total_points')
                ->get();
        });
    }

    /**
     * Récupère les statistiques de la saison (avec cache).
     */
    public function getSeasonStats(Season $season): array
    {
        $key = $this->buildKey('stats', $season->id);

        return Cache::remember($key, self::CACHE_TTL_SECONDS, function () use ($season) {
            return [
                'total_races' => $season->races()->count(),
                'published_races' => $season->races()->where('status', 'PUBLISHED')->count(),
                'participants' => SeasonStanding::forSeason($season->id)->count(),
            ];
        });
    }

    /**
     * Récupère les catégories actives (avec cache).
     */
    public function getActiveCategories(): Collection
    {
        return Cache::remember(
            $this->buildKey('categories'),
            self::CACHE_TTL_SECONDS,
            fn () => CarCategory::whereActive()->ordered()->get()
        );
    }

    /**
     * Invalide le cache des standings pour une saison.
     * À appeler après la publication de résultats ou le recalcul des standings.
     */
    public function invalidateForSeason(int $seasonId): void
    {
        Log::info("Invalidating standings cache for season {$seasonId}");

        // Invalider le cache général
        Cache::forget($this->buildKey('general', $seasonId));
        Cache::forget($this->buildKey('stats', $seasonId));

        // Invalider le cache de toutes les catégories
        $categories = CarCategory::all();
        foreach ($categories as $category) {
            Cache::forget($this->buildKey('category', $seasonId, $category->id));
        }
    }

    /**
     * Invalide tout le cache des standings.
     */
    public function invalidateAll(): void
    {
        Log::info('Invalidating all standings cache');

        // Utiliser le tag si le driver supporte les tags
        if ($this->supportsTagging()) {
            Cache::tags([self::CACHE_PREFIX])->flush();

            return;
        }

        // Sinon, invalider manuellement pour chaque saison active
        $seasons = Season::all();
        foreach ($seasons as $season) {
            $this->invalidateForSeason($season->id);
        }

        Cache::forget($this->buildKey('categories'));
    }

    /**
     * Préchauffe le cache pour une saison.
     */
    public function warmupForSeason(int $seasonId): void
    {
        Log::info("Warming up standings cache for season {$seasonId}");

        // Précharger les standings généraux
        $this->getGeneralStandings($seasonId);

        // Précharger les stats
        $season = Season::find($seasonId);
        if ($season) {
            $this->getSeasonStats($season);
        }

        // Précharger les catégories les plus consultées (top 5)
        $topCategories = CarCategory::whereActive()
            ->withCount('cars')
            ->orderByDesc('cars_count')
            ->take(5)
            ->get();

        foreach ($topCategories as $category) {
            $this->getCategoryStandings($seasonId, $category->id);
        }
    }

    /**
     * Construit une clé de cache.
     */
    protected function buildKey(string $type, ?int $seasonId = null, ?int $categoryId = null): string
    {
        $parts = [self::CACHE_PREFIX, $type];

        if ($seasonId !== null) {
            $parts[] = "s{$seasonId}";
        }

        if ($categoryId !== null) {
            $parts[] = "c{$categoryId}";
        }

        return implode(':', $parts);
    }

    /**
     * Vérifie si le driver de cache supporte le tagging.
     */
    protected function supportsTagging(): bool
    {
        $driver = config('cache.default');

        return in_array($driver, ['redis', 'memcached', 'dynamodb']);
    }
}
