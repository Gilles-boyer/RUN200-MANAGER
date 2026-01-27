<?php

namespace App\Livewire\Staff;

use App\Models\Race;
use App\Models\RaceRegistration;
use App\Models\Season;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Dashboard extends Component
{
    #[Computed]
    public function activeSeason(): ?Season
    {
        return Season::active()->latest()->first();
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'pending_registrations' => RaceRegistration::where('status', 'PENDING_VALIDATION')->count(),
            'accepted_registrations' => RaceRegistration::whereIn('status', ['ACCEPTED', 'ADMIN_CHECKED'])->count(),
            'tech_pending' => RaceRegistration::where('status', 'ADMIN_CHECKED')->count(),
            'upcoming_races' => Race::where('race_date', '>=', now())->count(),
            'open_races' => Race::where('status', 'OPEN')->count(),
            'today_registrations' => RaceRegistration::whereDate('created_at', today())->count(),
        ];
    }

    #[Computed]
    public function todayActivity(): array
    {
        // Activité d'aujourd'hui par heure (approche agnostique de la DB)
        $registrations = RaceRegistration::whereDate('created_at', today())
            ->get()
            ->groupBy(fn ($reg) => $reg->created_at->format('H'))
            ->map(fn ($group) => $group->count());

        $hours = [];
        $counts = [];
        for ($i = 8; $i <= 20; $i++) {
            $hours[] = sprintf('%02d:00', $i);
            $hourKey = sprintf('%02d', $i);
            $counts[] = $registrations->get($hourKey, 0);
        }

        return [
            'labels' => $hours,
            'data' => $counts,
        ];
    }

    #[Computed]
    public function weeklyActivity(): array
    {
        // Activité sur les 7 derniers jours (approche agnostique de la DB)
        $registrations = RaceRegistration::where('created_at', '>=', now()->subDays(7))
            ->get()
            ->groupBy(fn ($reg) => $reg->created_at->format('Y-m-d'))
            ->map(fn ($group) => $group->count())
            ->sortKeys();

        return [
            'labels' => $registrations->keys()->map(fn ($d) => \Carbon\Carbon::parse($d)->translatedFormat('D d'))->toArray(),
            'data' => $registrations->values()->toArray(),
        ];
    }

    #[Computed]
    public function checkpointStats(): array
    {
        // Stats des checkpoints aujourd'hui
        $stats = DB::table('checkpoint_passages')
            ->join('checkpoints', 'checkpoint_passages.checkpoint_id', '=', 'checkpoints.id')
            ->whereDate('checkpoint_passages.scanned_at', today())
            ->select('checkpoints.name', DB::raw('COUNT(*) as count'))
            ->groupBy('checkpoints.name')
            ->get();

        return [
            'labels' => $stats->pluck('name')->toArray(),
            'data' => $stats->pluck('count')->toArray(),
        ];
    }

    #[Computed]
    public function upcomingRaces()
    {
        return Race::with('season')
            ->where('race_date', '>=', now())
            ->orderBy('race_date')
            ->take(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.staff.dashboard')
            ->layout('layouts.app');
    }
}
