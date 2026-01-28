<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Models\Race;
use App\Models\Season;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class BoardIndex extends Component
{
    #[Computed]
    public function season(): ?Season
    {
        return Season::active()->first();
    }

    #[Computed]
    public function racesWithBoards(): Collection
    {
        if (! $this->season) {
            return collect();
        }

        return Race::where('season_id', $this->season->id)
            ->whereHas('documents', function ($query) {
                $query->where('status', 'PUBLISHED');
            })
            ->withCount(['documents' => function ($query) {
                $query->where('status', 'PUBLISHED');
            }])
            ->orderByDesc('race_date')
            ->get();
    }

    #[Computed]
    public function upcomingRacesWithBoards(): Collection
    {
        return $this->racesWithBoards->filter(function ($race) {
            return $race->race_date->isFuture() || $race->race_date->isToday();
        });
    }

    #[Computed]
    public function pastRacesWithBoards(): Collection
    {
        return $this->racesWithBoards->filter(function ($race) {
            return $race->race_date->isPast() && !$race->race_date->isToday();
        });
    }

    public function render()
    {
        return view('livewire.public.board-index')
            ->layout('components.layouts.racing-public', ['title' => 'Tableaux d\'affichage - RUN200']);
    }
}
