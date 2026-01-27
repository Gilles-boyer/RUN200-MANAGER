<?php

declare(strict_types=1);

namespace App\Livewire\Pilot;

use App\Domain\Championship\Rules\StandingsRules;
use App\Models\Season;
use App\Models\SeasonCategoryStanding;
use App\Models\SeasonStanding;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ChampionshipStanding extends Component
{
    public ?Season $season = null;

    public string $view = 'general'; // 'general' or 'categories'

    public function mount(?Season $season = null): void
    {
        // Get active season if not provided
        $this->season = $season ?? Season::active()->first();
    }

    #[Computed]
    public function pilot()
    {
        return auth()->user()->pilot;
    }

    #[Computed]
    public function generalStanding(): ?SeasonStanding
    {
        if (! $this->season || ! $this->pilot) {
            return null;
        }

        return SeasonStanding::forSeason($this->season->id)
            ->where('pilot_id', $this->pilot->id)
            ->first();
    }

    #[Computed]
    public function categoryStandings(): Collection
    {
        if (! $this->season || ! $this->pilot) {
            return collect();
        }

        return SeasonCategoryStanding::forSeason($this->season->id)
            ->where('pilot_id', $this->pilot->id)
            ->with('category')
            ->get();
    }

    #[Computed]
    public function topGeneralStandings(): Collection
    {
        if (! $this->season) {
            return collect();
        }

        return SeasonStanding::forSeason($this->season->id)
            ->with('pilot.user')
            ->whereNotNull('rank')
            ->orderBy('rank')
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function seasonStats(): array
    {
        if (! $this->season) {
            return [];
        }

        $totalRaces = $this->season->races()->count();
        $publishedRaces = $this->season->races()->where('status', 'PUBLISHED')->count();

        return [
            'total_races' => $totalRaces,
            'published_races' => $publishedRaces,
            'min_races_required' => StandingsRules::MIN_RACES_REQUIRED,
            'bonus_points' => StandingsRules::BONUS_ALL_RACES,
        ];
    }

    #[Computed]
    public function rankingStatus(): string
    {
        $standing = $this->generalStanding;

        if (! $standing) {
            return 'Aucune participation';
        }

        return StandingsRules::getRankingStatusLabel($standing->races_count);
    }

    #[Computed]
    public function bonusStatus(): string
    {
        $standing = $this->generalStanding;
        $stats = $this->seasonStats;

        if (! $standing || empty($stats)) {
            return '';
        }

        return StandingsRules::getBonusStatusLabel(
            $standing->races_count,
            $stats['published_races']
        );
    }

    public function switchView(string $view): void
    {
        $this->view = $view;
    }

    public function render()
    {
        return view('livewire.pilot.championship-standing')
            ->layout('layouts.app');
    }
}
