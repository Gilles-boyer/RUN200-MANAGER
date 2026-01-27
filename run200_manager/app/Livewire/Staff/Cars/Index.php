<?php

namespace App\Livewire\Staff\Cars;

use App\Models\Car;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $categoryFilter = null;

    public ?int $pilotFilter = null;

    public string $sortBy = 'race_number';

    public string $sortDirection = 'asc';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPilotFilter(): void
    {
        $this->resetPage();
    }

    public function sortByColumn(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->categoryFilter = null;
        $this->pilotFilter = null;
        $this->sortBy = 'race_number';
        $this->sortDirection = 'asc';
        $this->resetPage();
    }

    public function getCarsProperty()
    {
        $query = Car::query()
            ->with(['pilot.user', 'category', 'latestTechInspection']);

        // Search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('race_number', 'like', '%'.$this->search.'%')
                    ->orWhere('make', 'like', '%'.$this->search.'%')
                    ->orWhere('model', 'like', '%'.$this->search.'%')
                    ->orWhereHas('pilot', function ($pilotQuery) {
                        $pilotQuery->where('first_name', 'like', '%'.$this->search.'%')
                            ->orWhere('last_name', 'like', '%'.$this->search.'%');
                    });
            });
        }

        // Filters
        if ($this->categoryFilter) {
            $query->where('car_category_id', $this->categoryFilter);
        }

        if ($this->pilotFilter) {
            $query->where('pilot_id', $this->pilotFilter);
        }

        // Sorting
        $query->orderBy($this->sortBy, $this->sortDirection);

        return $query->paginate(20);
    }

    public function getCategoriesProperty()
    {
        return \App\Models\CarCategory::orderBy('name')->get();
    }

    public function getPilotsProperty()
    {
        return \App\Models\Pilot::with('user')
            ->orderBy('last_name')
            ->get();
    }

    public function render()
    {
        return view('livewire.staff.cars.index', [
            'cars' => $this->cars,
            'categories' => $this->categories,
            'pilots' => $this->pilots,
        ]);
    }
}
