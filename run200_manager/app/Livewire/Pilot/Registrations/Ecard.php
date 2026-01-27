<?php

namespace App\Livewire\Pilot\Registrations;

use App\Infrastructure\Qr\QrTokenService;
use App\Models\RaceRegistration;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Ecard extends Component
{
    public RaceRegistration $registration;

    public string $qrCodeDataUri = '';

    public string $plainToken = '';

    public function mount(RaceRegistration $registration)
    {
        $this->registration = $registration->load(['pilot', 'car.category', 'race', 'passages.checkpoint']);

        // Check ownership
        if ($this->registration->pilot->user_id !== Auth::id()) {
            abort(403, 'Vous ne pouvez pas accéder à cette e-carte');
        }

        // Only accepted registrations can have an e-card
        if (! $this->registration->isAccepted() && ! in_array($this->registration->status, [
            'ADMIN_CHECKED',
            'TECH_CHECKED_OK',
            'ENTRY_SCANNED',
            'BRACELET_GIVEN',
        ])) {
            session()->flash('error', 'Votre inscription doit être acceptée pour obtenir une e-carte');

            return $this->redirect(route('pilot.dashboard'));
        }

        $this->generateQrCode();
    }

    public function generateQrCode(): void
    {
        $qrService = new QrTokenService;
        $this->plainToken = $qrService->getOrGenerateToken($this->registration);
        $this->qrCodeDataUri = $qrService->generateQrCodeDataUri($this->plainToken, 250);
    }

    public function regenerateQrCode(): void
    {
        $qrService = new QrTokenService;
        $qrService->revoke($this->registration);
        $this->generateQrCode();

        session()->flash('success', 'QR code régénéré avec succès');
    }

    public function render()
    {
        $passedCheckpoints = $this->registration->passages
            ->pluck('checkpoint.code')
            ->toArray();

        return view('livewire.pilot.registrations.ecard', [
            'passedCheckpoints' => $passedCheckpoints,
        ])->layout('layouts.app');
    }
}
