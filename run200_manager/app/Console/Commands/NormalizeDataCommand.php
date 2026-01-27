<?php

namespace App\Console\Commands;

use App\Models\Car;
use App\Models\Pilot;
use App\Models\Race;
use App\Models\User;
use Illuminate\Console\Command;

class NormalizeDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:normalize
                            {--model= : Modèle spécifique à normaliser (user, pilot, car, race)}
                            {--dry-run : Exécuter en mode simulation sans modifier les données}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Normalise les données existantes en base de données (noms, emails, etc.)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $model = $this->option('model');

        if ($dryRun) {
            $this->warn('🔍 Mode simulation activé - aucune modification ne sera effectuée.');
            $this->newLine();
        }

        $this->info('🚀 Démarrage de la normalisation des données...');
        $this->newLine();

        $stats = [
            'users' => 0,
            'pilots' => 0,
            'cars' => 0,
            'races' => 0,
        ];

        if (! $model || $model === 'user') {
            $stats['users'] = $this->normalizeUsers($dryRun);
        }

        if (! $model || $model === 'pilot') {
            $stats['pilots'] = $this->normalizePilots($dryRun);
        }

        if (! $model || $model === 'car') {
            $stats['cars'] = $this->normalizeCars($dryRun);
        }

        if (! $model || $model === 'race') {
            $stats['races'] = $this->normalizeRaces($dryRun);
        }

        $this->newLine();
        $this->info('✅ Normalisation terminée !');
        $this->table(
            ['Modèle', 'Enregistrements mis à jour'],
            collect($stats)->map(fn ($count, $model) => [ucfirst($model), $count])->toArray()
        );

        return Command::SUCCESS;
    }

    /**
     * Normalise les utilisateurs.
     */
    protected function normalizeUsers(bool $dryRun): int
    {
        $this->info('👤 Normalisation des utilisateurs...');

        $count = 0;
        $bar = $this->output->createProgressBar(User::count());
        $bar->start();

        User::chunk(100, function ($users) use ($dryRun, &$count, $bar) {
            foreach ($users as $user) {
                $changes = [];

                // Le cast s'occupe de la normalisation lors de l'assignation
                $originalName = $user->getRawOriginal('name');
                $originalEmail = $user->getRawOriginal('email');

                // Réassigner pour déclencher le cast
                $user->name = $originalName;
                $user->email = $originalEmail;

                // Vérifier si des changements ont été faits
                if ($user->isDirty()) {
                    $changes = $user->getDirty();

                    if (! $dryRun) {
                        $user->saveQuietly(); // saveQuietly pour éviter les événements
                    }
                    $count++;

                    if ($this->getOutput()->isVerbose()) {
                        $this->newLine();
                        $this->line("  User #{$user->id}: ".json_encode($changes));
                    }
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();

        return $count;
    }

    /**
     * Normalise les pilotes.
     */
    protected function normalizePilots(bool $dryRun): int
    {
        $this->info('🏎️  Normalisation des pilotes...');

        $count = 0;
        $bar = $this->output->createProgressBar(Pilot::count());
        $bar->start();

        Pilot::chunk(100, function ($pilots) use ($dryRun, &$count, $bar) {
            foreach ($pilots as $pilot) {
                $fieldsToNormalize = [
                    'first_name',
                    'last_name',
                    'birth_place',
                    'city',
                    'postal_code',
                    'phone',
                    'license_number',
                    'guardian_first_name',
                    'guardian_last_name',
                    'guardian_license_number',
                    'emergency_contact_name',
                    'emergency_contact_phone',
                ];

                // Réassigner chaque champ pour déclencher les casts
                foreach ($fieldsToNormalize as $field) {
                    $original = $pilot->getRawOriginal($field);
                    if ($original !== null) {
                        $pilot->{$field} = $original;
                    }
                }

                if ($pilot->isDirty()) {
                    $changes = $pilot->getDirty();

                    if (! $dryRun) {
                        $pilot->saveQuietly();
                    }
                    $count++;

                    if ($this->getOutput()->isVerbose()) {
                        $this->newLine();
                        $this->line("  Pilot #{$pilot->id}: ".json_encode($changes));
                    }
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();

        return $count;
    }

    /**
     * Normalise les voitures.
     */
    protected function normalizeCars(bool $dryRun): int
    {
        $this->info('🚗 Normalisation des voitures...');

        $count = 0;
        $bar = $this->output->createProgressBar(Car::count());
        $bar->start();

        Car::chunk(100, function ($cars) use ($dryRun, &$count, $bar) {
            foreach ($cars as $car) {
                $fieldsToNormalize = ['make', 'model'];

                foreach ($fieldsToNormalize as $field) {
                    $original = $car->getRawOriginal($field);
                    if ($original !== null) {
                        $car->{$field} = $original;
                    }
                }

                if ($car->isDirty()) {
                    $changes = $car->getDirty();

                    if (! $dryRun) {
                        $car->saveQuietly();
                    }
                    $count++;

                    if ($this->getOutput()->isVerbose()) {
                        $this->newLine();
                        $this->line("  Car #{$car->id}: ".json_encode($changes));
                    }
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();

        return $count;
    }

    /**
     * Normalise les courses.
     */
    protected function normalizeRaces(bool $dryRun): int
    {
        $this->info('🏁 Normalisation des courses...');

        $count = 0;
        $bar = $this->output->createProgressBar(Race::count());
        $bar->start();

        Race::chunk(100, function ($races) use ($dryRun, &$count, $bar) {
            foreach ($races as $race) {
                $fieldsToNormalize = ['name', 'location'];

                foreach ($fieldsToNormalize as $field) {
                    $original = $race->getRawOriginal($field);
                    if ($original !== null) {
                        $race->{$field} = $original;
                    }
                }

                if ($race->isDirty()) {
                    $changes = $race->getDirty();

                    if (! $dryRun) {
                        $race->saveQuietly();
                    }
                    $count++;

                    if ($this->getOutput()->isVerbose()) {
                        $this->newLine();
                        $this->line("  Race #{$race->id}: ".json_encode($changes));
                    }
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();

        return $count;
    }
}
