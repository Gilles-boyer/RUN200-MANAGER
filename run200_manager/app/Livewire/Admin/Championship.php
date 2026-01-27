<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Application\Championship\UseCases\RebuildSeasonStandings;
use App\Domain\Championship\Rules\StandingsRules;
use App\Models\CarCategory;
use App\Models\Season;
use App\Models\SeasonCategoryStanding;
use App\Models\SeasonStanding;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Championship extends Component
{
    use WithPagination;

    public Season $season;

    public string $view = 'general'; // 'general' or category id

    public ?string $selectedCategoryId = null;

    public bool $showRebuildConfirmation = false;

    public ?string $successMessage = null;

    public ?string $errorMessage = null;

    public function mount(Season $season): void
    {
        $this->season = $season->load(['races', 'pointsRules']);
    }

    #[Computed]
    public function generalStandings()
    {
        return SeasonStanding::forSeason($this->season->id)
            ->with('pilot.user')
            ->orderByRaw('rank IS NULL, rank ASC')
            ->orderByDesc('total_points')
            ->paginate(20);
    }

    #[Computed]
    public function categoryStandings()
    {
        if (! $this->selectedCategoryId) {
            return collect();
        }

        return SeasonCategoryStanding::forSeason($this->season->id)
            ->forCategory((int) $this->selectedCategoryId)
            ->with(['pilot.user', 'category'])
            ->orderByRaw('rank IS NULL, rank ASC')
            ->orderByDesc('total_points')
            ->paginate(20);
    }

    #[Computed]
    public function categories()
    {
        // Get categories that have standings in this season
        return CarCategory::whereHas('seasonCategoryStandings', function ($query) {
            $query->where('season_id', $this->season->id);
        })->orderBy('name')->get();
    }

    #[Computed]
    public function seasonStats(): array
    {
        $totalRaces = $this->season->races()->count();
        $publishedRaces = $this->season->races()->where('status', 'PUBLISHED')->count();
        $totalPilots = SeasonStanding::forSeason($this->season->id)->count();
        $rankedPilots = SeasonStanding::forSeason($this->season->id)->whereNotNull('rank')->count();
        $pilotsWithBonus = SeasonStanding::forSeason($this->season->id)->where('bonus_points', '>', 0)->count();

        return [
            'total_races' => $totalRaces,
            'published_races' => $publishedRaces,
            'total_pilots' => $totalPilots,
            'ranked_pilots' => $rankedPilots,
            'pilots_with_bonus' => $pilotsWithBonus,
            'min_races_required' => StandingsRules::MIN_RACES_REQUIRED,
            'bonus_points' => StandingsRules::BONUS_ALL_RACES,
        ];
    }

    #[Computed]
    public function pointsRules()
    {
        return $this->season->pointsRules()->ordered()->get();
    }

    public function selectGeneral(): void
    {
        $this->view = 'general';
        $this->selectedCategoryId = null;
        $this->resetPage();
    }

    public function selectCategory(string $categoryId): void
    {
        $this->view = 'category';
        $this->selectedCategoryId = $categoryId;
        $this->resetPage();
    }

    public function confirmRebuild(): void
    {
        $this->showRebuildConfirmation = true;
    }

    public function cancelRebuild(): void
    {
        $this->showRebuildConfirmation = false;
    }

    public function rebuildStandings(): void
    {
        $this->showRebuildConfirmation = false;

        try {
            $useCase = app(RebuildSeasonStandings::class);
            $result = $useCase->execute($this->season, auth()->user());

            $this->successMessage = "Classement recalculé : {$result['ranked_pilots']} pilotes classés sur {$result['general_standings_count']} participants.";
            $this->errorMessage = null;
        } catch (\Exception $e) {
            $this->errorMessage = 'Erreur lors du recalcul : '.$e->getMessage();
            $this->successMessage = null;
        }
    }

    public function render()
    {
        return view('livewire.admin.championship')
            ->layout('layouts.app');
    }
}
