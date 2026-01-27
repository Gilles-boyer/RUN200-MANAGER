<?php

namespace App\Livewire\Staff\Registrations;

use App\Application\Registrations\UseCases\RecordTechInspection;
use App\Models\RaceRegistration;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TechInspectionForm extends Component
{
    public RaceRegistration $registration;

    public string $status = 'OK';

    public string $notes = '';

    public bool $showConfirmation = false;

    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    protected $rules = [
        'status' => 'required|in:OK,FAIL',
        'notes' => 'nullable|string|max:2000',
    ];

    public function mount(RaceRegistration $registration)
    {
        $this->registration = $registration->load([
            'pilot',
            'car.category',
            'race',
            'techInspection.inspector',
        ]);

        // Si déjà inspecté, pré-remplir les valeurs
        if ($this->registration->techInspection) {
            $this->status = $this->registration->techInspection->status;
            $this->notes = $this->registration->techInspection->notes ?? '';
        }
    }

    public function updatedStatus()
    {
        $this->resetErrorBag();
        $this->errorMessage = null;
    }

    public function confirmInspection()
    {
        $this->validate();

        // Validation notes si FAIL
        if ($this->status === 'FAIL' && empty(trim($this->notes))) {
            $this->errorMessage = 'Les notes sont obligatoires pour un contrôle échoué.';

            return;
        }

        $this->showConfirmation = true;
    }

    public function cancelConfirmation()
    {
        $this->showConfirmation = false;
    }

    public function submitInspection()
    {
        $this->showConfirmation = false;
        $this->errorMessage = null;
        $this->successMessage = null;

        try {
            $useCase = new RecordTechInspection;

            $useCase->execute(
                $this->registration,
                $this->status,
                $this->notes ?: null,
                Auth::user()
            );

            $this->successMessage = $this->status === 'OK'
                ? 'Contrôle technique validé avec succès !'
                : 'Contrôle technique échoué enregistré.';

            // Refresh registration data
            $this->registration->refresh();
            $this->registration->load(['techInspection.inspector']);

        } catch (\InvalidArgumentException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function resetInspection()
    {
        $this->errorMessage = null;
        $this->successMessage = null;

        try {
            $useCase = new RecordTechInspection;
            $useCase->reset($this->registration, Auth::user());

            $this->successMessage = 'Contrôle technique réinitialisé. Une nouvelle inspection peut être effectuée.';
            $this->status = 'OK';
            $this->notes = '';

            $this->registration->refresh();
            $this->registration->load(['techInspection']);

        } catch (\InvalidArgumentException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function canInspect(): bool
    {
        return in_array($this->registration->status, ['ACCEPTED', 'ADMIN_CHECKED'])
            && ! $this->registration->techInspection;
    }

    public function canReset(): bool
    {
        return $this->registration->status === 'TECH_CHECKED_FAIL'
            && $this->registration->techInspection;
    }

    public function render()
    {
        return view('livewire.staff.registrations.tech-inspection-form')
            ->layout('layouts.app');
    }
}
