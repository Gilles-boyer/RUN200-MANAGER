<?php

namespace App\Livewire\Staff\Paddock;

use App\Application\Registrations\UseCases\AssignPaddockSpot;
use App\Application\Registrations\UseCases\ReleasePaddockSpot;
use App\Models\PaddockSpot;
use App\Models\Race;
use App\Models\RaceRegistration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ManagePaddock extends Component
{
    use WithPagination;

    public ?int $selectedRaceId = null;

    public ?string $selectedZone = null;

    public string $searchSpot = '';

    public string $assignmentSearch = '';

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
        $this->selectedRaceId = Race::whereIn('status', ['OPEN', 'RUNNING'])
            ->orderBy('race_date')
            ->first()?->id;
    }

    public function updatingSearchSpot()
    {
        $this->resetPage();
    }

    public function updatingSelectedRaceId()
    {
        $this->resetPage();
        $this->closeAssignModal();
        $this->selectedSpotId = null;
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
        if (! $this->selectedRaceId) {
            $this->addError('assignment', 'Sélectionnez d’abord une course.');

            return;
        }

        $spot = PaddockSpot::inService()->findOrFail($spotId);
        $this->spotToAssignId = $spotId;
        $this->selectedSpotId = $spot->id;
        $this->registrationToAssignId = null;
        $this->assignmentSearch = '';
        $this->resetErrorBag('assignment');
        $this->showAssignModal = true;
    }

    public function closeAssignModal()
    {
        $this->showAssignModal = false;
        $this->spotToAssignId = null;
        $this->registrationToAssignId = null;
        $this->assignmentSearch = '';
    }

    public function assignSpotToRegistration()
    {
        $this->resetErrorBag('assignment');
        if (! $this->spotToAssignId || ! $this->registrationToAssignId) {
            $this->addError('assignment', 'Veuillez sélectionner un emplacement et une inscription');

            return;
        }

        try {
            $spot = PaddockSpot::findOrFail($this->spotToAssignId);
            $registration = RaceRegistration::findOrFail($this->registrationToAssignId);
            if (! $this->selectedRaceId || $registration->race_id !== $this->selectedRaceId) {
                $this->addError('assignment', 'Choisissez une inscription de la course sélectionnée.');

                return;
            }

            $useCase = new AssignPaddockSpot;
            $useCase->execute($registration, $spot, auth()->user());

            $this->closeAssignModal();
            session()->flash('success', 'Emplacement assigné avec succès !');
        } catch (ValidationException $e) {
            $this->addError('assignment', collect($e->errors())->flatten()->first());
        } catch (\Exception $e) {
            Log::error('Staff paddock assignment failed', ['exception' => $e]);
            $this->addError('assignment', 'L’assignation n’a pas pu être enregistrée. Réessayez.');
        }
    }

    public function quickAssign(int $spotId, int $registrationId)
    {
        try {
            $spot = PaddockSpot::findOrFail($spotId);
            $registration = RaceRegistration::findOrFail($registrationId);

            $useCase = new AssignPaddockSpot;
            if (! $this->selectedRaceId || $registration->race_id !== $this->selectedRaceId) {
                $this->addError('assignment', 'Choisissez une inscription de la course sélectionnée.');

                return;
            }
            $useCase->execute($registration, $spot, auth()->user());

            session()->flash('success', "Emplacement {$spot->spot_number} assigné avec succès !");
        } catch (ValidationException $e) {
            $this->addError('assignment', collect($e->errors())->flatten()->first());
        } catch (\Exception $e) {
            Log::error('Quick paddock assignment failed', ['exception' => $e]);
            $this->addError('assignment', 'L’assignation n’a pas pu être enregistrée. Réessayez.');
        }
    }

    public function releaseSpot(int $registrationId)
    {
        try {
            $registration = RaceRegistration::findOrFail($registrationId);
            if (! $this->selectedRaceId || $registration->race_id !== $this->selectedRaceId) {
                $this->addError('release', 'Cette inscription ne fait pas partie de la course sélectionnée.');

                return;
            }

            $useCase = new ReleasePaddockSpot;
            $useCase->execute($registration, auth()->user());

            session()->flash('success', 'Emplacement libéré avec succès !');
        } catch (\Exception $e) {
            Log::error('Paddock release failed', ['exception' => $e]);
            $this->addError('release', 'La libération n’a pas pu être enregistrée. Réessayez.');
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

        if (trim($this->searchSpot) !== '') {
            $query->where('spot_number', 'like', '%'.trim($this->searchSpot).'%');
        }

        if ($this->showOnlyAvailable && $this->selectedRaceId) {
            $query->availableForRace($this->selectedRaceId);
        }

        // Charger les emplacements
        $spots = $query->get();

        // Si une course est sélectionnée, charger les inscriptions pour cette course
        if ($this->selectedRaceId) {
            $spots->load(['registrations' => fn ($query) => $query->where('race_id', $this->selectedRaceId)
                ->whereIn('status', array_merge(RaceRegistration::engagedStatuses(), ['PENDING_VALIDATION']))
                ->with(['pilot', 'car'])]);
            $spots->each(function ($spot) {
                $registration = $spot->registrationForRace($this->selectedRaceId);
                $spot->setAttribute('registration_for_race', $registration);
                $spot->setAttribute('is_occupied_for_race', $registration !== null);
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
            ->whereIn('status', RaceRegistration::engagedStatuses())
            ->with(['pilot.user', 'car', 'paddockSpot']);

        if ($this->showAssignModal && trim($this->assignmentSearch) !== '') {
            $search = trim($this->assignmentSearch);
            $query->whereHas('pilot', function ($q) use ($search) {
                $q->where('first_name', 'like', '%'.$search.'%')
                    ->orWhere('last_name', 'like', '%'.$search.'%');
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
     * @return Collection<int, array<string, mixed>>
     */
    public function getSpotsForMapProperty(): Collection
    {
        if (! $this->selectedRaceId) {
            /** @var Collection<int, array<string, mixed>> */
            return collect([]);
        }

        $raceId = $this->selectedRaceId;

        $query = PaddockSpot::inService()
            ->orderBy('zone')
            ->orderBy('spot_number');

        if ($this->selectedZone) {
            $query->inZone($this->selectedZone);
        }

        if ($this->showOnlyAvailable) {
            $query->availableForRace($raceId);
        }

        $spots = $query->with(['registrations' => fn ($query) => $query->where('race_id', $raceId)
            ->whereIn('status', array_merge(RaceRegistration::engagedStatuses(), ['PENDING_VALIDATION']))
            ->with(['pilot', 'car'])])->get();
        /** @var Collection<int, array<string, mixed>> $result */
        $result = new Collection;

        foreach ($spots as $spot) {
            $registration = $spot->registrationForRace($raceId);

            $result->push([
                'id' => $spot->id,
                'spot_number' => $spot->spot_number,
                'zone' => $spot->zone,
                'position_x' => $spot->position_x,
                'position_y' => $spot->position_y,
                'is_available' => $spot->is_available,
                'is_occupied_for_race' => $registration !== null,
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
            ->whereIn('status', RaceRegistration::engagedStatuses())
            ->whereNull('paddock_spot_id')
            ->with(['pilot', 'car'])
            ->get();
    }

    public function render()
    {
        return view('livewire.staff.paddock.manage-paddock', [
            'spots' => $this->viewMode === 'grid' ? $this->paddockSpots : collect(),
            'races' => $this->races,
            'zones' => $this->zones,
            'statistics' => $this->statistics,
            'registrationsForAssignment' => $this->registrationsForAssignment,
            'spotsForMap' => $this->viewMode === 'map' ? $this->spotsForMap : collect(),
            'registrationsWithoutSpot' => $this->registrationsWithoutSpot,
        ]);
    }
}
