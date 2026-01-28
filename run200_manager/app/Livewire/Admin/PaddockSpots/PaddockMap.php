<?php

declare(strict_types=1);

namespace App\Livewire\Admin\PaddockSpots;

use App\Models\PaddockSpot;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class PaddockMap extends Component
{
    public string $mapImage = '';

    // Dimensions de la carte (suffisamment grandes pour une carte circulaire)
    public int $mapWidth = 2000;

    public int $mapHeight = 2000;

    public ?int $selectedSpotId = null;

    public bool $editMode = false;

    public function mount(): void
    {
        // Image par défaut - peut être configurée dans les settings
        $this->mapImage = asset('images/paddock-map.svg');
    }

    /**
     * Met à jour la position d'un emplacement sur la carte.
     * Positionnement libre sans contraintes (sauf minimum 0).
     */
    public function updateSpotPosition(int $spotId, int $x, int $y): void
    {
        $spot = PaddockSpot::findOrFail($spotId);
        $spot->update([
            // Position libre - on garde juste un minimum de 0
            'position_x' => max(0, $x),
            'position_y' => max(0, $y),
        ]);

        $this->dispatch('spot-updated', spotId: $spotId);
    }

    public function selectSpot(?int $spotId): void
    {
        $this->selectedSpotId = $spotId;
    }

    public function toggleEditMode(): void
    {
        $this->editMode = ! $this->editMode;
        if (! $this->editMode) {
            $this->selectedSpotId = null;
        }
    }

    public function resetAllPositions(): void
    {
        PaddockSpot::query()->update([
            'position_x' => null,
            'position_y' => null,
        ]);

        session()->flash('success', 'Toutes les positions ont été réinitialisées.');
    }

    public function autoPositionByZone(): void
    {
        $zones = PaddockSpot::select('zone')->distinct()->orderBy('zone')->pluck('zone');
        $zoneIndex = 0;
        $spotsPerRow = 10;
        $spotSize = 60;
        $padding = 20;
        $zoneSpacing = 100;

        foreach ($zones as $zone) {
            $spots = PaddockSpot::where('zone', $zone)->orderBy('spot_number')->get();
            $startY = $padding + ($zoneIndex * ($zoneSpacing + (ceil($spots->count() / $spotsPerRow) * $spotSize)));

            foreach ($spots as $index => $spot) {
                $row = intdiv($index, $spotsPerRow);
                $col = $index % $spotsPerRow;

                $spot->update([
                    'position_x' => $padding + ($col * $spotSize),
                    'position_y' => $startY + ($row * $spotSize),
                ]);
            }

            $zoneIndex++;
        }

        session()->flash('success', 'Positions automatiques appliquées par zone.');
    }

    public function getSpotsProperty()
    {
        return PaddockSpot::orderBy('zone')
            ->orderBy('spot_number')
            ->get()
            ->map(function ($spot) {
                return [
                    'id' => $spot->id,
                    'spot_number' => $spot->spot_number,
                    'zone' => $spot->zone,
                    'position_x' => $spot->position_x ?? 0,
                    'position_y' => $spot->position_y ?? 0,
                    'is_available' => $spot->is_available,
                    'has_position' => $spot->position_x !== null && $spot->position_y !== null,
                ];
            });
    }

    public function getUnpositionedSpotsProperty()
    {
        return PaddockSpot::whereNull('position_x')
            ->orWhereNull('position_y')
            ->orderBy('zone')
            ->orderBy('spot_number')
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.paddock-spots.paddock-map', [
            'spots' => $this->spots,
            'unpositionedSpots' => $this->unpositionedSpots,
        ]);
    }
}
