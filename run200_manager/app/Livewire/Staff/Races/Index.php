<?php

namespace App\Livewire\Staff\Races;

use App\Models\Race;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $statusFilter = '';

    public string $search = '';

    protected $queryString = ['statusFilter', 'search'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function downloadEngagedList(int $raceId)
    {
        return $this->redirect(route('staff.races.engaged-pdf', $raceId));
    }

    public function render()
    {
        $query = Race::with(['season', 'registrations']);

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('location', 'like', '%'.$this->search.'%');
            });
        }

        $races = $query->orderBy('race_date', 'desc')->paginate(10);

        return view('livewire.staff.races.index', [
            'races' => $races,
        ])->layout('layouts.app');
    }
}
