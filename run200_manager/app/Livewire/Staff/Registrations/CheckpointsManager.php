<?php

declare(strict_types=1);

namespace App\Livewire\Staff\Registrations;

use App\Models\Checkpoint;
use App\Models\CheckpointPassage;
use App\Models\RaceRegistration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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
        return $this->registration->passages
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
        $this->selectedCheckpointId = $checkpointId;
        $this->editingPassageId = null;
        $this->passageDate = now()->format('Y-m-d');
        $this->passageTime = now()->format('H:i');
        $this->staffNote = '';
        $this->showPassageModal = true;
    }

    public function openEditPassageModal(int $passageId): void
    {
        $passage = CheckpointPassage::find($passageId);
        if (! $passage) {
            return;
        }

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
        $this->validate([
            'selectedCheckpointId' => 'required|exists:checkpoints,id',
            'passageDate' => 'required|date',
            'passageTime' => 'required|date_format:H:i',
        ]);

        $scannedAt = \Carbon\Carbon::parse($this->passageDate.' '.$this->passageTime);

        try {
            DB::beginTransaction();

            if ($this->editingPassageId) {
                // Update existing passage
                $passage = CheckpointPassage::find($this->editingPassageId);

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
            session()->flash('error', 'Erreur: '.$e->getMessage());
        }
    }

    public function confirmDeletePassage(int $passageId): void
    {
        $this->passageToDelete = CheckpointPassage::with('checkpoint')->find($passageId);
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->passageToDelete = null;
    }

    public function deletePassage(): void
    {
        if (! $this->passageToDelete) {
            return;
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
            session()->flash('error', 'Erreur: '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.staff.registrations.checkpoints-manager')
            ->layout('layouts.app');
    }
}
