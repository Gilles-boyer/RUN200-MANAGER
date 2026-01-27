<?php

declare(strict_types=1);

namespace App\Livewire\Admin\CarCategories;

use App\Models\CarCategory;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    // Create/Edit modal
    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public bool $isActive = true;

    public int $sortOrder = 0;

    protected $queryString = ['search', 'statusFilter'];

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:100|unique:car_categories,name'.($this->editingId ? ','.$this->editingId : ''),
            'isActive' => 'boolean',
            'sortOrder' => 'integer|min:0',
        ];
    }

    protected array $messages = [
        'name.required' => 'Le nom de la catégorie est obligatoire.',
        'name.unique' => 'Cette catégorie existe déjà.',
        'name.max' => 'Le nom ne peut pas dépasser 100 caractères.',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->reset(['editingId', 'name', 'isActive', 'sortOrder']);
        $this->isActive = true;
        $this->sortOrder = CarCategory::max('sort_order') + 1;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $category = CarCategory::findOrFail($id);
        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->isActive = $category->is_active;
        $this->sortOrder = $category->sort_order;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'is_active' => $this->isActive,
            'sort_order' => $this->sortOrder,
        ];

        if ($this->editingId) {
            $category = CarCategory::findOrFail($this->editingId);
            $category->update($data);
            session()->flash('success', 'Catégorie mise à jour avec succès.');
        } else {
            CarCategory::create($data);
            session()->flash('success', 'Catégorie créée avec succès.');
        }

        $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['editingId', 'name', 'isActive', 'sortOrder']);
        $this->resetValidation();
    }

    public function toggleActive(int $id): void
    {
        $category = CarCategory::findOrFail($id);
        $category->update(['is_active' => ! $category->is_active]);
        session()->flash('success', 'Statut de la catégorie mis à jour.');
    }

    public function delete(int $id): void
    {
        $category = CarCategory::findOrFail($id);

        if ($category->cars()->exists()) {
            session()->flash('error', 'Impossible de supprimer une catégorie avec des voitures.');

            return;
        }

        $category->delete();
        session()->flash('success', 'Catégorie supprimée avec succès.');
    }

    public function moveUp(int $id): void
    {
        $category = CarCategory::findOrFail($id);
        $previous = CarCategory::where('sort_order', '<', $category->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();

        if ($previous) {
            $tempOrder = $category->sort_order;
            $category->update(['sort_order' => $previous->sort_order]);
            $previous->update(['sort_order' => $tempOrder]);
        }
    }

    public function moveDown(int $id): void
    {
        $category = CarCategory::findOrFail($id);
        $next = CarCategory::where('sort_order', '>', $category->sort_order)
            ->orderBy('sort_order', 'asc')
            ->first();

        if ($next) {
            $tempOrder = $category->sort_order;
            $category->update(['sort_order' => $next->sort_order]);
            $next->update(['sort_order' => $tempOrder]);
        }
    }

    public function render()
    {
        $query = CarCategory::withCount('cars');

        if ($this->search) {
            $query->where('name', 'like', '%'.$this->search.'%');
        }

        if ($this->statusFilter !== '') {
            $query->where('is_active', $this->statusFilter === 'active');
        }

        $categories = $query->orderBy('sort_order')->paginate(15);

        return view('livewire.admin.car-categories.index', [
            'categories' => $categories,
        ]);
    }
}
