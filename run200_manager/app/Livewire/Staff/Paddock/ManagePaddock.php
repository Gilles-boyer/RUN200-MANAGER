<?php

namespace App\Livewire\Staff\Paddock;

use App\Application\Registrations\UseCases\AssignPaddockSpot;
use App\Application\Registrations\UseCases\ReleasePaddockSpot;
use App\Models\PaddockSpot;
use App\Models\Race;
use App\Models\RaceRegistration;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ManagePaddock extends Component
{
    use WithPagination;

    public ?int $selectedRaceId = null;

    public ?string $selectedZone = null;

    public string $searchPilot = '';

    public bool $showOnlyAvailable = false;

    public bool $showAssignModal = false;

    public ?int $spotToAssignId = null;

    public ?int $registrationToAssignId = null;

    public string $viewMode = 'grid'; // 'grid' ou 'map'

    public ?int $selectedSpotId = null;

    public bool $showSpotDetailsModal = false;

    public ?PaddockSpot $spotDetails = null;

    public function mount()
    {
        // Sélectionner automatiquement la prochaine course
        $this->selectedRaceId = Race::where('status', '!=', 'COMPLETED')
            ->orderBy('race_date')
            ->first()?->id;
    }

    public function updatingSearchPilot()
    {
        $this->resetPage();
    }

    public function updatingSelectedRaceId()
    {
        $this->resetPage();
    }

    public function filterByZone(?string $zone)
    {
        $this->selectedZone = $zone;
        $this->resetPage();
    }

    public function setViewMode(string $mode)
    {
        $this->viewMode = in_array($mode, ['grid', 'map']) ? $mode : 'grid';
    }

    public function selectSpot(int $spotId)
    {
        $this->selectedSpotId = $spotId;

        // En mode map, on ouvre directement la modal d'assignation si une course est sélectionnée
        if ($this->viewMode === 'map' && $this->selectedRaceId) {
            $this->openAssignModal($spotId);
        }
    }

    public function showSpotDetails(int $spotId)
    {
        $this->spotDetails = PaddockSpot::find($spotId);
        $this->showSpotDetailsModal = true;
    }

    public function closeSpotDetailsModal()
    {
        $this->showSpotDetailsModal = false;
        $this->spotDetails = null;
    }

    public function toggleShowOnlyAvailable()
    {
        $this->showOnlyAvailable = ! $this->showOnlyAvailable;
        $this->resetPage();
    }

    public function openAssignModal(int $spotId)
    {
        $this->spotToAssignId = $spotId;
        $this->showAssignModal = true;
    }

    public function closeAssignModal()
    {
        $this->showAssignModal = false;
        $this->spotToAssignId = null;
        $this->registrationToAssignId = null;
    }

    public function assignSpotToRegistration()
    {
        if (! $this->spotToAssignId || ! $this->registrationToAssignId) {
            $this->addError('assignment', 'Veuillez sélectionner un emplacement et une inscription');

            return;
        }

        try {
            $spot = PaddockSpot::findOrFail($this->spotToAssignId);
            $registration = RaceRegistration::findOrFail($this->registrationToAssignId);

            $useCase = new AssignPaddockSpot;
            $useCase->execute($registration, $spot, auth()->user(), true);

            $this->closeAssignModal();
            session()->flash('success', 'Emplacement assigné avec succès !');
        } catch (\Exception $e) {
            $this->addError('assignment', $e->getMessage());
        }
    }

    public function quickAssign(int $spotId, int $registrationId)
    {
        try {
            $spot = PaddockSpot::findOrFail($spotId);
            $registration = RaceRegistration::findOrFail($registrationId);

            $useCase = new AssignPaddockSpot;
            $useCase->execute($registration, $spot, auth()->user(), true);

            session()->flash('success', "Emplacement {$spot->spot_number} assigné avec succès !");
        } catch (\Exception $e) {
            $this->addError('assignment', $e->getMessage());
        }
    }

    public function releaseSpot(int $registrationId)
    {
        try {
            $registration = RaceRegistration::findOrFail($registrationId);

            $useCase = new ReleasePaddockSpot;
            $useCase->execute($registration, auth()->user());

            session()->flash('success', 'Emplacement libéré avec succès !');
        } catch (\Exception $e) {
            $this->addError('release', $e->getMessage());
        }
    }

    public function getPaddockSpotsProperty()
    {
        $query = PaddockSpot::query()
            ->inService() // Uniquement les emplacements en service
            ->orderBy('zone')
            ->orderBy('spot_number');

        if ($this->selectedZone) {
            $query->inZone($this->selectedZone);
        }

        if ($this->showOnlyAvailable && $this->selectedRaceId) {
            $query->availableForRace($this->selectedRaceId);
        }

        // Charger les emplacements
        $spots = $query->get();

        // Si une course est sélectionnée, charger les inscriptions pour cette course
        if ($this->selectedRaceId) {
            $spots->each(function ($spot) {
                $spot->setAttribute('registration_for_race', $spot->registrationForRace($this->selectedRaceId));
                $spot->setAttribute('is_occupied_for_race', $spot->isOccupiedForRace($this->selectedRaceId));
            });
        }

        return $spots;
    }

    public function getRegistrationsForAssignmentProperty()
    {
        if (! $this->selectedRaceId) {
            return collect();
        }

        $query = RaceRegistration::where('race_id', $this->selectedRaceId)
            ->where('status', 'ACCEPTED')
            ->with(['pilot.user', 'car', 'paddockSpot']);

        if ($this->searchPilot) {
            $query->whereHas('pilot', function ($q) {
                $q->where('first_name', 'like', '%'.$this->searchPilot.'%')
                    ->orWhere('last_name', 'like', '%'.$this->searchPilot.'%');
            });
        }

        return $query->get();
    }

    public function getRacesProperty()
    {
        return Race::where('status', '!=', 'COMPLETED')
            ->orderBy('race_date', 'desc')
            ->get();
    }

    public function getZonesProperty()
    {
        return PaddockSpot::select('zone')
            ->distinct()
            ->orderBy('zone')
            ->pluck('zone');
    }

    public function getStatisticsProperty()
    {
        // Si une course est sélectionnée, statistiques pour cette course
        if ($this->selectedRaceId) {
            return PaddockSpot::getStatisticsForRace($this->selectedRaceId);
        }

        // Sinon statistiques globales
        return PaddockSpot::getGlobalStatistics();
    }

    /**
     * Get spots data for map display.
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    public function getSpotsForMapProperty(): \Illuminate\Support\Collection
    {
        if (! $this->selectedRaceId) {
            /** @var \Illuminate\Support\Collection<int, array<string, mixed>> */
            return collect([]);
        }

        $raceId = $this->selectedRaceId;

        $query = PaddockSpot::inService()
            ->orderBy('zone')
            ->orderBy('spot_number');

        if ($this->selectedZone) {
            $query->inZone($this->selectedZone);
        }

        $spots = $query->get();
        /** @var \Illuminate\Support\Collection<int, array<string, mixed>> $result */
        $result = new \Illuminate\Support\Collection();

        foreach ($spots as $spot) {
            $registration = $spot->registrationForRace($raceId);

            $result->push([
                'id' => $spot->id,
                'spot_number' => $spot->spot_number,
                'zone' => $spot->zone,
                'position_x' => $spot->position_x,
                'position_y' => $spot->position_y,
                'is_available' => $spot->is_available,
                'is_occupied_for_race' => $spot->isOccupiedForRace($raceId),
                'pilot_name' => $registration ? $registration->pilot->first_name.' '.$registration->pilot->last_name : null,
                'car_number' => $registration?->car?->race_number,
            ]);
        }

        return $result;
    }

    public function getRegistrationsWithoutSpotProperty()
    {
        if (! $this->selectedRaceId) {
            return collect();
        }

        return RaceRegistration::where('race_id', $this->selectedRaceId)
            ->where('status', 'ACCEPTED')
            ->whereNull('paddock_spot_id')
            ->with(['pilot', 'car'])
            ->get();
    }

    public function render()
    {
        return view('livewire.staff.paddock.manage-paddock', [
            'spots' => $this->paddockSpots,
            'races' => $this->races,
            'zones' => $this->zones,
            'statistics' => $this->statistics,
            'registrationsForAssignment' => $this->registrationsForAssignment,
            'spotsForMap' => $this->spotsForMap,
            'registrationsWithoutSpot' => $this->registrationsWithoutSpot,
        ]);
    }
}
