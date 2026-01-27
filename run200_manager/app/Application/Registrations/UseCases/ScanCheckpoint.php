<?php

namespace App\Application\Registrations\UseCases;

use App\Domain\Registration\Rules\CheckpointTransitions;
use App\Infrastructure\Qr\QrTokenService;
use App\Models\Checkpoint;
use App\Models\CheckpointPassage;
use App\Models\RaceRegistration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ScanCheckpoint
{
    public function __construct(
        private QrTokenService $qrTokenService
    ) {}

    /**
     * Scan a checkpoint for a registration using QR token
     *
     * @param  string  $token  The plain QR token
     * @param  string  $checkpointCode  The checkpoint code to scan
     * @param  User  $scanner  The user performing the scan
     *
     * @throws InvalidArgumentException
     */
    public function execute(string $token, string $checkpointCode, User $scanner): CheckpointPassage
    {
        // Validate token and get registration
        $registration = $this->qrTokenService->validate($token);

        if (! $registration) {
            throw new InvalidArgumentException('Token QR invalide ou expiré');
        }

        return $this->scanWithRegistration($registration, $checkpointCode, $scanner);
    }

    /**
     * Scan a checkpoint directly with a registration (for testing or internal use)
     */
    public function scanWithRegistration(RaceRegistration $registration, string $checkpointCode, User $scanner): CheckpointPassage
    {
        // Find checkpoint
        $checkpoint = Checkpoint::where('code', $checkpointCode)->first();

        if (! $checkpoint) {
            throw new InvalidArgumentException("Checkpoint inconnu: {$checkpointCode}");
        }

        if (! $checkpoint->is_active) {
            throw new InvalidArgumentException('Ce checkpoint est désactivé');
        }

        // Check user permission
        if (! $checkpoint->userCanScan($scanner)) {
            throw new InvalidArgumentException("Vous n'avez pas la permission de scanner ce checkpoint");
        }

        // Get passed checkpoints
        $passedCheckpoints = $registration->passages()
            ->with('checkpoint')
            ->get()
            ->pluck('checkpoint.code')
            ->toArray();

        // Check if transition is allowed
        if (! CheckpointTransitions::canScan($checkpointCode, $registration->status, $passedCheckpoints)) {
            $errorMessage = CheckpointTransitions::getErrorMessage(
                $checkpointCode,
                $registration->status,
                $passedCheckpoints
            );
            throw new InvalidArgumentException($errorMessage);
        }

        return DB::transaction(function () use ($registration, $checkpoint, $scanner, $checkpointCode) {
            // Create passage
            $passage = CheckpointPassage::create([
                'race_registration_id' => $registration->id,
                'checkpoint_id' => $checkpoint->id,
                'scanned_by' => $scanner->id,
                'scanned_at' => now(),
                'meta' => [
                    'user_agent' => request()->userAgent(),
                    'ip' => request()->ip(),
                ],
            ]);

            // Update registration status
            $newStatus = CheckpointTransitions::getStatusAfterScan($checkpointCode);
            if ($newStatus) {
                $registration->update(['status' => $newStatus]);
            }

            // Si c'est un checkpoint ADMIN_CHECK, mettre à jour la fiche d'engagement
            if ($checkpointCode === 'ADMIN_CHECK') {
                $engagementValidation = new UpdateEngagementFormValidation;
                $engagementValidation->recordAdminValidation($registration, $scanner);
            }

            // Log activity
            activity()
                ->performedOn($registration)
                ->causedBy($scanner)
                ->withProperties([
                    'checkpoint_code' => $checkpointCode,
                    'checkpoint_name' => $checkpoint->name,
                    'new_status' => $newStatus,
                ])
                ->log('checkpoint.scanned');

            return $passage->load(['checkpoint', 'scanner', 'registration.pilot', 'registration.car']);
        });
    }

    /**
     * Get registration info from token (for preview before scan)
     */
    public function getRegistrationFromToken(string $token): ?array
    {
        $registration = $this->qrTokenService->validate($token);

        if (! $registration) {
            return null;
        }

        $registration->load(['pilot', 'car.category', 'race', 'passages.checkpoint']);

        $passedCheckpoints = $registration->passages->pluck('checkpoint.code')->toArray();

        return [
            'registration' => $registration,
            'pilot' => $registration->pilot,
            'car' => $registration->car,
            'race' => $registration->race,
            'status' => $registration->status,
            'paddock' => $registration->paddock,
            'passed_checkpoints' => $passedCheckpoints,
        ];
    }
}
