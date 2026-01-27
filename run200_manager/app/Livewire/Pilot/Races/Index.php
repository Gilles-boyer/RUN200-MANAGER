<?php

namespace App\Livewire\Pilot\Races;

use App\Models\Race;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url(except: 'OPEN')]
    public string $statusFilter = 'OPEN';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = auth()->user();
        $pilot = $user->pilot;

        $query = Race::with(['season', 'registrations'])
            ->whereHas('season', function ($q) {
                $q->where('is_active', true);
            });

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('location', 'like', '%'.$this->search.'%');
            });
        }

        $races = $query->orderBy('race_date', 'asc')->paginate(10);

        // Get IDs of races where pilot is already registered
        $registeredRaceIds = [];
        if ($pilot) {
            $registeredRaceIds = $pilot->raceRegistrations()
                ->pluck('race_id')
                ->toArray();
        }

        return view('livewire.pilot.races.index', [
            'races' => $races,
            'pilot' => $pilot,
            'registeredRaceIds' => $registeredRaceIds,
        ])->layout('layouts.pilot');
    }
}
