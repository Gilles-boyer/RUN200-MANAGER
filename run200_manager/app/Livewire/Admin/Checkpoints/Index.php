<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Checkpoints;

use App\Models\Checkpoint;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    // Create/Edit modal
    public bool $showModal = false;

    public ?int $editingId = null;

    public string $code = '';

    public string $name = '';

    public string $requiredPermission = '';

    public bool $isActive = true;

    public int $sortOrder = 0;

    protected $queryString = ['search', 'statusFilter'];

    protected function rules(): array
    {
        return [
            'code' => 'required|string|max:20|unique:checkpoints,code'.($this->editingId ? ','.$this->editingId : ''),
            'name' => 'required|string|max:100',
            'requiredPermission' => 'nullable|string|max:100',
            'isActive' => 'boolean',
            'sortOrder' => 'integer|min:0',
        ];
    }

    protected array $messages = [
        'code.required' => 'Le code du checkpoint est obligatoire.',
        'code.unique' => 'Ce code existe déjà.',
        'name.required' => 'Le nom du checkpoint est obligatoire.',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->reset(['editingId', 'code', 'name', 'requiredPermission', 'isActive', 'sortOrder']);
        $this->isActive = true;
        $this->sortOrder = Checkpoint::max('sort_order') + 1;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $checkpoint = Checkpoint::findOrFail($id);
        $this->editingId = $checkpoint->id;
        $this->code = $checkpoint->code;
        $this->name = $checkpoint->name;
        $this->requiredPermission = $checkpoint->required_permission ?? '';
        $this->isActive = $checkpoint->is_active;
        $this->sortOrder = $checkpoint->sort_order;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'code' => strtoupper($this->code),
            'name' => $this->name,
            'required_permission' => $this->requiredPermission ?: null,
            'is_active' => $this->isActive,
            'sort_order' => $this->sortOrder,
        ];

        if ($this->editingId) {
            $checkpoint = Checkpoint::findOrFail($this->editingId);
            $checkpoint->update($data);
            session()->flash('success', 'Checkpoint mis à jour avec succès.');
        } else {
            Checkpoint::create($data);
            session()->flash('success', 'Checkpoint créé avec succès.');
        }

        $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['editingId', 'code', 'name', 'requiredPermission', 'isActive', 'sortOrder']);
        $this->resetValidation();
    }

    public function toggleActive(int $id): void
    {
        $checkpoint = Checkpoint::findOrFail($id);
        $checkpoint->update(['is_active' => ! $checkpoint->is_active]);
        session()->flash('success', 'Statut du checkpoint mis à jour.');
    }

    public function delete(int $id): void
    {
        $checkpoint = Checkpoint::findOrFail($id);

        if ($checkpoint->passages()->exists()) {
            session()->flash('error', 'Impossible de supprimer un checkpoint avec des passages enregistrés.');

            return;
        }

        $checkpoint->delete();
        session()->flash('success', 'Checkpoint supprimé avec succès.');
    }

    public function moveUp(int $id): void
    {
        $checkpoint = Checkpoint::findOrFail($id);
        $previous = Checkpoint::where('sort_order', '<', $checkpoint->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();

        if ($previous) {
            $tempOrder = $checkpoint->sort_order;
            $checkpoint->update(['sort_order' => $previous->sort_order]);
            $previous->update(['sort_order' => $tempOrder]);
        }
    }

    public function moveDown(int $id): void
    {
        $checkpoint = Checkpoint::findOrFail($id);
        $next = Checkpoint::where('sort_order', '>', $checkpoint->sort_order)
            ->orderBy('sort_order', 'asc')
            ->first();

        if ($next) {
            $tempOrder = $checkpoint->sort_order;
            $checkpoint->update(['sort_order' => $next->sort_order]);
            $next->update(['sort_order' => $tempOrder]);
        }
    }

    public function render()
    {
        $query = Checkpoint::withCount('passages');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('code', 'like', '%'.$this->search.'%')
                    ->orWhere('name', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->statusFilter !== '') {
            $query->where('is_active', $this->statusFilter === 'active');
        }

        $checkpoints = $query->orderBy('sort_order')->paginate(15);

        // Get available permissions for dropdown
        $permissions = Permission::where('name', 'like', 'checkpoint.%')
            ->orWhere('name', 'like', 'scan.%')
            ->pluck('name', 'name')
            ->prepend('Aucune (tous les staff)', '')
            ->toArray();

        return view('livewire.admin.checkpoints.index', [
            'checkpoints' => $checkpoints,
            'permissions' => $permissions,
        ]);
    }
}
