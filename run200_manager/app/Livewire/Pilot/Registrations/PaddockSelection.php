<?php

namespace App\Livewire\Pilot\Registrations;

use App\Application\Registrations\UseCases\AssignPaddockSpot;
use App\Application\Registrations\UseCases\ReleasePaddockSpot;
use App\Models\PaddockSpot;
use App\Models\RaceRegistration;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
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
        $spot = PaddockSpot::findOrFail($spotId);

        // Si l'emplacement est occupé pour cette course, afficher les détails
        if ($spot->isOccupiedForRace($this->registration->race_id) && ! $this->isStaffOrAdmin()) {
            $this->showSpotDetails($spotId);

            return;
        }

        // Si l'emplacement est hors service
        if ($spot->isOutOfService()) {
            $this->addError('spot', 'Cet emplacement est actuellement hors service.');

            return;
        }

        $this->selectedSpotId = $spotId;
    }

    public function confirmSelection()
    {
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
                $this->isStaffOrAdmin() // Force si staff/admin
            );

            session()->flash('success', 'Votre emplacement a été réservé pour la course "'.$this->registration->race->name.'" !');

            // Rediriger vers la page des inscriptions
            return $this->redirect(route('pilot.registrations.index'));
        } catch (\Exception $e) {
            $this->addError('spot', $e->getMessage());
        }
    }

    public function releaseSpot()
    {
        try {
            $this->authorize('releasePaddockSpot', $this->registration);

            $useCase = new ReleasePaddockSpot;
            $useCase->execute($this->registration, auth()->user());

            $this->registration = $this->registration->fresh(['paddockSpot']);
            $this->selectedSpotId = null;

            session()->flash('success', 'Votre emplacement a été libéré pour cette course.');
        } catch (\Exception $e) {
            $this->addError('release', $e->getMessage());
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
        $query->with(['registrations' => function ($q) use ($raceId) {
            $q->where('race_id', $raceId)
                ->whereIn('status', ['ACCEPTED', 'PENDING_VALIDATION'])
                ->with('pilot.user', 'car');
        }]);

        return $query->get()->map(function ($spot) use ($raceId) {
            // Ajouter des attributs dynamiques pour cette course
            $spot->is_occupied_for_race = $spot->isOccupiedForRace($raceId);
            $spot->registration_for_race = $spot->registrationForRace($raceId);

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

        return PaddockSpot::where('is_available', true)
            ->orderBy('zone')
            ->orderBy('spot_number')
            ->get()
            ->map(function ($spot) use ($raceId) {
                $registration = $spot->registrationForRace($raceId);

                return [
                    'id' => $spot->id,
                    'spot_number' => $spot->spot_number,
                    'zone' => $spot->zone,
                    'position_x' => $spot->position_x,
                    'position_y' => $spot->position_y,
                    'is_available' => $spot->is_available,
                    'is_occupied_for_race' => $spot->isOccupiedForRace($raceId),
                    'pilot_name' => $registration ? $registration->pilot->first_name.' '.$registration->pilot->last_name : null,
                ];
            });
    }

    protected function isStaffOrAdmin(): bool
    {
        return auth()->user()->isStaff() || auth()->user()->isAdmin();
    }

    public function render()
    {
        return view('livewire.pilot.registrations.paddock-selection', [
            'spots' => $this->availableSpots,
            'spotsForMap' => $this->spotsForMap,
            'zones' => $this->zones,
            'statistics' => $this->statistics,
        ]);
    }
}
