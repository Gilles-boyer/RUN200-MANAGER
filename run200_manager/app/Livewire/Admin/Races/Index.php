<?php

namespace App\Livewire\Admin\Races;

use App\Events\RaceCancelled;
use App\Events\RaceOpened;
use App\Models\Race;
use App\Models\Season;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public ?int $seasonFilter = null;

    public string $cancellationReason = '';

    protected $queryString = ['search', 'statusFilter', 'seasonFilter'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function deleteRace(int $raceId)
    {
        $race = Race::findOrFail($raceId);

        // Vérifier qu'il n'y a pas d'inscriptions
        if ($race->registrations()->exists()) {
            session()->flash('error', 'Impossible de supprimer une course avec des inscriptions.');

            return;
        }

        $race->delete();

        session()->flash('success', 'Course supprimée avec succès.');
    }

    public function updateStatus(int $raceId, string $status)
    {
        $race = Race::findOrFail($raceId);
        $previousStatus = $race->status;

        $race->update(['status' => $status]);

        // Dispatch events based on status change
        if ($status === 'OPEN' && $previousStatus !== 'OPEN') {
            // Race is now open for registrations
            RaceOpened::dispatch($race);
            session()->flash('success', 'La course est maintenant ouverte aux inscriptions. Les pilotes ont été notifiés par email.');
        } elseif ($status === 'CANCELLED' && $previousStatus !== 'CANCELLED') {
            // Race is cancelled - notify registered pilots
            RaceCancelled::dispatch($race, $this->cancellationReason ?: null);
            $this->cancellationReason = '';
            session()->flash('success', 'La course a été annulée. Les pilotes inscrits ont été notifiés par email.');
        } else {
            session()->flash('success', 'Statut de la course mis à jour.');
        }
    }

    public function render()
    {
        $query = Race::with(['season', 'registrations']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('location', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->seasonFilter) {
            $query->where('season_id', $this->seasonFilter);
        }

        $races = $query->orderBy('race_date', 'desc')->paginate(15);
        $seasons = Season::orderBy('start_date', 'desc')->get();

        return view('livewire.admin.races.index', [
            'races' => $races,
            'seasons' => $seasons,
        ])->layout('layouts.app');
    }
}
