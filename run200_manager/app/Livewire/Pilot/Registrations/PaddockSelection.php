<?php

namespace App\Livewire\Pilot\Registrations;

use App\Application\Registrations\UseCases\AssignPaddockSpot;
use App\Application\Registrations\UseCases\ReleasePaddockSpot;
use App\Models\PaddockSpot;
use App\Models\RaceRegistration;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class PaddockSelection extends Component
{
    use AuthorizesRequests;

    public RaceRegistration $registration;

    public ?string $selectedZone = null;

    public ?int $selectedSpotId = null;

    public bool $showSpotDetails = false;

    public ?PaddockSpot $spotDetails = null;

    public string $viewMode = 'grid'; // 'grid' ou 'map'

    public function mount(RaceRegistration $registration)
    {
        $this->registration = $registration->load(['pilot.user', 'car', 'race', 'paddockSpot']);

        // Vérifier les permissions
        $this->authorize('selectPaddockSpot', $registration);
    }

    public function selectSpot(int $spotId)
    {
        $this->authorize('selectPaddockSpot', $this->registration);
        $this->resetErrorBag('spot');
        $spot = PaddockSpot::findOrFail($spotId);

        if ($this->registration->paddock_spot_id === $spotId) {
            $this->selectedSpotId = null;

            return;
        }

        // Si l'emplacement est occupé pour cette course, afficher les détails
        if ($spot->isOccupiedForRace($this->registration->race_id)) {
            $this->selectedSpotId = null;
            $this->showSpotDetails($spotId);

            return;
        }

        // Si l'emplacement est hors service
        if ($spot->isOutOfService()) {
            $this->selectedSpotId = null;
            $this->addError('spot', 'Cet emplacement est actuellement hors service.');

            return;
        }

        $this->selectedSpotId = $spotId;
    }

    public function confirmSelection()
    {
        $this->authorize('selectPaddockSpot', $this->registration);
        $this->resetErrorBag('spot');

        if (! $this->selectedSpotId) {
            $this->addError('spot', 'Veuillez sélectionner un emplacement');

            return;
        }

        try {
            $spot = PaddockSpot::findOrFail($this->selectedSpotId);
            $useCase = new AssignPaddockSpot;

            $useCase->execute(
                $this->registration,
                $spot,
                auth()->user(),
                false
            );

            session()->flash('success', 'Votre emplacement a été réservé pour la course "'.$this->registration->race->name.'" !');

            // Rediriger vers la page des inscriptions
            return $this->redirect(route('pilot.registrations.index'));
        } catch (ValidationException $e) {
            $this->addError('spot', collect($e->errors())->flatten()->first());
            $this->selectedSpotId = null;
        } catch (\Exception $e) {
            Log::error('Paddock reservation failed', ['registration_id' => $this->registration->id, 'exception' => $e]);
            $this->addError('spot', 'La réservation n’a pas pu être enregistrée. Réessayez.');
        }
    }

    public function releaseSpot()
    {
        $this->resetErrorBag('release');
        try {
            $this->authorize('releasePaddockSpot', $this->registration);

            $useCase = new ReleasePaddockSpot;
            $useCase->execute($this->registration, auth()->user());

            $this->registration = $this->registration->fresh(['paddockSpot']);
            $this->selectedSpotId = null;

            session()->flash('success', 'Votre emplacement a été libéré pour cette course.');
        } catch (AuthorizationException) {
            $this->addError('release', 'Vous ne pouvez pas libérer cet emplacement.');
        } catch (\Exception $e) {
            Log::error('Paddock release failed', ['registration_id' => $this->registration->id, 'exception' => $e]);
            $this->addError('release', 'La libération n’a pas pu être enregistrée. Réessayez.');
        }
    }

    public function showSpotDetails(int $spotId)
    {
        $this->spotDetails = PaddockSpot::findOrFail($spotId);
        $this->showSpotDetails = true;
    }

    public function closeSpotDetails()
    {
        $this->showSpotDetails = false;
        $this->spotDetails = null;
    }

    public function filterByZone(?string $zone)
    {
        $this->selectedZone = $zone;
        $this->selectedSpotId = null;
    }

    public function setViewMode(string $mode)
    {
        $this->viewMode = in_array($mode, ['grid', 'map']) ? $mode : 'grid';
    }

    public function getAvailableSpotsProperty()
    {
        $raceId = $this->registration->race_id;

        $query = PaddockSpot::where('is_available', true) // En service uniquement
            ->orderBy('zone')
            ->orderBy('spot_number');

        if ($this->selectedZone) {
            $query->inZone($this->selectedZone);
        }

        // Charger les inscriptions pour cette course spécifique
        $validStatuses = array_merge(RaceRegistration::engagedStatuses(), ['PENDING_VALIDATION']);
        $query->with(['registrations' => function ($q) use ($raceId, $validStatuses) {
            $q->where('race_id', $raceId)
                ->whereIn('status', $validStatuses);
        }]);

        return $query->get()->map(function ($spot) use ($raceId) {
            // Ajouter des attributs dynamiques pour cette course
            $registration = $spot->registrationForRace($raceId);
            $spot->is_occupied_for_race = $registration !== null;

            return $spot;
        });
    }

    public function getZonesProperty()
    {
        return PaddockSpot::where('is_available', true)
            ->select('zone')
            ->distinct()
            ->orderBy('zone')
            ->pluck('zone');
    }

    public function getStatisticsProperty()
    {
        return PaddockSpot::getStatisticsForRace($this->registration->race_id);
    }

    public function getSpotsForMapProperty()
    {
        $raceId = $this->registration->race_id;

        $query = PaddockSpot::where('is_available', true)
            ->with(['registrations' => fn ($query) => $query->where('race_id', $raceId)
                ->whereIn('status', array_merge(RaceRegistration::engagedStatuses(), ['PENDING_VALIDATION']))])
            ->orderBy('zone')
            ->orderBy('spot_number');

        if ($this->selectedZone) {
            $query->inZone($this->selectedZone);
        }

        return $query->get()
            ->map(function ($spot) use ($raceId) {
                $registration = $spot->registrationForRace($raceId);

                return [
                    'id' => $spot->id,
                    'spot_number' => $spot->spot_number,
                    'zone' => $spot->zone,
                    'position_x' => $spot->position_x,
                    'position_y' => $spot->position_y,
                    'is_available' => $spot->is_available,
                    'is_occupied_for_race' => $registration !== null,
                ];
            });
    }

    public function render()
    {
        return view('livewire.pilot.registrations.paddock-selection', [
            'spots' => $this->viewMode === 'grid' ? $this->availableSpots : collect(),
            'spotsForMap' => $this->viewMode === 'map' ? $this->spotsForMap : collect(),
            'zones' => $this->zones,
            'statistics' => $this->statistics,
        ]);
    }
}
