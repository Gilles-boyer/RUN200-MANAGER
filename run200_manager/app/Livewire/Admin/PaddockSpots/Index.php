<?php

declare(strict_types=1);

namespace App\Livewire\Admin\PaddockSpots;

use App\Models\PaddockSpot;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $zoneFilter = '';

    public string $statusFilter = '';

    // Create/Edit modal
    public bool $showModal = false;

    public ?int $editingId = null;

    public string $spotNumber = '';

    public string $zone = 'A';

    public ?int $positionX = null;

    public ?int $positionY = null;

    public bool $isAvailable = true;

    public string $notes = '';

    // Bulk create modal
    public bool $showBulkModal = false;

    public string $bulkZone = 'A';

    public int $bulkStartNumber = 1;

    public int $bulkEndNumber = 10;

    protected $queryString = ['search', 'zoneFilter', 'statusFilter'];

    protected function rules(): array
    {
        return [
            'spotNumber' => 'required|string|max:20|unique:paddock_spots,spot_number'.($this->editingId ? ','.$this->editingId : ''),
            'zone' => 'required|string|max:10',
            'positionX' => 'nullable|integer|min:0',
            'positionY' => 'nullable|integer|min:0',
            'isAvailable' => 'boolean',
            'notes' => 'nullable|string|max:500',
        ];
    }

    protected array $messages = [
        'spotNumber.required' => 'Le numéro d\'emplacement est obligatoire.',
        'spotNumber.unique' => 'Cet emplacement existe déjà.',
        'zone.required' => 'La zone est obligatoire.',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->reset(['editingId', 'spotNumber', 'zone', 'positionX', 'positionY', 'isAvailable', 'notes']);
        $this->isAvailable = true;
        $this->zone = 'A';
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $spot = PaddockSpot::findOrFail($id);
        $this->editingId = $spot->id;
        $this->spotNumber = $spot->spot_number;
        $this->zone = $spot->zone;
        $this->positionX = $spot->position_x;
        $this->positionY = $spot->position_y;
        $this->isAvailable = $spot->is_available;
        $this->notes = $spot->notes ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'spot_number' => $this->spotNumber,
            'zone' => strtoupper($this->zone),
            'position_x' => $this->positionX,
            'position_y' => $this->positionY,
            'is_available' => $this->isAvailable,
            'notes' => $this->notes ?: null,
        ];

        if ($this->editingId) {
            $spot = PaddockSpot::findOrFail($this->editingId);
            $spot->update($data);
            session()->flash('success', 'Emplacement mis à jour avec succès.');
        } else {
            PaddockSpot::create($data);
            session()->flash('success', 'Emplacement créé avec succès.');
        }

        $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['editingId', 'spotNumber', 'zone', 'positionX', 'positionY', 'isAvailable', 'notes']);
        $this->resetValidation();
    }

    // Bulk creation
    public function openBulkModal(): void
    {
        $this->reset(['bulkZone', 'bulkStartNumber', 'bulkEndNumber']);
        $this->bulkZone = 'A';
        $this->bulkStartNumber = 1;
        $this->bulkEndNumber = 10;
        $this->showBulkModal = true;
    }

    public function closeBulkModal(): void
    {
        $this->showBulkModal = false;
    }

    public function createBulk(): void
    {
        $this->validate([
            'bulkZone' => 'required|string|max:10',
            'bulkStartNumber' => 'required|integer|min:1',
            'bulkEndNumber' => 'required|integer|gte:bulkStartNumber|max:999',
        ], [
            'bulkEndNumber.gte' => 'Le numéro de fin doit être supérieur ou égal au numéro de début.',
        ]);

        $created = 0;
        $skipped = 0;
        $zone = strtoupper($this->bulkZone);

        for ($i = $this->bulkStartNumber; $i <= $this->bulkEndNumber; $i++) {
            $spotNumber = $zone.str_pad((string) $i, 3, '0', STR_PAD_LEFT);

            if (PaddockSpot::where('spot_number', $spotNumber)->exists()) {
                $skipped++;

                continue;
            }

            PaddockSpot::create([
                'spot_number' => $spotNumber,
                'zone' => $zone,
                'is_available' => true,
            ]);
            $created++;
        }

        if ($created > 0) {
            $message = "{$created} emplacement(s) créé(s) avec succès.";
            if ($skipped > 0) {
                $message .= " {$skipped} emplacement(s) ignoré(s) (déjà existants).";
            }
            session()->flash('success', $message);
        } else {
            session()->flash('warning', 'Aucun emplacement créé. Tous existaient déjà.');
        }

        $this->closeBulkModal();
    }

    public function toggleAvailable(int $id): void
    {
        $spot = PaddockSpot::findOrFail($id);
        $spot->update(['is_available' => ! $spot->is_available]);
        $status = $spot->is_available ? 'en service' : 'hors service';
        session()->flash('success', "Emplacement marqué comme {$status}.");
    }

    public function delete(int $id): void
    {
        $spot = PaddockSpot::findOrFail($id);

        if ($spot->registrations()->exists()) {
            session()->flash('error', 'Impossible de supprimer un emplacement avec des inscriptions (historique).');

            return;
        }

        $spot->delete();
        session()->flash('success', 'Emplacement supprimé avec succès.');
    }

    public function render()
    {
        $query = PaddockSpot::withCount('registrations');

        if ($this->search) {
            $query->where('spot_number', 'like', '%'.$this->search.'%');
        }

        if ($this->zoneFilter) {
            $query->where('zone', $this->zoneFilter);
        }

        if ($this->statusFilter !== '') {
            $query->where('is_available', $this->statusFilter === 'in_service');
        }

        $spots = $query->orderBy('zone')->orderBy('spot_number')->paginate(20);

        // Get unique zones for filter
        $zones = PaddockSpot::distinct()->pluck('zone')->sort()->mapWithKeys(fn ($z) => [$z => "Zone $z"])->prepend('Toutes', '')->toArray();

        // Statistics globales
        $stats = PaddockSpot::getGlobalStatistics();

        return view('livewire.admin.paddock-spots.index', [
            'spots' => $spots,
            'zones' => $zones,
            'stats' => $stats,
        ]);
    }
}
