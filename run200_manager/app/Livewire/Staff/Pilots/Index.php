<?php

declare(strict_types=1);

namespace App\Livewire\Staff\Pilots;

use App\Models\Pilot;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $sortField = 'last_name';

    public string $sortDirection = 'asc';

    protected $queryString = ['search', 'sortField', 'sortDirection'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        $query = Pilot::with(['user', 'cars.category']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('first_name', 'like', '%'.$this->search.'%')
                    ->orWhere('last_name', 'like', '%'.$this->search.'%')
                    ->orWhere('license_number', 'like', '%'.$this->search.'%')
                    ->orWhere('phone', 'like', '%'.$this->search.'%')
                    ->orWhereHas('user', function ($uq) {
                        $uq->where('email', 'like', '%'.$this->search.'%');
                    });
            });
        }

        $query->orderBy($this->sortField, $this->sortDirection);

        $pilots = $query->paginate(15);

        return view('livewire.staff.pilots.index', [
            'pilots' => $pilots,
        ])->layout('layouts.app');
    }
}
