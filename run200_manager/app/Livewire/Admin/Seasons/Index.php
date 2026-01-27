<?php

namespace App\Livewire\Admin\Seasons;

use App\Models\Season;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    protected $queryString = ['search', 'statusFilter'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function deleteSeason(int $seasonId)
    {
        $season = Season::findOrFail($seasonId);

        // Vérifier qu'il n'y a pas de courses
        if ($season->races()->exists()) {
            session()->flash('error', 'Impossible de supprimer une saison avec des courses.');

            return;
        }

        $season->delete();

        session()->flash('success', 'Saison supprimée avec succès.');
    }

    public function toggleActive(int $seasonId)
    {
        $season = Season::findOrFail($seasonId);
        $season->update(['is_active' => ! $season->is_active]);

        session()->flash('success', 'Statut de la saison mis à jour.');
    }

    public function render()
    {
        $query = Season::withCount('races');

        if ($this->search) {
            $query->where('name', 'like', '%'.$this->search.'%');
        }

        if ($this->statusFilter !== '') {
            $query->where('is_active', $this->statusFilter === 'active');
        }

        $seasons = $query->orderBy('start_date', 'desc')->paginate(10);

        return view('livewire.admin.seasons.index', [
            'seasons' => $seasons,
        ])->layout('layouts.app');
    }
}
