<?php

namespace App\Livewire\Staff\Registrations;

use App\Application\Registrations\UseCases\AssignPaddock;
use App\Application\Registrations\UseCases\ValidateRegistration;
use App\Application\Registrations\UseCases\RecordLicenseCheck;
use App\Infrastructure\Qr\QrTokenService;
use App\Models\Checkpoint;
use App\Models\Race;
use App\Models\RaceRegistration;
use App\Support\UserFacingError;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public ?int $raceId = null;

    public string $statusFilter = '';

    public string $licenseFilter = '';

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

    // Modal QR Code
    public bool $showQrModal = false;

    public string $qrCodeSvg = '';

    public ?string $registrationCode = null;

    public bool $showLicenseModal = false;

    public ?int $licenseRegistrationId = null;

    public string $licenseReturnFocusId = '';

    public array $licenseExpected = [];

    public string $licenseResult = 'needs_review';

    public string $licenseSource = 'ligue_ffsa';

    public string $licenseSourceAccessStatus = 'incomplete';

    public string $licenseTypeObserved = '';

    public string $licenseStateObserved = '';

    public string $licenseYearObserved = '';

    public string $licenseIdentityMatches = '';

    public string $licenseNumberMatches = '';

    public string $licenseValidForRaceDate = '';

    public string $licenseValidUntil = '';

    public string $licenseReasonCode = '';

    public string $licenseNote = '';

    protected $queryString = [
        'raceId' => ['except' => null, 'as' => 'raceId'],
        'statusFilter' => ['except' => ''],
        'licenseFilter' => ['except' => ''],
        'search' => ['except' => ''],
        'sortBy' => ['except' => 'pilot_name'],
        'sortDirection' => ['except' => 'asc'],
    ];

    #[Computed]
    public function checkpoints()
    {
        return Checkpoint::active()->ordered()->get();
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

    public function updatingLicenseFilter(): void
    {
        $this->resetPage();
    }

    public function openLicenseModal(int $registrationId, string $origin = 'desktop'): void
    {
        Gate::authorize('license.verify');

        $registration = RaceRegistration::with(['pilot', 'race', 'licenseCheck'])->findOrFail($registrationId);
        if ($registration->licenseCheckState() === 'not_applicable') {
            session()->flash('error', 'Aucun contrôle de licence requis pour cette course.');

            return;
        }

        $check = $registration->licenseCheck;
        $this->licenseRegistrationId = $registration->id;
        $this->licenseReturnFocusId = 'license-check-'.($origin === 'mobile' ? 'mobile' : 'desktop').'-'.$registration->id;
        $this->licenseExpected = [
            'pilot_id' => $registration->pilot_id,
            'license_number' => (string) $registration->pilot->license_number,
            'first_name' => (string) $registration->pilot->first_name,
            'last_name' => (string) $registration->pilot->last_name,
            'race_date' => $registration->race->race_date->format('Y-m-d'),
            'required_license_code' => $registration->race->required_license_code,
            'check_id' => $check?->id,
            'check_updated_at' => $check?->updated_at?->format('Y-m-d H:i:s.u'),
            'check_revision' => $check?->revision,
        ];
        $this->licenseResult = $check?->result ?? 'needs_review';
        $this->licenseSource = $check?->source ?? 'ligue_ffsa';
        $this->licenseSourceAccessStatus = $check?->source_access_status ?? 'incomplete';
        $this->licenseTypeObserved = $check?->license_type_observed ?? '';
        $this->licenseStateObserved = $check?->license_state_observed ?? '';
        $this->licenseYearObserved = (string) ($check?->license_year_observed ?? '');
        $this->licenseIdentityMatches = $check?->identity_matches === null ? '' : ($check->identity_matches ? 'yes' : 'no');
        $this->licenseNumberMatches = $check?->license_number_matches === null ? '' : ($check->license_number_matches ? 'yes' : 'no');
        $this->licenseValidForRaceDate = $check?->valid_for_race_date === null ? '' : ($check->valid_for_race_date ? 'yes' : 'no');
        $this->licenseValidUntil = $check?->valid_until?->format('Y-m-d') ?? '';
        $this->licenseReasonCode = $check?->reason_code ?? '';
        $this->licenseNote = $check?->note ?? '';
        $this->resetValidation();
        $this->showLicenseModal = true;
    }

    public function closeLicenseModal(): void
    {
        $focusId = $this->licenseReturnFocusId;
        $this->showLicenseModal = false;
        $this->licenseRegistrationId = null;
        $this->licenseExpected = [];
        $this->licenseReturnFocusId = '';
        $this->resetValidation();
        if ($focusId !== '') {
            $this->dispatch('license-modal-closed', id: $focusId);
        }
    }

    public function updatedLicenseResult(): void
    {
        $this->licenseReasonCode = '';
    }

    public function updatedLicenseSource(): void
    {
        if ($this->licenseSource !== 'ligue_ffsa' && $this->licenseResult !== 'needs_review') {
            $this->licenseResult = 'needs_review';
            $this->licenseReasonCode = '';
        }
        if ($this->licenseSource === 'official_ffsa_document') {
            $this->licenseResult = 'needs_review';
            $this->licenseReasonCode = 'document_only';
        }
    }

    public function updatedLicenseSourceAccessStatus(): void
    {
        if ($this->licenseSourceAccessStatus !== 'consulted' && $this->licenseResult !== 'needs_review') {
            $this->licenseResult = 'needs_review';
            $this->licenseReasonCode = '';
        }
        if ($this->licenseSourceAccessStatus === 'unavailable') {
            $this->licenseResult = 'needs_review';
            $this->licenseReasonCode = 'source_unavailable';
        }
    }

    public function getCanConfirmValidLicenseProperty(): bool
    {
        return $this->licenseSource === 'ligue_ffsa'
            && $this->licenseSourceAccessStatus === 'consulted'
            && strtoupper(trim($this->licenseTypeObserved)) === strtoupper((string) ($this->licenseExpected['required_license_code'] ?? ''))
            && $this->licenseStateObserved === 'active'
            && $this->licenseYearObserved === substr((string) ($this->licenseExpected['race_date'] ?? ''), 0, 4)
            && $this->licenseIdentityMatches === 'yes'
            && $this->licenseNumberMatches === 'yes'
            && $this->licenseValidForRaceDate === 'yes'
            && $this->licenseValidUntil !== ''
            && $this->licenseValidUntil >= ($this->licenseExpected['race_date'] ?? '');
    }

    public function getCanConfirmInvalidLicenseProperty(): bool
    {
        if ($this->licenseSource !== 'ligue_ffsa' || $this->licenseSourceAccessStatus !== 'consulted'
            || $this->licenseIdentityMatches !== 'yes' || $this->licenseNumberMatches !== 'yes'
            || $this->licenseValidForRaceDate !== 'no') {
            return false;
        }

        $typeMatches = strtoupper(trim($this->licenseTypeObserved)) === strtoupper((string) ($this->licenseExpected['required_license_code'] ?? ''));
        $yearMatches = $this->licenseYearObserved === substr((string) ($this->licenseExpected['race_date'] ?? ''), 0, 4);
        $expiredBeforeRace = $this->licenseValidUntil !== '' && $this->licenseValidUntil < ($this->licenseExpected['race_date'] ?? '');

        return match ($this->licenseReasonCode) {
            'revoked' => $this->licenseStateObserved === 'suspended' && $typeMatches && $yearMatches,
            'expired' => $this->licenseStateObserved === 'expired' && $typeMatches && $yearMatches && $expiredBeforeRace,
            'no_current_license_confirmed' => $this->licenseStateObserved === 'inactive'
                && ($this->licenseTypeObserved === '' || $typeMatches)
                && ($this->licenseYearObserved === '' || $yearMatches)
                && ($this->licenseValidUntil === '' || $expiredBeforeRace),
            default => false,
        };
    }

    public function confirmLicenseCheck(): void
    {
        Gate::authorize('license.verify');

        if (! $this->showLicenseModal || ! $this->licenseRegistrationId) {
            return;
        }

        $this->validate([
            'licenseResult' => ['required', 'in:valid,invalid,needs_review'],
            'licenseSource' => ['required', 'in:verification_ffsa,ligue_ffsa,licence_ffsa,official_ffsa_document'],
            'licenseSourceAccessStatus' => ['required', 'in:consulted,unavailable,incomplete'],
            'licenseTypeObserved' => ['nullable', 'string', 'max:32'],
            'licenseStateObserved' => ['nullable', 'in:active,inactive,expired,suspended,unknown'],
            'licenseYearObserved' => ['nullable', 'digits:4'],
            'licenseIdentityMatches' => ['nullable', 'in:yes,no'],
            'licenseNumberMatches' => ['nullable', 'in:yes,no'],
            'licenseValidForRaceDate' => ['nullable', 'in:yes,no'],
            'licenseValidUntil' => ['nullable', 'date_format:Y-m-d'],
            'licenseReasonCode' => [$this->licenseResult === 'valid' ? 'nullable' : 'required', 'string', 'max:40'],
            'licenseNote' => ['nullable', 'string', 'max:500'],
        ], [
            'licenseYearObserved.digits' => 'Indiquez une année à quatre chiffres.',
            'licenseValidUntil.date_format' => 'Indiquez une date au format AAAA-MM-JJ.',
            'licenseReasonCode.required' => 'Choisissez le motif du contrôle.',
        ]);

        if ($this->licenseResult === 'valid' && ! $this->canConfirmValidLicense) {
            $this->addError('licenseResult', 'La validation RCCR exige la recherche FFSA consultée, le code RCCR, l’état Active, l’année et la date de fin valables, ainsi que l’identité et le numéro concordants.');

            return;
        }

        if ($this->licenseResult === 'invalid' && ! $this->canConfirmInvalidLicense) {
            $this->addError('licenseResult', 'L’alerte rouge exige une preuve FFSA cohérente : licence expirée, suspendue ou inactive, identité et numéro concordants, et non valable le jour de la course. En cas de doute, choisissez « À examiner ».');

            return;
        }

        try {
            (new RecordLicenseCheck)->execute($this->licenseRegistrationId, [
                'result' => $this->licenseResult,
                'source' => $this->licenseSource,
                'source_access_status' => $this->licenseSourceAccessStatus,
                'license_type_observed' => $this->licenseTypeObserved ?: null,
                'license_state_observed' => $this->licenseStateObserved ?: null,
                'license_year_observed' => $this->licenseYearObserved === '' ? null : (int) $this->licenseYearObserved,
                'identity_matches' => $this->licenseIdentityMatches === '' ? null : $this->licenseIdentityMatches === 'yes',
                'license_number_matches' => $this->licenseNumberMatches === '' ? null : $this->licenseNumberMatches === 'yes',
                'valid_for_race_date' => $this->licenseValidForRaceDate === '' ? null : $this->licenseValidForRaceDate === 'yes',
                'valid_until' => $this->licenseValidUntil ?: null,
                'reason_code' => $this->licenseResult === 'valid' ? null : ($this->licenseReasonCode ?: null),
                'note' => $this->licenseNote ?: null,
            ], $this->licenseExpected);
            $this->closeLicenseModal();
            session()->flash('success', 'Contrôle de licence enregistré.');
        } catch (ValidationException $e) {
            $this->addError('licenseResult', collect($e->errors())->flatten()->first() ?? 'Le contrôle n’a pas pu être enregistré. Vérifiez le dossier.');
        } catch (\Throwable $e) {
            $this->addError('licenseResult', UserFacingError::message($e, 'Impossible d’enregistrer le contrôle. Actualisez le dossier et réessayez.'));
        }
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
        Gate::authorize('race_registration.validate');

        if (! in_array($action, ['accept', 'refuse'], true)) {
            throw ValidationException::withMessages(['validationAction' => 'Cette décision est invalide.']);
        }

        $registration = RaceRegistration::with(['pilot', 'car', 'race'])->findOrFail($registrationId);
        if (! $registration->isPending() && ! ($action === 'accept' && $registration->isPendingPayment())) {
            session()->flash('error', 'Le statut de cette inscription a changé. Actualisez la liste avant de continuer.');

            return;
        }

        $this->selectedRegistration = $registration;
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
        Gate::authorize('race_registration.validate');

        if (! $this->selectedRegistration) {
            return;
        }

        $this->validate(['validationAction' => ['required', 'in:accept,refuse']]);

        try {
            $useCase = new ValidateRegistration;
            $registration = RaceRegistration::findOrFail($this->selectedRegistration->id);

            if ($this->validationAction === 'accept') {
                $validated = $registration->isPendingPayment()
                    ? $useCase->acceptBeforePayment($registration)
                    : $useCase->accept($registration);
                $balanceCents = max(0, (int) $validated->race->entry_fee_cents - $validated->getPaidAmountCents());
                session()->flash('success', $balanceCents === 0
                    ? 'Inscription acceptée avec succès.'
                    : 'Inscription acceptée. Solde à régler : '.number_format($balanceCents / 100, 2, ',', ' ').' €.');
            } else {
                $this->validate([
                    'refusalReason' => 'required|min:10',
                ], [
                    'refusalReason.required' => 'La raison du refus est obligatoire.',
                    'refusalReason.min' => 'La raison doit contenir au moins 10 caractères.',
                ]);

                $useCase->refuse($registration, $this->refusalReason);
                session()->flash('success', 'Inscription refusée.');
            }
        } catch (ValidationException $e) {
            throw $e;
        } catch (\InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage() === 'Seules les inscriptions en attente peuvent être validées'
                ? 'Le statut de cette inscription a changé. Actualisez la liste avant de continuer.'
                : $e->getMessage());
        } catch (\Exception $e) {
            session()->flash('error', UserFacingError::message($e, 'Impossible de valider cette inscription.'));
        }

        $this->closeValidationModal();
    }

    public function openPaddockModal(int $registrationId)
    {
        Gate::authorize('race_registration.assign_paddock');

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
        Gate::authorize('race_registration.assign_paddock');

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
            $useCase->execute(RaceRegistration::findOrFail($this->selectedRegistration->id), $this->paddockNumber);
            session()->flash('success', 'Paddock assigné avec succès.');
        } catch (\InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            session()->flash('error', UserFacingError::message($e, 'Impossible d’attribuer ce paddock.'));
        }

        $this->closePaddockModal();
    }

    public function render()
    {
        $query = RaceRegistration::with(['pilot', 'car.category', 'race.season', 'licenseCheck', 'passages.checkpoint', 'payments']);

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

        if ($this->licenseFilter !== '' && in_array($this->licenseFilter, ['not_applicable', 'not_checked', 'stale', 'valid', 'invalid', 'needs_review'], true)) {
            $query->whereLicenseCheckState($this->licenseFilter);
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
            'licenseRegistration' => $this->showLicenseModal && $this->licenseRegistrationId
                ? RaceRegistration::with(['pilot', 'race', 'licenseCheck'])->find($this->licenseRegistrationId)
                : null,
        ])->layout('layouts.app');
    }
}
