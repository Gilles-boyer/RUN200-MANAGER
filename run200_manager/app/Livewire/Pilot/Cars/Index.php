<?php

namespace App\Livewire\Pilot\Cars;

use App\Models\Car;
use App\Models\CarCategory;
use Illuminate\Database\QueryException;
use Livewire\Component;

class Index extends Component
{
    public $search = '';

    public $categoryFilter = '';

    public ?string $deleteError = null;

    public function mount()
    {
        $pilot = auth()->user()?->pilot;

        if (! $pilot) {
            return redirect()->route('pilot.profile.edit')
                ->with('info', 'Veuillez créer votre profil pilote avant d\'ajouter des voitures');
        }
    }

    public function deleteCar($carId)
    {
        $this->deleteError = null;
        $car = Car::findOrFail($carId);

        // Vérifier que l'utilisateur est propriétaire
        if ($car->pilot->user_id !== auth()->id() && ! auth()->user()->isAdmin()) {
            session()->flash('error', 'Vous n\'êtes pas autorisé à supprimer cette voiture.');

            return;
        }

        try {
            $car->delete();
        } catch (\DomainException $e) {
            $this->deleteError = $e->getMessage();

            return;
        } catch (QueryException $e) {
            if (! $car->raceRegistrations()->exists()) {
                throw $e;
            }

            $this->deleteError = Car::DELETION_BLOCKED_MESSAGE;

            return;
        }

        session()->flash('success', 'Voiture supprimée avec succès.');
    }

    public function render()
    {
        $pilot = auth()->user()->pilot;

        $carsQuery = $pilot->cars()->with('category')->withExists(['raceRegistrations as has_registrations']);

        if ($this->search) {
            $carsQuery->where(function ($q) {
                $q->where('make', 'like', '%'.$this->search.'%')
                    ->orWhere('model', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->categoryFilter) {
            $carsQuery->where('car_category_id', $this->categoryFilter);
        }

        $cars = $carsQuery->orderBy('race_number')->get();
        $categories = CarCategory::whereActive()->ordered()->get();

        return view('livewire.pilot.cars.index', [
            'cars' => $cars,
            'categories' => $categories,
            'pilot' => $pilot,
        ])->layout('layouts.pilot');
    }
}
