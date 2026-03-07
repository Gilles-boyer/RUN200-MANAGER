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

    protected $messages = [
        'status.required' => 'Le statut du contrôle technique est obligatoire.',
        'status.in' => 'Le statut doit être "OK" ou "FAIL".',
        'notes.max' => 'Les notes ne peuvent pas dépasser 2000 caractères.',
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

    public function updateNotes()
    {
        $this->errorMessage = null;
        $this->successMessage = null;

        if (! $this->registration->techInspection) {
            $this->errorMessage = 'Aucun contrôle technique à modifier.';
            return;
        }

        $notesValue = trim($this->notes) !== '' ? trim($this->notes) : null;

        // Mettre à jour TechInspection
        $this->registration->techInspection->update([
            'notes' => $notesValue,
        ]);

        // Synchroniser avec CarTechInspectionHistory
        \App\Models\CarTechInspectionHistory::where('tech_inspection_id', $this->registration->techInspection->id)
            ->update(['notes' => $notesValue]);

        // Synchroniser avec EngagementForm
        if ($this->registration->engagementForm) {
            $this->registration->engagementForm->update([
                'tech_notes' => $notesValue,
            ]);
        }

        // Synchroniser avec le passage TECH_CHECK si existant
        $techPassage = $this->registration->passages()
            ->whereHas('checkpoint', fn ($q) => $q->where('code', 'TECH_CHECK'))
            ->first();

        if ($techPassage) {
            $meta = $techPassage->meta ?? [];
            if ($notesValue) {
                $meta['staff_note'] = $notesValue;
            } else {
                unset($meta['staff_note']);
            }
            $techPassage->update(['meta' => $meta]);
        }

        activity()
            ->performedOn($this->registration)
            ->causedBy(Auth::user())
            ->withProperties([
                'notes' => $notesValue,
                'action' => 'tech_notes_updated',
            ])
            ->log('tech.notes_updated');

        $this->successMessage = 'Notes mises à jour avec succès.';

        $this->registration->refresh();
        $this->registration->load(['techInspection.inspector']);
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
