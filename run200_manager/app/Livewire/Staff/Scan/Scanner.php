<?php

namespace App\Livewire\Staff\Scan;

use App\Application\Registrations\UseCases\ScanCheckpoint;
use App\Infrastructure\Qr\QrTokenService;
use App\Models\Checkpoint;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class Scanner extends Component
{
    public string $checkpointCode;

    public ?Checkpoint $checkpoint = null;

    public string $token = '';

    public string $registrationCode = '';

    public ?array $registrationInfo = null;

    public ?string $scanResult = null;

    public ?string $errorMessage = null;

    public bool $showSuccess = false;

    public string $scanMode = 'camera'; // 'camera' or 'manual'

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
    }

    #[On('tokenScanned')]
    public function processToken(?string $token = null): void
    {
        $tokenToProcess = $token ?? $this->token;

        if (empty($tokenToProcess)) {
            $this->errorMessage = 'Veuillez entrer ou scanner un token';

            return;
        }

        $this->reset(['registrationInfo', 'scanResult', 'errorMessage', 'showSuccess']);

        $qrService = new QrTokenService;
        $scanUseCase = new ScanCheckpoint($qrService);

        // Get registration info
        $this->registrationInfo = $scanUseCase->getRegistrationFromToken($tokenToProcess);

        if (! $this->registrationInfo) {
            $this->errorMessage = 'Token QR invalide ou expiré';

            return;
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

            // Dispatch browser event for sound/vibration feedback
            $this->dispatch('scan-success');

        } catch (\InvalidArgumentException $e) {
            $this->errorMessage = $e->getMessage();
            $this->dispatch('scan-error');
        }
    }

    public function setScanMode(string $mode): void
    {
        $this->scanMode = $mode;
    }

    public function resetScanner(): void
    {
        $this->reset(['token', 'registrationCode', 'registrationInfo', 'scanResult', 'errorMessage', 'showSuccess']);
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

        $this->reset(['registrationInfo', 'scanResult', 'errorMessage', 'showSuccess']);

        // Parse registration code: XXX-NNNNNN-RRRR
        $code = strtoupper(trim($this->registrationCode));

        // Try to extract registration ID from the code
        if (preg_match('/^([A-Z]{2,5})-([0-9]+)-([0-9]+)$/', $code, $matches)) {
            $registrationId = (int) $matches[3];

            $registration = \App\Models\RaceRegistration::with(['pilot', 'car.category', 'race', 'passages.checkpoint'])
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

                    $this->registrationInfo = [
                        'registration' => $registration,
                        'pilot' => $registration->pilot,
                        'car' => $registration->car,
                        'race' => $registration->race,
                        'status' => $registration->status,
                        'paddock' => $registration->paddock,
                        'passed_checkpoints' => $passedCheckpoints,
                    ];

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
