<?php

namespace App\Livewire\Staff\Registrations;

use App\Application\Registrations\UseCases\AssignPaddock;
use App\Application\Registrations\UseCases\ValidateRegistration;
use App\Domain\Registration\Enums\RegistrationStatus;
use App\Models\Checkpoint;
use App\Models\Race;
use App\Models\RaceRegistration;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public ?int $raceId = null;

    public string $statusFilter = '';

    public string $search = '';

    // Modal validation
    public bool $showValidationModal = false;

    public ?RaceRegistration $selectedRegistration = null;

    public string $validationAction = '';

    public string $refusalReason = '';

    // Modal paddock
    public bool $showPaddockModal = false;

    public string $paddockNumber = '';

    // Modal changement de statut
    public bool $showStatusModal = false;

    public string $newStatus = '';

    public string $statusChangeReason = '';

    protected $queryString = [
        'raceId' => ['except' => null, 'as' => 'raceId'],
        'statusFilter' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    #[Computed]
    public function checkpoints()
    {
        return Checkpoint::active()->ordered()->get();
    }

    #[Computed]
    public function availableStatuses()
    {
        return collect(RegistrationStatus::cases())->mapWithKeys(function ($status) {
            return [$status->value => $status->label()];
        })->toArray();
    }

    public function mount($raceId = null, $statusFilter = null): void
    {
        if ($raceId !== null) {
            $this->raceId = (int) $raceId;
        }
        if ($statusFilter !== null) {
            $this->statusFilter = $statusFilter;
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function openValidationModal(int $registrationId, string $action)
    {
        $this->selectedRegistration = RaceRegistration::with(['pilot', 'car', 'race'])->find($registrationId);
        $this->validationAction = $action;
        $this->refusalReason = '';
        $this->showValidationModal = true;
    }

    public function closeValidationModal()
    {
        $this->showValidationModal = false;
        $this->selectedRegistration = null;
        $this->validationAction = '';
        $this->refusalReason = '';
    }

    public function confirmValidation()
    {
        if (! $this->selectedRegistration) {
            return;
        }

        try {
            $useCase = new ValidateRegistration;

            if ($this->validationAction === 'accept') {
                $useCase->accept($this->selectedRegistration);
                session()->flash('success', 'Inscription acceptée avec succès.');
            } else {
                $this->validate([
                    'refusalReason' => 'required|min:10',
                ], [
                    'refusalReason.required' => 'La raison du refus est obligatoire.',
                    'refusalReason.min' => 'La raison doit contenir au moins 10 caractères.',
                ]);

                $useCase->refuse($this->selectedRegistration, $this->refusalReason);
                session()->flash('success', 'Inscription refusée.');
            }
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }

        $this->closeValidationModal();
    }

    public function openPaddockModal(int $registrationId)
    {
        $this->selectedRegistration = RaceRegistration::with(['pilot', 'car', 'race'])->find($registrationId);
        $this->paddockNumber = $this->selectedRegistration->paddock ?? '';
        $this->showPaddockModal = true;
    }

    public function closePaddockModal()
    {
        $this->showPaddockModal = false;
        $this->selectedRegistration = null;
        $this->paddockNumber = '';
    }

    public function assignPaddock()
    {
        if (! $this->selectedRegistration) {
            return;
        }

        $this->validate([
            'paddockNumber' => 'required|string|max:20',
        ], [
            'paddockNumber.required' => 'Le numéro de paddock est obligatoire.',
        ]);

        try {
            $useCase = new AssignPaddock;
            $useCase->execute($this->selectedRegistration, $this->paddockNumber);
            session()->flash('success', 'Paddock assigné avec succès.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }

        $this->closePaddockModal();
    }

    public function openStatusModal(int $registrationId)
    {
        $this->selectedRegistration = RaceRegistration::with(['pilot', 'car', 'race'])->find($registrationId);
        $this->newStatus = $this->selectedRegistration->status;
        $this->statusChangeReason = '';
        $this->showStatusModal = true;
    }

    public function closeStatusModal()
    {
        $this->showStatusModal = false;
        $this->selectedRegistration = null;
        $this->newStatus = '';
        $this->statusChangeReason = '';
    }

    public function updateStatus()
    {
        if (! $this->selectedRegistration) {
            return;
        }

        $this->validate([
            'newStatus' => 'required|in:'.implode(',', RegistrationStatus::values()),
        ], [
            'newStatus.required' => 'Le statut est obligatoire.',
            'newStatus.in' => 'Le statut sélectionné est invalide.',
        ]);

        try {
            $oldStatus = $this->selectedRegistration->status;

            $updateData = ['status' => $this->newStatus];

            // Si on passe à REFUSED, on peut ajouter la raison
            if ($this->newStatus === RegistrationStatus::REFUSED->value && $this->statusChangeReason) {
                $updateData['reason'] = $this->statusChangeReason;
            }

            // Si on passe à ACCEPTED ou autre, on garde la validation
            if (in_array($this->newStatus, [RegistrationStatus::ACCEPTED->value, RegistrationStatus::ADMIN_CHECKED->value])) {
                $updateData['validated_at'] = now();
                $updateData['validated_by'] = auth()->id();
            }

            $this->selectedRegistration->update($updateData);

            $statusLabel = RegistrationStatus::from($this->newStatus)->label();
            session()->flash('success', "Statut modifié avec succès : {$statusLabel}");
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors de la modification du statut : '.$e->getMessage());
        }

        $this->closeStatusModal();
    }

    public function render()
    {
        $query = RaceRegistration::with(['pilot', 'car.category', 'race.season', 'passages.checkpoint', 'payments']);

        if ($this->raceId) {
            $query->where('race_id', $this->raceId);
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->search) {
            $query->whereHas('pilot', function ($q) {
                $q->where('first_name', 'like', '%'.$this->search.'%')
                    ->orWhere('last_name', 'like', '%'.$this->search.'%')
                    ->orWhere('license_number', 'like', '%'.$this->search.'%');
            });
        }

        $registrations = $query->orderBy('created_at', 'desc')->paginate(15);

        $races = Race::with('season')
            ->orderBy('race_date', 'desc')
            ->get();

        return view('livewire.staff.registrations.index', [
            'registrations' => $registrations,
            'races' => $races,
        ])->layout('layouts.app');
    }
}
