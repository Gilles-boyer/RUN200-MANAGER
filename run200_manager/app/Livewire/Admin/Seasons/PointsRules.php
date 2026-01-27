<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Seasons;

use App\Models\Season;
use App\Models\SeasonPointsRule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class PointsRules extends Component
{
    public Season $season;

    // Rule form
    public bool $showModal = false;

    public ?int $editingId = null;

    public int $positionFrom = 1;

    public int $positionTo = 1;

    public int $points = 0;

    protected function rules(): array
    {
        return [
            'positionFrom' => 'required|integer|min:1|max:999',
            'positionTo' => 'required|integer|min:1|max:999|gte:positionFrom',
            'points' => 'required|integer|min:0|max:1000',
        ];
    }

    protected array $messages = [
        'positionFrom.required' => 'La position de début est obligatoire.',
        'positionTo.required' => 'La position de fin est obligatoire.',
        'positionTo.gte' => 'La position de fin doit être supérieure ou égale à la position de début.',
        'points.required' => 'Le nombre de points est obligatoire.',
    ];

    public function mount(Season $season): void
    {
        $this->season = $season;
    }

    public function openCreateModal(): void
    {
        $this->reset(['editingId', 'positionFrom', 'positionTo', 'points']);

        // Find next available position
        $lastRule = $this->season->pointsRules()->orderBy('position_to', 'desc')->first();
        $this->positionFrom = $lastRule ? $lastRule->position_to + 1 : 1;
        $this->positionTo = $this->positionFrom;
        $this->points = 0;

        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $rule = SeasonPointsRule::findOrFail($id);
        $this->editingId = $rule->id;
        $this->positionFrom = $rule->position_from;
        $this->positionTo = $rule->position_to;
        $this->points = $rule->points;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        // Check for overlapping positions (excluding current rule if editing)
        $overlapping = $this->season->pointsRules()
            ->where(function ($query) {
                $query->whereBetween('position_from', [$this->positionFrom, $this->positionTo])
                    ->orWhereBetween('position_to', [$this->positionFrom, $this->positionTo])
                    ->orWhere(function ($q) {
                        $q->where('position_from', '<=', $this->positionFrom)
                            ->where('position_to', '>=', $this->positionTo);
                    });
            })
            ->when($this->editingId, fn ($q) => $q->where('id', '!=', $this->editingId))
            ->exists();

        if ($overlapping) {
            $this->addError('positionFrom', 'Cette plage de positions chevauche une règle existante.');

            return;
        }

        $data = [
            'season_id' => $this->season->id,
            'position_from' => $this->positionFrom,
            'position_to' => $this->positionTo,
            'points' => $this->points,
        ];

        if ($this->editingId) {
            $rule = SeasonPointsRule::findOrFail($this->editingId);
            $rule->update($data);
            session()->flash('success', 'Règle de points mise à jour.');
        } else {
            SeasonPointsRule::create($data);
            session()->flash('success', 'Règle de points créée.');
        }

        $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['editingId', 'positionFrom', 'positionTo', 'points']);
        $this->resetValidation();
    }

    public function delete(int $id): void
    {
        SeasonPointsRule::findOrFail($id)->delete();
        session()->flash('success', 'Règle de points supprimée.');
    }

    public function createDefaultRules(): void
    {
        if ($this->season->pointsRules()->exists()) {
            session()->flash('error', 'Des règles existent déjà. Supprimez-les d\'abord.');

            return;
        }

        // Standard F1-like points
        $defaultRules = [
            ['position_from' => 1, 'position_to' => 1, 'points' => 25],
            ['position_from' => 2, 'position_to' => 2, 'points' => 18],
            ['position_from' => 3, 'position_to' => 3, 'points' => 15],
            ['position_from' => 4, 'position_to' => 4, 'points' => 12],
            ['position_from' => 5, 'position_to' => 5, 'points' => 10],
            ['position_from' => 6, 'position_to' => 6, 'points' => 8],
            ['position_from' => 7, 'position_to' => 7, 'points' => 6],
            ['position_from' => 8, 'position_to' => 8, 'points' => 4],
            ['position_from' => 9, 'position_to' => 9, 'points' => 2],
            ['position_from' => 10, 'position_to' => 10, 'points' => 1],
        ];

        foreach ($defaultRules as $rule) {
            $this->season->pointsRules()->create($rule);
        }

        session()->flash('success', 'Règles de points par défaut créées (système F1).');
    }

    public function render()
    {
        $rules = $this->season->pointsRules()->ordered()->get();

        return view('livewire.admin.seasons.points-rules', [
            'rules' => $rules,
        ]);
    }
}
