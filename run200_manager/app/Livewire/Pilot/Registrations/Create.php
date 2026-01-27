<?php

namespace App\Livewire\Pilot\Registrations;

use App\Application\Registrations\UseCases\SubmitRegistration;
use App\Models\Car;
use App\Models\Race;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Create extends Component
{
    public Race $race;

    public ?int $selectedCarId = null;

    public bool $confirmTerms = false;

    public string $errorMessage = '';

    public function mount(Race $race)
    {
        $this->race = $race;

        // Vérifier que la course est ouverte
        if (! $race->isOpen()) {
            session()->flash('error', 'Cette course n\'est pas ouverte aux inscriptions.');

            return $this->redirect(route('pilot.races.index'));
        }

        // Vérifier si le pilote existe
        $pilot = auth()->user()->pilot;
        if (! $pilot) {
            session()->flash('error', 'Vous devez compléter votre profil pilote avant de vous inscrire.');

            return $this->redirect(route('pilot.profile.edit'));
        }

        // Vérifier si déjà inscrit
        $existingRegistration = $this->race->registrations()
            ->where('pilot_id', $pilot->id)
            ->first();

        if ($existingRegistration) {
            session()->flash('error', 'Vous êtes déjà inscrit à cette course.');

            return $this->redirect(route('pilot.registrations.index'));
        }
    }

    #[Computed]
    public function pilot()
    {
        return auth()->user()->pilot;
    }

    #[Computed]
    public function cars()
    {
        if (! $this->pilot) {
            return collect();
        }

        return $this->pilot->cars()->get();
    }

    #[Computed]
    public function selectedCar()
    {
        if (! $this->selectedCarId) {
            return null;
        }

        return Car::find($this->selectedCarId);
    }

    public function submit(SubmitRegistration $submitRegistration)
    {
        $this->errorMessage = '';

        // Validation
        if (! $this->selectedCarId) {
            $this->errorMessage = 'Veuillez sélectionner une voiture.';

            return;
        }

        if (! $this->confirmTerms) {
            $this->errorMessage = 'Vous devez accepter les conditions pour vous inscrire.';

            return;
        }

        $pilot = $this->pilot;
        if (! $pilot) {
            $this->errorMessage = 'Profil pilote non trouvé.';

            return;
        }

        $car = Car::find($this->selectedCarId);
        if (! $car || $car->pilot_id !== $pilot->id) {
            $this->errorMessage = 'Voiture invalide ou non autorisée.';

            return;
        }

        try {
            $registration = $submitRegistration->execute($this->race, $pilot, $car, true);

            session()->flash('info', 'Votre inscription a été créée. Veuillez procéder au paiement pour la valider.');

            // Rediriger vers la page de paiement
            return $this->redirect(route('pilot.registrations.payment', $registration));
        } catch (\InvalidArgumentException $e) {
            $this->errorMessage = $e->getMessage();
        } catch (\Exception $e) {
            $this->errorMessage = 'Une erreur est survenue lors de l\'inscription. Veuillez réessayer.';
            report($e);
        }
    }

    public function render()
    {
        return view('livewire.pilot.registrations.create')
            ->layout('layouts.pilot');
    }
}
