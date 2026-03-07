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

    // Category editing modal
    public bool $showCategoryModal = false;

    public ?int $editingCarId = null;

    public ?int $newCategoryId = null;

    // Race number editing modal
    public bool $showRaceNumberModal = false;

    public ?int $editingRaceNumberCarId = null;

    public ?int $newRaceNumber = null;

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

    public function openCategoryModal(int $carId): void
    {
        $car = Car::find($carId);
        if ($car) {
            $this->editingCarId = $carId;
            $this->newCategoryId = $car->car_category_id;
            $this->showCategoryModal = true;
        }
    }

    public function closeCategoryModal(): void
    {
        $this->showCategoryModal = false;
        $this->editingCarId = null;
        $this->newCategoryId = null;
    }

    public function updateCategory(): void
    {
        if (! $this->editingCarId || ! $this->newCategoryId) {
            return;
        }

        $car = Car::find($this->editingCarId);
        if ($car) {
            $car->update(['car_category_id' => $this->newCategoryId]);
            $this->dispatch('notify', type: 'success', message: 'Catégorie mise à jour avec succès');
        }

        $this->closeCategoryModal();
    }

    public function openRaceNumberModal(int $carId): void
    {
        $car = Car::find($carId);
        if ($car) {
            $this->editingRaceNumberCarId = $carId;
            $this->newRaceNumber = $car->race_number->toInt();
            $this->showRaceNumberModal = true;
        }
    }

    public function closeRaceNumberModal(): void
    {
        $this->showRaceNumberModal = false;
        $this->editingRaceNumberCarId = null;
        $this->newRaceNumber = null;
        $this->resetValidation(['newRaceNumber']);
    }

    public function updateRaceNumber(): void
    {
        $this->validate([
            'newRaceNumber' => [
                'required',
                'integer',
                'min:0',
                'max:999',
                'unique:cars,race_number,' . $this->editingRaceNumberCarId,
            ],
        ], [
            'newRaceNumber.required' => 'Le numéro de course est obligatoire.',
            'newRaceNumber.integer' => 'Le numéro de course doit être un nombre entier.',
            'newRaceNumber.min' => 'Le numéro de course doit être au minimum 0.',
            'newRaceNumber.max' => 'Le numéro de course ne peut pas dépasser 999.',
            'newRaceNumber.unique' => 'Ce numéro de course est déjà utilisé par une autre voiture.',
        ]);

        $car = Car::find($this->editingRaceNumberCarId);
        if ($car) {
            $car->update(['race_number' => $this->newRaceNumber]);
            $this->dispatch('notify', type: 'success', message: 'Numéro de course mis à jour avec succès');
        }

        $this->closeRaceNumberModal();
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
