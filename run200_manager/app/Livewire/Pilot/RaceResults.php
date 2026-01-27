<?php

declare(strict_types=1);

namespace App\Livewire\Pilot;

use App\Models\Race;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class RaceResults extends Component
{
    use WithPagination;

    public Race $race;

    public string $searchQuery = '';

    public string $categoryFilter = '';

    public function mount(Race $race): void
    {
        // Ensure results are published
        if (! $race->isPublished()) {
            abort(404, 'Les résultats de cette course ne sont pas encore publiés.');
        }

        $this->race = $race->load(['season']);
    }

    #[Computed]
    public function results()
    {
        return $this->race->results()
            ->when($this->searchQuery, function ($query) {
                $query->where(function ($q) {
                    $q->where('pilot_name', 'like', "%{$this->searchQuery}%")
                        ->orWhere('bib', 'like', "%{$this->searchQuery}%")
                        ->orWhere('car_description', 'like', "%{$this->searchQuery}%");
                });
            })
            ->when($this->categoryFilter, function ($query) {
                $query->where('category_name', $this->categoryFilter);
            })
            ->orderBy('position')
            ->paginate(25);
    }

    #[Computed]
    public function categories(): array
    {
        return $this->race->results()
            ->whereNotNull('category_name')
            ->distinct()
            ->pluck('category_name')
            ->sort()
            ->values()
            ->toArray();
    }

    #[Computed]
    public function podium()
    {
        return $this->race->results()
            ->orderBy('position')
            ->limit(3)
            ->get();
    }

    #[Computed]
    public function statistics(): array
    {
        $results = $this->race->results;

        if ($results->isEmpty()) {
            return [];
        }

        $times = $results->pluck('time_ms')->filter();

        return [
            'total_participants' => $results->count(),
            'fastest_time' => $results->first()?->formatted_time,
            'slowest_time' => $results->sortByDesc('time_ms')->first()?->formatted_time,
            'average_time' => $times->isNotEmpty() ? $this->formatTime((int) $times->average()) : null,
            'categories_count' => $results->pluck('category_name')->filter()->unique()->count(),
        ];
    }

    private function formatTime(int $milliseconds): string
    {
        $totalSeconds = intdiv($milliseconds, 1000);
        $ms = $milliseconds % 1000;
        $minutes = intdiv($totalSeconds, 60);
        $seconds = $totalSeconds % 60;

        return sprintf('%d:%02d.%03d', $minutes, $seconds, $ms);
    }

    public function render()
    {
        return view('livewire.pilot.race-results');
    }
}
