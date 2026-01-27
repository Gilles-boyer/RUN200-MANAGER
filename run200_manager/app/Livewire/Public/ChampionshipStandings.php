<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Domain\Championship\Rules\StandingsRules;
use App\Infrastructure\Cache\StandingsCacheService;
use App\Models\CarCategory;
use App\Models\Season;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class ChampionshipStandings extends Component
{
    #[Url]
    public string $view = 'general'; // 'general' or category ID

    protected StandingsCacheService $cacheService;

    public function boot(StandingsCacheService $cacheService): void
    {
        $this->cacheService = $cacheService;
    }

    #[Computed]
    public function season(): ?Season
    {
        return Season::active()->first();
    }

    #[Computed]
    public function categories(): Collection
    {
        return $this->cacheService->getActiveCategories();
    }

    #[Computed]
    public function generalStandings(): Collection
    {
        if (! $this->season) {
            return collect();
        }

        return $this->cacheService->getGeneralStandings($this->season->id);
    }

    #[Computed]
    public function categoryStandings(): Collection
    {
        if (! $this->season || $this->view === 'general') {
            return collect();
        }

        return $this->cacheService->getCategoryStandings($this->season->id, (int) $this->view);
    }

    #[Computed]
    public function currentCategory(): ?CarCategory
    {
        if ($this->view === 'general') {
            return null;
        }

        return CarCategory::find((int) $this->view);
    }

    #[Computed]
    public function seasonStats(): array
    {
        if (! $this->season) {
            return [
                'total_races' => 0,
                'published_races' => 0,
                'participants' => 0,
            ];
        }

        return $this->cacheService->getSeasonStats($this->season);
    }

    #[Computed]
    public function rules(): array
    {
        return [
            'min_races' => StandingsRules::MIN_RACES_REQUIRED,
            'bonus_points' => StandingsRules::BONUS_ALL_RACES,
        ];
    }

    public function switchView(string $view): void
    {
        $this->view = $view;
    }

    public function render()
    {
        return view('livewire.public.championship-standings')
            ->layout('layouts.guest', ['title' => 'Classement du championnat']);
    }
}
