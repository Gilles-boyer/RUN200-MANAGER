<?php

declare(strict_types=1);

namespace App\Livewire\Staff\Registrations;

use App\Application\Registrations\UseCases\UpdateEngagementFormValidation;
use App\Domain\Registration\Rules\CheckpointTransitions;
use App\Events\TechInspectionCompleted;
use App\Models\CarTechInspectionHistory;
use App\Models\Checkpoint;
use App\Models\CheckpointPassage;
use App\Models\RaceRegistration;
use App\Models\TechInspection;
use App\Models\User;
use App\Support\UserFacingError;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CheckpointsManager extends Component
{
    public RaceRegistration $registration;

    // Modal for adding/editing passage
    public bool $showPassageModal = false;

    public ?int $selectedCheckpointId = null;

    public ?int $editingPassageId = null;

    public string $passageDate = '';

    public string $passageTime = '';

    public string $staffNote = '';

    // Modal for deleting passage
    public bool $showDeleteModal = false;

    public ?CheckpointPassage $passageToDelete = null;

    public function mount(RaceRegistration $registration): void
    {
        $this->registration = $registration->load([
            'pilot',
            'car.category',
            'race',
            'passages.checkpoint',
            'passages.scanner',
            'techInspection',
            'payments',
            'activities.causer', // Load activity log with causer relation
        ]);
    }

    #[Computed]
    public function checkpoints()
    {
        return Checkpoint::active()->ordered()->get();
    }

    #[Computed]
    public function passagesByCheckpoint()
    {
        // Force load scanner relation to avoid lazy loading
        return $this->registration->passages->load('scanner', 'checkpoint')
            ->keyBy('checkpoint_id');
    }

    #[Computed]
    public function availableStaff()
    {
        return User::role(['ADMIN', 'STAFF_ADMINISTRATIF', 'CONTROLEUR_TECHNIQUE', 'STAFF_ENTREE', 'STAFF_SONO'])
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function registrationActivities()
    {
        return $this->registration->activities()->with('causer')->latest()->get();
    }

    public function getCheckpointStatus(Checkpoint $checkpoint): string
    {
        $passage = $this->passagesByCheckpoint->get($checkpoint->id);

        if ($passage) {
            return 'completed';
        }

        // Check if previous checkpoints are done
        $previousCheckpoint = $checkpoint->previousCheckpoint();
        if ($previousCheckpoint && ! $this->passagesByCheckpoint->has($previousCheckpoint->id)) {
            return 'locked';
        }

        return 'pending';
    }

    public function openAddPassageModal(int $checkpointId): void
    {
        Gate::authorize('registration.manage');

        $this->selectedCheckpointId = $checkpointId;
        $this->editingPassageId = null;
        $this->passageDate = now()->format('Y-m-d');
        $this->passageTime = now()->format('H:i');
        $this->staffNote = '';
        $this->showPassageModal = true;
    }

    public function openEditPassageModal(int $passageId): void
    {
        Gate::authorize('registration.manage');

        $passage = $this->registration->passages()->with('checkpoint')->findOrFail($passageId);

        $this->editingPassageId = $passageId;
        $this->selectedCheckpointId = $passage->checkpoint_id;
        $this->passageDate = $passage->scanned_at->format('Y-m-d');
        $this->passageTime = $passage->scanned_at->format('H:i');
        $this->staffNote = $passage->meta['staff_note'] ?? '';
        $this->showPassageModal = true;
    }

    public function closePassageModal(): void
    {
        $this->showPassageModal = false;
        $this->selectedCheckpointId = null;
        $this->editingPassageId = null;
        $this->passageDate = '';
        $this->passageTime = '';
        $this->staffNote = '';
    }

    public function savePassage(): void
    {
        Gate::authorize('registration.manage');

        $this->validate([
            'selectedCheckpointId' => 'required|exists:checkpoints,id',
            'passageDate' => 'required|date',
            'passageTime' => 'required|date_format:H:i',
        ]);

        $checkpoint = Checkpoint::findOrFail($this->selectedCheckpointId);
        $this->registration->refresh();

        if ($this->editingPassageId) {
            $passageToEdit = $this->registration->passages()->with('checkpoint')->findOrFail($this->editingPassageId);
            if ($passageToEdit->checkpoint_id !== $checkpoint->id) {
                throw ValidationException::withMessages([
                    'selectedCheckpointId' => 'Le checkpoint de ce passage ne peut pas être remplacé.',
                ]);
            }
        } else {
            $passedCodes = CheckpointPassage::query()
                ->where('race_registration_id', $this->registration->id)
                ->with('checkpoint')->get()
                ->map(fn (CheckpointPassage $passage) => $passage->checkpoint->code)
                ->all();
            if (! $checkpoint->is_active || ! CheckpointTransitions::canScan($checkpoint->code, $this->registration->status, $passedCodes)) {
                throw ValidationException::withMessages([
                    'selectedCheckpointId' => 'Cette étape ne peut pas être validée dans l’état actuel de l’inscription.',
                ]);
            }
        }

        $scannedAt = Carbon::parse($this->passageDate.' '.$this->passageTime);

        try {
            DB::beginTransaction();

            if ($this->editingPassageId) {
                // Update existing passage
                $passage = $passageToEdit;

                // Merge existing meta with updated staff_note
                $meta = $passage->meta ?? [];
                if (trim($this->staffNote) !== '') {
                    $meta['staff_note'] = trim($this->staffNote);
                } else {
                    unset($meta['staff_note']);
                }

                $passage->update([
                    'scanned_at' => $scannedAt,
                    'meta' => $meta,
                ]);

                // Si c'est un passage TECH_CHECK, synchroniser les notes avec TechInspection et CarTechInspectionHistory
                $passage->load('checkpoint');
                if ($passage->checkpoint && $passage->checkpoint->code === 'TECH_CHECK' && $this->registration->techInspection) {
                    $notesValue = trim($this->staffNote) !== '' ? trim($this->staffNote) : null;

                    // Mettre à jour TechInspection
                    $this->registration->techInspection->update([
                        'notes' => $notesValue,
                    ]);

                    // Mettre à jour CarTechInspectionHistory
                    CarTechInspectionHistory::where('tech_inspection_id', $this->registration->techInspection->id)
                        ->update(['notes' => $notesValue]);

                    // Mettre à jour EngagementForm
                    if ($this->registration->engagementForm) {
                        $this->registration->engagementForm->update([
                            'tech_notes' => $notesValue,
                        ]);
                    }
                }

                activity()
                    ->performedOn($this->registration)
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'passage_id' => $passage->id,
                        'checkpoint' => $passage->checkpoint->name,
                        'new_time' => $scannedAt->toIso8601String(),
                        'action' => 'manual_edit',
                    ])
                    ->log('checkpoint.passage_edited');

                session()->flash('success', 'Passage modifié avec succès.');
            } else {
                // Check if passage already exists
                $existingPassage = CheckpointPassage::where('race_registration_id', $this->registration->id)
                    ->where('checkpoint_id', $this->selectedCheckpointId)
                    ->first();

                if ($existingPassage) {
                    session()->flash('error', 'Ce checkpoint a déjà été scanné.');
                    DB::rollBack();

                    return;
                }

                // Create new passage
                $meta = [
                    'manual_entry' => true,
                    'entered_by' => auth()->user()->name,
                    'reason' => 'Correction manuelle',
                ];

                if (trim($this->staffNote) !== '') {
                    $meta['staff_note'] = trim($this->staffNote);
                }

                $passage = CheckpointPassage::create([
                    'race_registration_id' => $this->registration->id,
                    'checkpoint_id' => $this->selectedCheckpointId,
                    'scanned_by' => auth()->id(),
                    'scanned_at' => $scannedAt,
                    'meta' => $meta,
                ]);

                activity()
                    ->performedOn($this->registration)
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'passage_id' => $passage->id,
                        'checkpoint' => $passage->checkpoint->name,
                        'scanned_at' => $scannedAt->toIso8601String(),
                        'action' => 'manual_add',
                    ])
                    ->log('checkpoint.passage_added_manually');

                // Si c'est un passage TECH_CHECK, créer l'inspection technique et l'historique
                if ($checkpoint->code === 'TECH_CHECK') {
                    // Créer l'entrée TechInspection si elle n'existe pas
                    if (! $this->registration->techInspection) {
                        $techInspection = TechInspection::create([
                            'race_registration_id' => $this->registration->id,
                            'status' => 'OK',
                            'notes' => trim($this->staffNote) !== '' ? trim($this->staffNote) : null,
                            'inspected_by' => auth()->id(),
                            'inspected_at' => $scannedAt,
                        ]);

                        // Créer l'entrée dans l'historique pour la voiture
                        CarTechInspectionHistory::create([
                            'car_id' => $this->registration->car_id,
                            'race_registration_id' => $this->registration->id,
                            'tech_inspection_id' => $techInspection->id,
                            'status' => 'OK',
                            'notes' => trim($this->staffNote) !== '' ? trim($this->staffNote) : null,
                            'inspected_by' => auth()->id(),
                            'inspected_at' => $scannedAt,
                        ]);

                        // Mettre à jour la fiche d'engagement
                        $engagementValidation = new UpdateEngagementFormValidation;
                        $engagementValidation->recordTechValidation($this->registration, auth()->user(), 'OK', trim($this->staffNote) !== '' ? trim($this->staffNote) : null);

                        // Dispatch event for email notification
                        TechInspectionCompleted::dispatch($techInspection);
                    }
                }

                $newStatus = CheckpointTransitions::getStatusAfterScan($checkpoint->code);
                if ($newStatus !== null) {
                    $this->registration->update(['status' => $newStatus]);
                }

                session()->flash('success', 'Passage ajouté avec succès.');
            }

            DB::commit();

            // Refresh registration data
            $this->registration = $this->registration->fresh([
                'pilot',
                'car.category',
                'race',
                'passages.checkpoint',
                'passages.scanner',
                'techInspection',
                'payments',
                'activities.causer',
            ]);

            $this->closePassageModal();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', UserFacingError::message($e, 'Impossible d’enregistrer ce passage.'));
        }
    }

    public function confirmDeletePassage(int $passageId): void
    {
        Gate::authorize('registration.manage');

        $this->passageToDelete = CheckpointPassage::query()
            ->where('race_registration_id', $this->registration->id)
            ->with('checkpoint')->findOrFail($passageId);
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->passageToDelete = null;
    }

    public function deletePassage(): void
    {
        Gate::authorize('registration.manage');

        if (! $this->passageToDelete) {
            return;
        }

        $this->passageToDelete = CheckpointPassage::query()
            ->where('race_registration_id', $this->registration->id)
            ->with('checkpoint')->findOrFail($this->passageToDelete->id);

        $dependentCodes = match ($this->passageToDelete->checkpoint->code) {
            'ADMIN_CHECK' => ['TECH_CHECK', 'ENTRY', 'BRACELET'],
            'TECH_CHECK' => ['ENTRY', 'BRACELET'],
            'ENTRY' => ['BRACELET'],
            default => [],
        };
        if (in_array($this->registration->fresh()->status, ['RESULTS_IMPORTED', 'PUBLISHED'], true)
            || ($dependentCodes !== [] && $this->registration->passages()
                ->whereHas('checkpoint', fn ($query) => $query->whereIn('code', $dependentCodes))->exists())) {
            throw ValidationException::withMessages([
                'passageToDelete' => 'Supprimez d’abord les étapes suivantes ou corrigez les résultats publiés.',
            ]);
        }

        try {
            DB::beginTransaction();

            $checkpointName = $this->passageToDelete->checkpoint->name;

            activity()
                ->performedOn($this->registration)
                ->causedBy(auth()->user())
                ->withProperties([
                    'passage_id' => $this->passageToDelete->id,
                    'checkpoint' => $checkpointName,
                    'original_scanned_at' => $this->passageToDelete->scanned_at->toIso8601String(),
                    'action' => 'manual_delete',
                ])
                ->log('checkpoint.passage_deleted');

            // Si c'est un passage TECH_CHECK, supprimer aussi TechInspection et CarTechInspectionHistory
            $checkpointCode = $this->passageToDelete->checkpoint->code;
            if ($checkpointCode === 'TECH_CHECK' && $this->registration->techInspection) {
                $techInspectionId = $this->registration->techInspection->id;

                // Supprimer l'historique correspondant
                CarTechInspectionHistory::where('tech_inspection_id', $techInspectionId)->delete();

                // Supprimer le TechInspection
                $this->registration->techInspection->delete();
            }

            $previousStatus = match ($checkpointCode) {
                'ADMIN_CHECK' => 'ACCEPTED',
                'TECH_CHECK' => $this->registration->hasPassedCheckpoint('ADMIN_CHECK') ? 'ADMIN_CHECKED' : 'ACCEPTED',
                'ENTRY' => 'TECH_CHECKED_OK',
                'BRACELET' => 'ENTRY_SCANNED',
                default => null,
            };
            if ($previousStatus !== null) {
                $this->registration->update(['status' => $previousStatus]);
            }

            $this->passageToDelete->delete();

            DB::commit();

            // Refresh registration data
            $this->registration = $this->registration->fresh([
                'pilot',
                'car.category',
                'race',
                'passages.checkpoint',
                'passages.scanner',
                'techInspection',
                'payments',
                'activities.causer',
            ]);

            session()->flash('success', "Passage au checkpoint \"{$checkpointName}\" supprimé.");
            $this->closeDeleteModal();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', UserFacingError::message($e, 'Impossible de supprimer ce passage.'));
        }
    }

    public function render()
    {
        return view('livewire.staff.registrations.checkpoints-manager')
            ->layout('layouts.app');
    }
}
