<?php

namespace App\Livewire\Staff\Cars;

use App\Models\Car;
use App\Models\CarTechInspectionHistory;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class TechInspectionHistory extends Component
{
    use WithPagination;

    public Car $car;

    public string $statusFilter = '';

    public ?int $inspectorFilter = null;

    public ?string $fromDate = null;

    public ?string $toDate = null;

    public function mount(Car $car): void
    {
        $this->car = $car->load(['pilot', 'category']);
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingInspectorFilter(): void
    {
        $this->resetPage();
    }

    public function updatingFromDate(): void
    {
        $this->resetPage();
    }

    public function updatingToDate(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->statusFilter = '';
        $this->inspectorFilter = null;
        $this->fromDate = null;
        $this->toDate = null;
        $this->resetPage();
    }

    public function getHistoryProperty()
    {
        $query = CarTechInspectionHistory::query()
            ->where('car_id', $this->car->id)
            ->with(['inspector', 'registration.race']);

        // Filtres
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->inspectorFilter) {
            $query->where('inspected_by', $this->inspectorFilter);
        }

        if ($this->fromDate) {
            $query->whereDate('inspected_at', '>=', $this->fromDate);
        }

        if ($this->toDate) {
            $query->whereDate('inspected_at', '<=', $this->toDate);
        }

        return $query->orderBy('inspected_at', 'desc')->paginate(15);
    }

    public function getStatsProperty()
    {
        $allInspections = CarTechInspectionHistory::where('car_id', $this->car->id)->get();

        return [
            'total' => $allInspections->count(),
            'ok' => $allInspections->where('status', 'OK')->count(),
            'fail' => $allInspections->where('status', 'FAIL')->count(),
            'last_inspection' => $allInspections->sortByDesc('inspected_at')->first(),
        ];
    }

    public function getInspectorsProperty()
    {
        // Get unique inspectors from this car's tech inspection history
        $inspectorIds = \App\Models\CarTechInspectionHistory::where('car_id', $this->car->id)
            ->distinct()
            ->pluck('inspected_by')
            ->filter();

        return \App\Models\User::whereIn('id', $inspectorIds)
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.staff.cars.tech-inspection-history', [
            'history' => $this->history,
            'stats' => $this->stats,
            'inspectors' => $this->inspectors,
        ]);
    }
}
