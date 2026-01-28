<?php

namespace App\Console\Commands;

use App\Jobs\SendBulkEmailJob;
use App\Mail\RaceReminderMail;
use App\Models\Race;
use App\Models\RaceRegistration;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendRaceReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:race-reminders {--days=3 : Number of days before the race}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoyer les rappels J-3 (ou autre) aux pilotes inscrits aux courses';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $targetDate = Carbon::now()->addDays($days)->toDateString();

        $this->info("Recherche des courses du {$targetDate} (J-{$days})...");

        // Find all accepted registrations for races happening in X days
        $registrations = RaceRegistration::with(['race.season', 'pilot.user', 'car.category', 'paddockSpot'])
            ->whereHas('race', function ($query) use ($targetDate) {
                $query->whereDate('race_date', $targetDate)
                    ->whereIn('status', ['OPEN', 'CLOSED']); // Race must be active
            })
            ->where('status', 'ACCEPTED')
            ->get();

        if ($registrations->isEmpty()) {
            $this->info("Aucune course prévue dans {$days} jours avec des inscriptions acceptées.");

            return Command::SUCCESS;
        }

        $sent = 0;
        $races = $registrations->groupBy('race_id');

        foreach ($races as $raceId => $raceRegistrations) {
            $firstRegistration = $raceRegistrations->first();
            if (!$firstRegistration || !$firstRegistration->race instanceof Race) {
                continue;
            }
            /** @var Race $race */
            $race = $firstRegistration->race;
            $this->info("Course: {$race->name} ({$raceRegistrations->count()} inscriptions)");

            foreach ($raceRegistrations as $registration) {
                if ($registration->pilot && $registration->pilot->user && $registration->pilot->user->email) {
                    try {
                        SendBulkEmailJob::dispatch(
                            $registration->pilot->user,
                            new RaceReminderMail($race, $registration),
                            "Race reminder J-{$days} for race #{$race->id}"
                        );
                        $sent++;
                        $this->line("  ✓ Rappel planifié pour {$registration->pilot->fullName}");
                    } catch (\Exception $e) {
                        $this->error("  ✗ Erreur pour {$registration->pilot->fullName}: {$e->getMessage()}");
                    }
                }
            }
        }

        $this->newLine();
        $this->info("✅ {$sent} rappel(s) planifié(s) avec succès.");

        return Command::SUCCESS;
    }
}
