<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Models\Season;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class RaceCalendar extends Component
{
    #[Computed]
    public function season(): ?Season
    {
        return Season::active()->first();
    }

    #[Computed]
    public function upcomingRaces(): Collection
    {
        if (! $this->season) {
            return collect();
        }

        return $this->season->races()
            ->whereIn('status', ['OPEN', 'CLOSED'])
            ->where('race_date', '>=', now()->startOfDay())
            ->orderBy('race_date')
            ->get();
    }

    #[Computed]
    public function pastRaces(): Collection
    {
        if (! $this->season) {
            return collect();
        }

        return $this->season->races()
            ->whereIn('status', ['PUBLISHED', 'RESULTS_READY', 'COMPLETED'])
            ->where('race_date', '<', now()->startOfDay())
            ->orderByDesc('race_date')
            ->get();
    }

    #[Computed]
    public function allRaces(): Collection
    {
        if (! $this->season) {
            return collect();
        }

        return $this->season->races()
            ->whereIn('status', ['OPEN', 'CLOSED', 'PUBLISHED', 'RESULTS_READY', 'COMPLETED', 'RUNNING'])
            ->orderBy('race_date')
            ->get();
    }

    #[Computed]
    public function seasonStats(): array
    {
        if (! $this->season) {
            return [
                'total_races' => 0,
                'completed_races' => 0,
                'upcoming_races' => 0,
            ];
        }

        $races = $this->allRaces;

        return [
            'total_races' => $races->count(),
            'completed_races' => $races->whereIn('status', ['PUBLISHED', 'RESULTS_READY', 'COMPLETED'])->count(),
            'upcoming_races' => $races->whereIn('status', ['OPEN', 'CLOSED'])->count(),
        ];
    }

    public function render()
    {
        return view('livewire.public.race-calendar')
            ->layout('components.layouts.racing-public', ['title' => 'Calendrier des courses - RUN200']);
    }
}
