<?php

namespace App\Livewire\Staff\Registrations;

use App\Application\Registrations\UseCases\ScanCheckpoint;
use App\Application\Registrations\UseCases\UpdateEngagementFormValidation;
use App\Domain\Registration\Enums\RegistrationStatus;
use App\Infrastructure\Pdf\EngagementFormPdfService;
use App\Models\Checkpoint;
use App\Models\CheckpointPassage;
use App\Models\EngagementForm;
use App\Models\Pilot;
use App\Models\Race;
use App\Models\RaceRegistration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
class EngagementSign extends Component
{
    #[Url]
    public ?int $raceId = null;

    public ?RaceRegistration $registration = null;

    // Signature data (base64)
    public string $signatureData = '';

    public string $guardianSignatureData = '';

    // Additional pilot details (from official form)
    public ?string $pilotPermitNumber = null;

    public ?string $pilotPermitDate = null;

    public ?string $pilotEmail = null;

    // UI State
    public bool $showSignatureModal = false;

    public bool $isGuardianSignature = false;

    public string $searchQuery = '';

    /**
     * Mount the component
     */
    public function mount(?RaceRegistration $registration = null): void
    {
        if ($registration && $registration->exists) {
            $this->registration = $registration;
            $this->raceId = $registration->race_id;
        } elseif (! $this->raceId) {
            // Default to active race (today or next)
            $race = Race::where('status', 'OPEN')
                ->orWhere('status', 'RUNNING')
                ->orderBy('race_date')
                ->first();

            if ($race) {
                $this->raceId = $race->id;
            }
        }
    }

    /**
     * Get the current race
     */
    #[Computed]
    public function race(): ?Race
    {
        return $this->raceId ? Race::find($this->raceId) : null;
    }

    /**
     * Get available races for selection
     */
    #[Computed]
    public function availableRaces()
    {
        return Race::whereIn('status', ['OPEN', 'RUNNING', 'CLOSED'])
            ->orderBy('race_date', 'desc')
            ->take(10)
            ->get();
    }

    /**
     * Get accepted registrations without engagement for current race
     */
    #[Computed]
    public function pendingRegistrations()
    {
        if (! $this->raceId) {
            return collect();
        }

        $query = RaceRegistration::where('race_id', $this->raceId)
            ->whereIn('status', RaceRegistration::engagedStatuses())
            ->whereDoesntHave('engagementForm')
            ->with(['pilot', 'car.category']);

        if ($this->searchQuery) {
            $search = $this->searchQuery;
            $query->where(function ($q) use ($search) {
                $q->whereHas('pilot', function ($pq) use ($search) {
                    $pq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('license_number', 'like', "%{$search}%");
                })
                    ->orWhereHas('car', function ($cq) use ($search) {
                        $cq->where('race_number', 'like', "%{$search}%");
                    });
            });
        }

        return $query->orderBy('created_at')->get();
    }

    /**
     * Get signed engagements for current race
     */
    #[Computed]
    public function signedEngagements()
    {
        if (! $this->raceId) {
            return collect();
        }

        return EngagementForm::forRace($this->raceId)
            ->with(['registration.pilot', 'registration.car', 'registration.techInspection.inspector', 'witness', 'adminValidator'])
            ->orderBy('signed_at', 'desc')
            ->take(20)
            ->get();
    }

    /**
     * Get statistics for current race
     */
    #[Computed]
    public function stats(): array
    {
        if (! $this->raceId) {
            return [
                'total' => 0,
                'signed' => 0,
                'pending' => 0,
            ];
        }

        $total = RaceRegistration::where('race_id', $this->raceId)
            ->whereIn('status', RaceRegistration::engagedStatuses())
            ->count();

        $signed = EngagementForm::forRace($this->raceId)->count();

        return [
            'total' => $total,
            'signed' => $signed,
            'pending' => $total - $signed,
        ];
    }

    /**
     * Select a registration for signing
     */
    public function selectRegistration(int $registrationId): void
    {
        $this->registration = RaceRegistration::with(['pilot', 'car.category', 'race'])
            ->findOrFail($registrationId);

        // Reset all form data
        $this->signatureData = '';
        $this->guardianSignatureData = '';

        // Pre-fill pilot details
        /** @var Pilot $pilot */
        $pilot = $this->registration->pilot;
        $this->pilotPermitNumber = $pilot->permit_number ?? null;
        $this->pilotPermitDate = $pilot->permit_date?->format('Y-m-d');
        /** @var User|null $user */
        $user = $pilot->user;
        $this->pilotEmail = $user->email ?? null;

        $this->showSignatureModal = true;
        $this->isGuardianSignature = false;
    }

    /**
     * Open signature pad for pilot
     */
    public function openPilotSignature(): void
    {
        $this->isGuardianSignature = false;
        $this->dispatch('open-signature-pad');
    }

    /**
     * Open signature pad for guardian
     */
    public function openGuardianSignature(): void
    {
        $this->isGuardianSignature = true;
        $this->dispatch('open-signature-pad');
    }

    /**
     * Save signature from JavaScript
     */
    public function saveSignature(string $data): void
    {
        if ($this->isGuardianSignature) {
            $this->guardianSignatureData = $data;
        } else {
            $this->signatureData = $data;
        }
    }

    /**
     * Clear a signature
     */
    public function clearSignature(bool $isGuardian = false): void
    {
        if ($isGuardian) {
            $this->guardianSignatureData = '';
        } else {
            $this->signatureData = '';
        }
    }

    /**
     * Submit the engagement form
     */
    public function submitEngagement(ScanCheckpoint $scanCheckpoint): void
    {
        if (! $this->registration) {
            $this->addError('registration', 'Aucune inscription sélectionnée.');

            return;
        }

        if (empty($this->signatureData)) {
            $this->addError('signatureData', 'La signature du pilote est requise.');

            return;
        }

        // Check for minor requiring guardian signature
        if ($this->registration->pilot->is_minor && empty($this->guardianSignatureData)) {
            $this->addError('guardianSignatureData', 'La signature du tuteur légal est requise pour un pilote mineur.');

            return;
        }

        // Check if engagement already exists
        if ($this->registration->engagementForm) {
            $this->addError('registration', 'Une feuille d\'engagement existe déjà pour cette inscription.');

            return;
        }

        try {
            // Prepare pilot details from form
            $vehicleDetails = [
                'pilot_email' => $this->pilotEmail ?: null,
                'pilot_permit_number' => $this->pilotPermitNumber ?: null,
                'pilot_permit_date' => $this->pilotPermitDate ? Carbon::parse($this->pilotPermitDate) : null,
            ];

            $engagement = DB::transaction(function () use ($scanCheckpoint, $vehicleDetails) {
                $registration = RaceRegistration::whereKey($this->registration->id)->lockForUpdate()->firstOrFail();
                if (! in_array($registration->status, RaceRegistration::engagedStatuses(), true)) {
                    throw new InvalidArgumentException('Cette inscription doit être acceptée avant la signature.');
                }
                if ($registration->engagementForm()->exists()) {
                    throw new InvalidArgumentException('Une feuille d’engagement existe déjà pour cette inscription.');
                }

                $form = EngagementForm::createFromRegistration(
                    $registration,
                    $this->signatureData,
                    auth()->id(),
                    $registration->pilot->is_minor ? $this->guardianSignatureData : null,
                    request()->ip(),
                    request()->userAgent(),
                    $vehicleDetails
                );

                if ($registration->status === RegistrationStatus::ACCEPTED->value) {
                    $scanCheckpoint->scanWithRegistration($registration, 'ADMIN_CHECK', auth()->user(), 'engagement_signature');
                } elseif ($registration->hasPassedCheckpoint('ADMIN_CHECK')) {
                    (new UpdateEngagementFormValidation)->recordAdminValidation($registration, auth()->user());
                } else {
                    // An older registration may have advanced without a recorded admin passage.
                    $checkpoint = Checkpoint::where('code', 'ADMIN_CHECK')->firstOrFail();
                    if (! $checkpoint->is_active || ! $checkpoint->userCanScan(auth()->user())) {
                        throw new InvalidArgumentException('La vérification administrative est indisponible pour ce compte.');
                    }
                    CheckpointPassage::create([
                        'race_registration_id' => $registration->id,
                        'checkpoint_id' => $checkpoint->id,
                        'scanned_by' => auth()->id(),
                        'scanned_at' => now(),
                        'meta' => ['source' => 'engagement_signature', 'reason' => 'Synchronisation d’une étape déjà franchie'],
                    ]);
                    (new UpdateEngagementFormValidation)->recordAdminValidation($registration, auth()->user());
                }

                return $form;
            });

            Log::info('Engagement form signed', [
                'engagement_id' => $engagement->id,
                'registration_id' => $this->registration->id,
                'pilot' => $this->registration->pilot->full_name,
                'witnessed_by' => auth()->id(),
            ]);

            $this->showSignatureModal = false;
            $this->registration = null;
            $this->signatureData = '';
            $this->guardianSignatureData = '';
            $this->resetPilotFields();
            unset($this->pendingRegistrations, $this->signedEngagements, $this->stats);

            session()->flash('success', 'Feuille d’engagement signée et vérification administrative enregistrée.');
        } catch (InvalidArgumentException $e) {
            $this->addError('registration', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Error creating engagement form', [
                'error' => $e->getMessage(),
                'registration_id' => $this->registration->id,
            ]);

            $this->addError('registration', 'Erreur lors de la création de la feuille d\'engagement.');
        }
    }

    /**
     * Close the signature modal
     */
    public function closeModal(): void
    {
        $this->showSignatureModal = false;
        $this->registration = null;
        $this->signatureData = '';
        $this->guardianSignatureData = '';
        $this->resetPilotFields();
    }

    /**
     * Reset pilot detail fields
     */
    private function resetPilotFields(): void
    {
        $this->pilotPermitNumber = null;
        $this->pilotPermitDate = null;
        $this->pilotEmail = null;
    }

    /**
     * Download PDF for an engagement
     */
    public function downloadPdf(int $engagementId)
    {
        $engagement = EngagementForm::findOrFail($engagementId);

        $pdfService = new EngagementFormPdfService;

        return $pdfService->download($engagement);
    }

    /**
     * Stream PDF for an engagement (view in browser)
     */
    public function viewPdf(int $engagementId)
    {
        $engagement = EngagementForm::findOrFail($engagementId);

        $pdfService = new EngagementFormPdfService;

        return $pdfService->stream($engagement);
    }

    public function render()
    {
        return view('livewire.staff.registrations.engagement-sign');
    }
}
