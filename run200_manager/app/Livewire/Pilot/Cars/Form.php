<?php

namespace App\Livewire\Pilot\Cars;

use App\Models\Car;
use App\Models\CarCategory;
use Livewire\Component;

class Form extends Component
{
    public $car;

    public $race_number;

    public $make;

    public $model;

    public $car_category_id;

    public $notes;

    public function mount($car = null)
    {
        if ($car) {
            $this->car = Car::findOrFail($car);

            // Vérifier autorisation
            if ($this->car->pilot->user_id !== auth()->id() && ! auth()->user()->isAdmin()) {
                abort(403);
            }

            $this->race_number = $this->car->race_number->toInt();
            $this->make = $this->car->make;
            $this->model = $this->car->model;
            $this->car_category_id = $this->car->car_category_id;
            $this->notes = $this->car->notes;
        }
    }

    public function save()
    {
        $pilot = auth()->user()->pilot;

        if (! $pilot) {
            session()->flash('error', 'Vous devez créer votre profil pilote avant d\'ajouter une voiture.');

            return redirect()->route('pilot.profile.edit');
        }

        $validatedData = $this->validate([
            'race_number' => 'required|integer|min:0|max:999',
            'make' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'car_category_id' => 'required|exists:car_categories,id',
            'notes' => 'nullable|string',
        ]);

        // Vérifier unicité race_number
        $existingCar = Car::where('race_number', $this->race_number)
            ->when($this->car, fn ($q) => $q->where('id', '!=', $this->car->id))
            ->first();

        if ($existingCar) {
            $this->addError('race_number', 'Ce numéro de course est déjà utilisé.');

            return;
        }

        $carData = [
            'pilot_id' => $pilot->id,
            'race_number' => $this->race_number,
            'make' => $this->make,
            'model' => $this->model,
            'car_category_id' => $this->car_category_id,
            'notes' => $this->notes,
        ];

        if ($this->car) {
            $this->car->update($carData);
            $message = 'Voiture mise à jour avec succès';
        } else {
            Car::create($carData);
            $message = 'Voiture créée avec succès';
        }

        session()->flash('success', $message);

        return redirect()->route('pilot.cars.index');
    }

    public function delete()
    {
        if (! $this->car) {
            return redirect()->route('pilot.cars.index');
        }

        $this->car->delete();
        session()->flash('success', 'Voiture supprimée avec succès');

        return redirect()->route('pilot.cars.index');
    }

    /**
     * Generate a random available race number.
     */
    public function generateRandomNumber()
    {
        // Get all used race numbers
        $usedNumbers = Car::pluck('race_number')->toArray();

        // Generate list of available numbers (0-999)
        $allNumbers = range(0, 999);
        $availableNumbers = array_diff($allNumbers, $usedNumbers);

        if (empty($availableNumbers)) {
            $this->addError('race_number', 'Aucun numéro disponible.');

            return;
        }

        // Pick a random available number
        $this->race_number = $availableNumbers[array_rand($availableNumbers)];

        // Clear any previous error
        $this->resetErrorBag('race_number');
    }

    public function render()
    {
        $categories = CarCategory::whereActive()->ordered()->get();

        return view('livewire.pilot.cars.form', [
            'categories' => $categories,
        ])->layout('layouts.pilot');
    }
}
