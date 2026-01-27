<?php

namespace App\Console\Commands;

use App\Mail\TechInspectionReminder;
use App\Models\RaceRegistration;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTechInspectionReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:tech-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoyer les rappels de vérifications techniques (VA/VT) pour les courses du lendemain';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Récupérer les courses dont le VA/VT est demain (race_date - 1 jour)
        $tomorrow = Carbon::tomorrow()->toDateString();

        $registrations = RaceRegistration::with(['race', 'pilot.user', 'car'])
            ->whereHas('race', function ($query) use ($tomorrow) {
                $query->whereDate('race_date', '>', $tomorrow);
            })
            ->whereIn('status', ['ACCEPTED', 'PENDING_VALIDATION'])
            ->whereDoesntHave('techInspection')
            ->get();

        if ($registrations->isEmpty()) {
            $this->info('Aucun rappel à envoyer aujourd\'hui.');

            return Command::SUCCESS;
        }

        $sent = 0;
        foreach ($registrations as $registration) {
            // Vérifier que le VA/VT est bien demain
            $techDate = $registration->race->race_date->copy()->subDay()->toDateString();
            if ($techDate === $tomorrow) {
                try {
                    Mail::to($registration->pilot->user->email)
                        ->send(new TechInspectionReminder($registration));
                    $sent++;
                    $this->info("Rappel envoyé à {$registration->pilot->user->name}");
                } catch (\Exception $e) {
                    $this->error("Erreur pour {$registration->pilot->user->name}: {$e->getMessage()}");
                }
            }
        }

        $this->info("{$sent} rappel(s) envoyé(s) avec succès.");

        return Command::SUCCESS;
    }
}
