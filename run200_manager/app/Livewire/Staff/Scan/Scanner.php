<?php

namespace App\Livewire\Staff\Scan;

use App\Application\Registrations\UseCases\ScanCheckpoint;
use App\Domain\Registration\Enums\RegistrationStatus;
use App\Infrastructure\Qr\QrTokenService;
use App\Models\Checkpoint;
use App\Models\Race;
use App\Models\RaceRegistration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Scanner extends Component
{
    use WithPagination;

    public string $checkpointCode;

    public ?Checkpoint $checkpoint = null;

    public string $token = '';

    public string $registrationCode = '';

    public ?array $registrationInfo = null;

    public ?string $scanResult = null;

    public ?string $errorMessage = null;

    public bool $showSuccess = false;

    public bool $alreadyScanned = false;

    public string $scanMode = 'camera'; // 'list', 'camera' or 'manual'

    public ?int $selectedRaceId = null;

    public string $readySearch = '';

    public array $raceStats = [];

    public function mount(string $checkpointCode)
    {
        $this->checkpointCode = strtoupper($checkpointCode);
        $this->checkpoint = Checkpoint::where('code', $this->checkpointCode)->first();

        if (! $this->checkpoint) {
            abort(404, 'Checkpoint non trouvé');
        }

        if (! $this->checkpoint->is_active) {
            abort(403, 'Ce checkpoint est désactivé');
        }

        if (! $this->checkpoint->userCanScan(Auth::user())) {
            abort(403, 'Vous n\'avez pas la permission de scanner ce checkpoint');
        }

        // Pre-select the most recent active race if available
        $latestRace = Race::where('status', 'OPEN')
            ->orderByDesc('race_date')
            ->first();
        if ($latestRace) {
            $this->selectedRaceId = $latestRace->id;
            $this->computeRaceStats();
        }

        if ($this->checkpointCode === 'TECH_CHECK') {
            $this->scanMode = 'list';
        }
    }

    /**
     * Called when the selected race changes
     */
    public function updatedSelectedRaceId(): void
    {
        $this->resetPage();
        $this->computeRaceStats();
    }

    public function updatingReadySearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function readyForTechnicalCheck()
    {
        if ($this->checkpointCode !== 'TECH_CHECK' || ! $this->selectedRaceId) {
            return RaceRegistration::query()->whereRaw('1 = 0')->paginate(12);
        }

        return RaceRegistration::query()
            ->where('race_id', $this->selectedRaceId)
            ->where('status', RegistrationStatus::ADMIN_CHECKED->value)
            ->whereHas('passages', fn ($query) => $query->whereHas('checkpoint', fn ($checkpoint) => $checkpoint->where('code', 'ADMIN_CHECK')))
            ->whereDoesntHave('passages', fn ($query) => $query->whereHas('checkpoint', fn ($checkpoint) => $checkpoint->where('code', 'TECH_CHECK')))
            ->whereDoesntHave('techInspection')
            ->when(trim($this->readySearch) !== '', function ($query) {
                $search = trim($this->readySearch);
                $query->where(function ($query) use ($search) {
                    $query->whereHas('pilot', fn ($pilot) => $pilot->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('license_number', 'like', "%{$search}%"))
                        ->orWhereHas('car', fn ($car) => $car->where('race_number', 'like', "%{$search}%"));
                });
            })
            ->with(['pilot', 'car.category'])
            ->orderBy('created_at')
            ->paginate(12);
    }

    public function validateTechnicalCheck(int $registrationId, ScanCheckpoint $scanUseCase): void
    {
        if ($this->checkpointCode !== 'TECH_CHECK' || ! $this->checkpoint?->userCanScan(Auth::user())) {
            abort(403);
        }

        $this->reset(['registrationInfo', 'scanResult', 'errorMessage', 'showSuccess', 'alreadyScanned']);

        try {
            DB::transaction(function () use ($registrationId, $scanUseCase) {
                $registration = RaceRegistration::query()->lockForUpdate()->find($registrationId);
                if (! $registration || $registration->race_id !== $this->selectedRaceId) {
                    throw new InvalidArgumentException('Cette inscription ne fait pas partie de la course sélectionnée.');
                }
                if ($registration->hasPassedCheckpoint('TECH_CHECK') || $registration->techInspection()->exists()) {
                    throw new InvalidArgumentException('Ce contrôle technique est déjà enregistré.');
                }
                if ($registration->status !== RegistrationStatus::ADMIN_CHECKED->value) {
                    throw new InvalidArgumentException('La vérification administrative doit être terminée avant le contrôle technique.');
                }

                $scanUseCase->scanWithRegistration($registration, 'TECH_CHECK', Auth::user(), 'tech_list');
            });

            $this->showSuccess = true;
            $this->scanResult = 'Contrôle technique validé. Le statut et le suivi des étapes sont à jour.';
            $this->computeRaceStats();
            unset($this->readyForTechnicalCheck);
        } catch (InvalidArgumentException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    /**
     * Compute statistics for the selected race
     */
    public function computeRaceStats(): void
    {
        if (! $this->selectedRaceId) {
            $this->raceStats = [];

            return;
        }

        // Get all registrations for this race (excluding REFUSED and SUBMITTED)
        $acceptedStatuses = [
            RegistrationStatus::ACCEPTED->value,
            RegistrationStatus::ADMIN_CHECKED->value,
            RegistrationStatus::TECH_CHECKED_OK->value,
            RegistrationStatus::TECH_CHECKED_FAIL->value,
            RegistrationStatus::ENTRY_SCANNED->value,
            RegistrationStatus::BRACELET_GIVEN->value,
            RegistrationStatus::RESULTS_IMPORTED->value,
            RegistrationStatus::PUBLISHED->value,
        ];

        $registrations = RaceRegistration::where('race_id', $this->selectedRaceId)
            ->whereIn('status', $acceptedStatuses)
            ->get();

        $total = $registrations->count();

        // Admin checked = status is ADMIN_CHECKED or beyond (means they passed admin check)
        $adminCheckedStatuses = [
            RegistrationStatus::ADMIN_CHECKED->value,
            RegistrationStatus::TECH_CHECKED_OK->value,
            RegistrationStatus::TECH_CHECKED_FAIL->value,
            RegistrationStatus::ENTRY_SCANNED->value,
            RegistrationStatus::BRACELET_GIVEN->value,
            RegistrationStatus::RESULTS_IMPORTED->value,
            RegistrationStatus::PUBLISHED->value,
        ];
        $adminChecked = $registrations->whereIn('status', $adminCheckedStatuses)->count();

        // Tech checked = status is TECH_CHECKED_OK, TECH_CHECKED_FAIL or beyond
        $techCheckedStatuses = [
            RegistrationStatus::TECH_CHECKED_OK->value,
            RegistrationStatus::TECH_CHECKED_FAIL->value,
            RegistrationStatus::ENTRY_SCANNED->value,
            RegistrationStatus::BRACELET_GIVEN->value,
            RegistrationStatus::RESULTS_IMPORTED->value,
            RegistrationStatus::PUBLISHED->value,
        ];
        $techChecked = $registrations->whereIn('status', $techCheckedStatuses)->count();

        // Tech OK only
        $techOkStatuses = [
            RegistrationStatus::TECH_CHECKED_OK->value,
            RegistrationStatus::ENTRY_SCANNED->value,
            RegistrationStatus::BRACELET_GIVEN->value,
            RegistrationStatus::RESULTS_IMPORTED->value,
            RegistrationStatus::PUBLISHED->value,
        ];
        $techOk = $registrations->whereIn('status', $techOkStatuses)->count();

        // Tech FAIL
        $techFail = $registrations->where('status', RegistrationStatus::TECH_CHECKED_FAIL->value)->count();

        $this->raceStats = [
            'total' => $total,
            'admin_checked' => $adminChecked,
            'tech_checked' => $techChecked,
            'tech_ok' => $techOk,
            'tech_fail' => $techFail,
            'admin_pending' => $total - $adminChecked,
            'tech_pending' => $adminChecked - $techChecked,
        ];
    }

    /**
     * Get available races for selection
     */
    public function getAvailableRacesProperty(): Collection
    {
        return Race::whereIn('status', ['OPEN', 'CLOSED'])
            ->orderByDesc('race_date')
            ->get();
    }

    #[On('tokenScanned')]
    public function processToken(?string $token = null): void
    {
        $tokenToProcess = $token ?? $this->token;

        if (empty($tokenToProcess)) {
            $this->errorMessage = 'Veuillez entrer ou scanner un token';

            return;
        }

        $this->reset(['registrationInfo', 'scanResult', 'errorMessage', 'showSuccess', 'alreadyScanned']);

        $qrService = new QrTokenService;
        $scanUseCase = new ScanCheckpoint($qrService);

        // Get registration info
        $this->registrationInfo = $scanUseCase->getRegistrationFromToken($tokenToProcess);

        if (! $this->registrationInfo) {
            $this->errorMessage = 'Token QR invalide ou expiré';

            return;
        }

        // Check if this checkpoint was already scanned
        $passedCheckpoints = $this->registrationInfo['passed_checkpoints'] ?? [];
        if (in_array($this->checkpointCode, $passedCheckpoints)) {
            $this->alreadyScanned = true;
        }

        // Store token for potential scan
        $this->token = $tokenToProcess;
    }

    public function confirmScan(): void
    {
        if (empty($this->token)) {
            $this->errorMessage = 'Aucun token à valider';

            return;
        }

        $qrService = new QrTokenService;
        $scanUseCase = new ScanCheckpoint($qrService);

        try {
            $passage = $scanUseCase->execute($this->token, $this->checkpointCode, Auth::user());

            $this->showSuccess = true;
            $this->scanResult = 'Scan effectué avec succès !';
            $this->registrationInfo = $scanUseCase->getRegistrationFromToken($this->token);

            if ($this->checkpointCode === 'TECH_CHECK') {
                $this->computeRaceStats();
                unset($this->readyForTechnicalCheck);
            }

            // Dispatch browser event for sound/vibration feedback
            $this->dispatch('scan-success');

        } catch (InvalidArgumentException $e) {
            $this->errorMessage = $e->getMessage();
            $this->dispatch('scan-error');
        }
    }

    public function setScanMode(string $mode): void
    {
        if (! in_array($mode, $this->checkpointCode === 'TECH_CHECK' ? ['list', 'camera', 'manual'] : ['camera', 'manual'], true)) {
            return;
        }
        $this->scanMode = $mode;
    }

    public function resetScanner(): void
    {
        $this->reset(['token', 'registrationCode', 'registrationInfo', 'scanResult', 'errorMessage', 'showSuccess', 'alreadyScanned']);
    }

    /**
     * Search by registration code (format: XXX-NNNNNN-RRRR)
     */
    public function searchByRegistrationCode(): void
    {
        if (empty($this->registrationCode)) {
            $this->errorMessage = 'Veuillez entrer un code d\'inscription';

            return;
        }

        $this->reset(['registrationInfo', 'scanResult', 'errorMessage', 'showSuccess', 'alreadyScanned']);

        // Parse registration code: XXX-NNNNNN-RRRR
        $code = strtoupper(trim($this->registrationCode));

        // Try to extract registration ID from the code
        if (preg_match('/^([A-Z]{2,5})-([0-9]+)-([0-9]+)$/', $code, $matches)) {
            $registrationId = (int) $matches[3];

            $registration = RaceRegistration::with(['pilot', 'car.category', 'race', 'passages.checkpoint'])
                ->find($registrationId);

            if ($registration) {
                // Verify code matches
                $expectedCode = sprintf(
                    '%s-%s-%04d',
                    strtoupper(substr($registration->race->name, 0, 3)),
                    str_pad($registration->pilot->license_number ?? $registration->pilot_id, 6, '0', STR_PAD_LEFT),
                    $registration->id
                );

                if ($code === $expectedCode) {
                    $passedCheckpoints = $registration->passages->pluck('checkpoint.code')->toArray();

                    // Load payments for isPaid check
                    $registration->load('payments');

                    $this->registrationInfo = [
                        'registration' => $registration,
                        'pilot' => $registration->pilot,
                        'car' => $registration->car,
                        'race' => $registration->race,
                        'status' => $registration->status,
                        'paddock' => $registration->paddock,
                        'passed_checkpoints' => $passedCheckpoints,
                        'is_paid' => $registration->isPaid(),
                    ];

                    // Check if this checkpoint was already scanned
                    if (in_array($this->checkpointCode, $passedCheckpoints)) {
                        $this->alreadyScanned = true;
                    }

                    // Generate token for scan validation
                    $qrService = new QrTokenService;
                    $this->token = $qrService->getOrGenerateToken($registration);

                    return;
                }
            }
        }

        $this->errorMessage = 'Code d\'inscription invalide ou non trouvé';
    }

    public function render()
    {
        return view('livewire.staff.scan.scanner')
            ->layout('layouts.app');
    }
}
