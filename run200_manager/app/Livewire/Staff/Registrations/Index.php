<?php

namespace App\Livewire\Staff\Registrations;

use App\Application\Registrations\UseCases\AssignPaddock;
use App\Application\Registrations\UseCases\ValidateRegistration;
use App\Domain\Registration\Enums\RegistrationStatus;
use App\Infrastructure\Qr\QrTokenService;
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

    // Tri
    public string $sortBy = 'pilot_name';

    public string $sortDirection = 'asc';

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

    // Modal QR Code
    public bool $showQrModal = false;

    public string $qrCodeSvg = '';

    public ?string $registrationCode = null;

    protected $queryString = [
        'raceId' => ['except' => null, 'as' => 'raceId'],
        'statusFilter' => ['except' => ''],
        'search' => ['except' => ''],
        'sortBy' => ['except' => 'pilot_name'],
        'sortDirection' => ['except' => 'asc'],
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

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function openQrModal(int $registrationId)
    {
        $registration = RaceRegistration::with(['pilot', 'car', 'race'])->find($registrationId);
        if (! $registration) {
            return;
        }

        $this->selectedRegistration = $registration;

        // Générer ou récupérer le token QR
        $qrService = new QrTokenService;
        $token = $qrService->getOrGenerateToken($registration);

        // Générer le code d'inscription (format: RACE_ID-PILOT_ID-REG_ID)
        $this->registrationCode = sprintf(
            '%s-%s-%04d',
            strtoupper(substr($registration->race->name, 0, 3)),
            str_pad($registration->pilot->license_number ?? $registration->pilot_id, 6, '0', STR_PAD_LEFT),
            $registration->id
        );

        // Générer le SVG du QR code
        $this->qrCodeSvg = $qrService->generateQrCodeSvg($token, 250);

        $this->showQrModal = true;
    }

    public function closeQrModal()
    {
        $this->showQrModal = false;
        $this->selectedRegistration = null;
        $this->qrCodeSvg = '';
        $this->registrationCode = null;
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

        // Jointures pour le tri (avant les filtres pour éviter les ambiguïtés)
        $query->join('pilots', 'race_registrations.pilot_id', '=', 'pilots.id')
              ->join('cars', 'race_registrations.car_id', '=', 'cars.id')
              ->join('races', 'race_registrations.race_id', '=', 'races.id')
              ->select('race_registrations.*');

        if ($this->raceId) {
            $query->where('race_registrations.race_id', $this->raceId);
        }

        if ($this->statusFilter) {
            $query->where('race_registrations.status', $this->statusFilter);
        }

        if ($this->search) {
            $searchTerm = $this->search;
            $query->where(function ($q) use ($searchTerm) {
                // Recherche par pilote
                $q->whereHas('pilot', function ($pq) use ($searchTerm) {
                    $pq->where('first_name', 'like', '%'.$searchTerm.'%')
                        ->orWhere('last_name', 'like', '%'.$searchTerm.'%')
                        ->orWhere('license_number', 'like', '%'.$searchTerm.'%');
                })
                // Recherche par numéro de voiture
                ->orWhereHas('car', function ($cq) use ($searchTerm) {
                    $cq->where('race_number', 'like', '%'.$searchTerm.'%');
                });
            });
        }

        // Appliquer le tri
        switch ($this->sortBy) {
            case 'pilot_name':
                $query->orderBy('pilots.last_name', $this->sortDirection)
                      ->orderBy('pilots.first_name', $this->sortDirection);
                break;
            case 'race_number':
                $query->orderBy('cars.race_number', $this->sortDirection);
                break;
            case 'race_date':
                $query->orderBy('races.race_date', $this->sortDirection);
                break;
            case 'status':
                $query->orderBy('race_registrations.status', $this->sortDirection);
                break;
            case 'created_at':
                $query->orderBy('race_registrations.created_at', $this->sortDirection);
                break;
            default:
                $query->orderBy('pilots.last_name', 'asc')
                      ->orderBy('pilots.first_name', 'asc');
        }

        $registrations = $query->paginate(15);

        $races = Race::with('season')
            ->orderBy('race_date', 'desc')
            ->get();

        return view('livewire.staff.registrations.index', [
            'registrations' => $registrations,
            'races' => $races,
        ])->layout('layouts.app');
    }
}
