<?php

namespace Database\Seeders;

use App\Models\Car;
use App\Models\CarCategory;
use App\Models\Checkpoint;
use App\Models\CheckpointPassage;
use App\Models\Pilot;
use App\Models\QrToken;
use App\Models\Race;
use App\Models\RaceRegistration;
use App\Models\RaceResult;
use App\Models\ResultImport;
use App\Models\Season;
use App\Models\TechInspection;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    private array $checkpoints = [];

    private array $categories = [];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Début du peuplement de la base de données...');
        $this->command->newLine();

        // Cache checkpoints and categories
        $this->checkpoints = Checkpoint::all()->keyBy('code')->toArray();
        $this->categories = CarCategory::all()->toArray();

        // Create seasons
        $seasons = $this->createSeasons();

        // Create staff users
        $staffUsers = $this->createStaffUsers();

        // Create pilots with cars
        $pilots = $this->createPilotsWithCars(40);

        // Create races for each season
        $this->createRacesAndRegistrations($seasons, $pilots, $staffUsers);

        $this->command->newLine();
        $this->command->info('✅ Base de données peuplée avec succès!');
        $this->command->newLine();
        $this->displaySummary();
    }

    /**
     * Create seasons
     */
    private function createSeasons(): array
    {
        $this->command->info('📅 Création des saisons...');

        $seasons = [];

        // Past season (2025)
        $seasons['past'] = Season::create([
            'name' => 'Saison 2025',
            'start_date' => '2025-03-01',
            'end_date' => '2025-10-31',
            'is_active' => false,
        ]);

        // Current season (2026)
        $seasons['current'] = Season::create([
            'name' => 'Saison 2026',
            'start_date' => '2026-03-01',
            'end_date' => '2026-10-31',
            'is_active' => true,
        ]);

        $this->command->info('   ✓ 2 saisons créées (2025 terminée, 2026 active)');

        return $seasons;
    }

    /**
     * Create staff users with different roles
     */
    private function createStaffUsers(): array
    {
        $this->command->info('👥 Création des utilisateurs staff...');

        $users = [];

        // Admin
        $users['admin'] = User::factory()->create([
            'name' => 'Jean-Pierre Admin',
            'email' => 'admin@run200.com',
            'password' => Hash::make('password'),
        ]);
        $users['admin']->assignRole('ADMIN');

        // Staff Administratif
        $users['staff_admin'] = User::factory()->create([
            'name' => 'Marie Dupont',
            'email' => 'marie.dupont@run200.com',
            'password' => Hash::make('password'),
        ]);
        $users['staff_admin']->assignRole('STAFF_ADMINISTRATIF');

        // Contrôleur Technique
        $users['tech'] = User::factory()->create([
            'name' => 'Pierre Martin',
            'email' => 'pierre.martin@run200.com',
            'password' => Hash::make('password'),
        ]);
        $users['tech']->assignRole('CONTROLEUR_TECHNIQUE');

        // Staff Entrée
        $users['entry'] = User::factory()->create([
            'name' => 'Sophie Bernard',
            'email' => 'sophie.bernard@run200.com',
            'password' => Hash::make('password'),
        ]);
        $users['entry']->assignRole('STAFF_ENTREE');

        // Staff Sono
        $users['sono'] = User::factory()->create([
            'name' => 'Lucas Petit',
            'email' => 'lucas.petit@run200.com',
            'password' => Hash::make('password'),
        ]);
        $users['sono']->assignRole('STAFF_SONO');

        $this->command->info('   ✓ 5 utilisateurs staff créés');

        return $users;
    }

    /**
     * Create pilots with their cars
     */
    private function createPilotsWithCars(int $count): array
    {
        $this->command->info("🏎️  Création de {$count} pilotes avec leurs voitures...");

        $pilots = [];
        $raceNumbersUsed = [];

        // Famous racing-inspired names for more realism
        $firstNames = ['Max', 'Lewis', 'Charles', 'Carlos', 'Lando', 'Oscar', 'George', 'Fernando', 'Sergio', 'Pierre', 'Yuki', 'Valtteri', 'Kevin', 'Nico', 'Daniel', 'Lance', 'Zhou', 'Logan', 'Alex', 'Nyck'];
        $lastNames = ['Verstappen', 'Hamilton', 'Leclerc', 'Sainz', 'Norris', 'Piastri', 'Russell', 'Alonso', 'Perez', 'Gasly', 'Tsunoda', 'Bottas', 'Magnussen', 'Hulkenberg', 'Ricciardo', 'Stroll', 'Guanyu', 'Sargeant', 'Albon', 'De Vries'];

        for ($i = 0; $i < $count; $i++) {
            // Create user for pilot
            $firstName = $firstNames[$i % count($firstNames)];
            $lastName = $lastNames[$i % count($lastNames)].($i >= 20 ? ' Jr' : '');

            $user = User::factory()->create([
                'name' => "{$firstName} {$lastName}",
                'email' => strtolower("{$firstName}.{$lastName}").($i >= 20 ? $i : '').'@example.com',
                'password' => Hash::make('password'),
            ]);
            $user->assignRole('PILOTE');

            // Determine if minor (about 10% of pilots)
            $isMinor = $i % 10 === 0;

            // Create pilot
            $pilot = Pilot::create([
                'user_id' => $user->id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'license_number' => str_pad((string) (100000 + $i), 6, '0', STR_PAD_LEFT),
                'birth_date' => $isMinor
                    ? now()->subYears(rand(14, 17))->subDays(rand(0, 365))->format('Y-m-d')
                    : now()->subYears(rand(20, 55))->subDays(rand(0, 365))->format('Y-m-d'),
                'birth_place' => fake()->city(),
                'phone' => fake()->phoneNumber(),
                'address' => fake()->address(),
                'is_minor' => $isMinor,
                'guardian_first_name' => $isMinor ? fake()->firstName() : null,
                'guardian_last_name' => $isMinor ? $lastName : null,
                'guardian_phone' => $isMinor ? fake()->phoneNumber() : null,
                'permit_number' => $isMinor ? null : fake()->regexify('[A-Z]{2}[0-9]{6}'),
                'permit_date' => $isMinor ? null : now()->subYears(rand(1, 20))->subDays(rand(0, 365))->format('Y-m-d'),
            ]);

            // Create 1-3 cars per pilot
            $numCars = rand(1, 3);
            for ($c = 0; $c < $numCars; $c++) {
                // Generate unique race number
                do {
                    $raceNumber = rand(1, 999);
                } while (in_array($raceNumber, $raceNumbersUsed));
                $raceNumbersUsed[] = $raceNumber;

                // Select random category
                $category = $this->categories[array_rand($this->categories)];

                Car::create([
                    'pilot_id' => $pilot->id,
                    'car_category_id' => $category['id'],
                    'race_number' => $raceNumber,
                    'make' => $this->getRandomCarMake(),
                    'model' => $this->getRandomCarModel(),
                    'notes' => rand(0, 3) === 0 ? fake()->sentence() : null,
                ]);
            }

            $pilots[] = $pilot;
        }

        $carCount = Car::count();
        $this->command->info("   ✓ {$count} pilotes et {$carCount} voitures créés");

        return $pilots;
    }

    /**
     * Create races and registrations
     */
    private function createRacesAndRegistrations(array $seasons, array $pilots, array $staffUsers): void
    {
        $this->command->info('🏁 Création des courses et inscriptions...');

        // ========== PAST SEASON (2025) - All races completed ==========
        $pastRaces = $this->createPastSeasonRaces($seasons['past'], $pilots, $staffUsers);

        // ========== CURRENT SEASON (2026) ==========
        $currentRaces = $this->createCurrentSeasonRaces($seasons['current'], $pilots, $staffUsers);

        $totalRaces = Race::count();
        $totalRegistrations = RaceRegistration::count();
        $this->command->info("   ✓ {$totalRaces} courses et {$totalRegistrations} inscriptions créées");
    }

    /**
     * Create past season races (all completed with results)
     */
    private function createPastSeasonRaces(Season $season, array $pilots, array $staffUsers): array
    {
        $races = [];
        $locations = [
            ['name' => 'Grand Prix de Monaco', 'location' => 'Monte-Carlo, Monaco'],
            ['name' => 'Course de Spa', 'location' => 'Spa-Francorchamps, Belgique'],
            ['name' => 'Grand Prix de France', 'location' => 'Le Castellet, France'],
            ['name' => 'Course de Monza', 'location' => 'Monza, Italie'],
            ['name' => 'Course du Nürburgring', 'location' => 'Nürburg, Allemagne'],
        ];

        foreach ($locations as $index => $loc) {
            $raceDate = now()->subMonths(12 - ($index * 2))->subDays(rand(0, 15));

            $race = Race::create([
                'season_id' => $season->id,
                'name' => $loc['name'].' 2025',
                'race_date' => $raceDate,
                'location' => $loc['location'],
                'status' => 'PUBLISHED',
            ]);

            // Create registrations and results for past races
            $this->createCompletedRaceData($race, $pilots, $staffUsers, 25);

            $races[] = $race;
        }

        return $races;
    }

    /**
     * Create current season races (various states)
     */
    private function createCurrentSeasonRaces(Season $season, array $pilots, array $staffUsers): array
    {
        $races = [];

        // Race 1: PUBLISHED (completed)
        $races[] = $this->createRace($season, [
            'name' => 'Opening Race 2026',
            'location' => 'Circuit Paul Ricard, France',
            'race_date' => '2026-03-15',
            'status' => 'PUBLISHED',
        ], $pilots, $staffUsers, 30, 'completed');

        // Race 2: RESULTS_READY (awaiting publication)
        $races[] = $this->createRace($season, [
            'name' => 'Grand Prix de Belgique 2026',
            'location' => 'Spa-Francorchamps, Belgique',
            'race_date' => '2026-04-20',
            'status' => 'RESULTS_READY',
        ], $pilots, $staffUsers, 28, 'results_ready');

        // Race 3: RUNNING (race in progress today)
        $races[] = $this->createRace($season, [
            'name' => 'Course de Monaco 2026',
            'location' => 'Monte-Carlo, Monaco',
            'race_date' => '2026-01-24', // Today
            'status' => 'RUNNING',
        ], $pilots, $staffUsers, 32, 'running');

        // Race 4: CLOSED (registrations closed, race upcoming)
        $races[] = $this->createRace($season, [
            'name' => 'Grand Prix d\'Italie 2026',
            'location' => 'Monza, Italie',
            'race_date' => '2026-02-15',
            'status' => 'CLOSED',
        ], $pilots, $staffUsers, 26, 'closed');

        // Race 5: OPEN (registrations open)
        $races[] = $this->createRace($season, [
            'name' => 'Course du Nürburgring 2026',
            'location' => 'Nürburg, Allemagne',
            'race_date' => '2026-05-10',
            'status' => 'OPEN',
        ], $pilots, $staffUsers, 18, 'open');

        // Race 6: DRAFT (not yet open)
        $races[] = $this->createRace($season, [
            'name' => 'Grand Prix de Silverstone 2026',
            'location' => 'Silverstone, Royaume-Uni',
            'race_date' => '2026-06-20',
            'status' => 'DRAFT',
        ], $pilots, $staffUsers, 0, 'draft');

        // Race 7: OPEN (future race)
        $races[] = $this->createRace($season, [
            'name' => 'Course de Barcelone 2026',
            'location' => 'Barcelone, Espagne',
            'race_date' => '2026-07-15',
            'status' => 'OPEN',
        ], $pilots, $staffUsers, 12, 'open');

        return $races;
    }

    /**
     * Create a single race with appropriate data
     */
    private function createRace(Season $season, array $raceData, array $pilots, array $staffUsers, int $registrationCount, string $type): Race
    {
        $race = Race::create([
            'season_id' => $season->id,
            'name' => $raceData['name'],
            'race_date' => $raceData['race_date'],
            'location' => $raceData['location'],
            'status' => $raceData['status'],
        ]);

        if ($registrationCount === 0) {
            return $race;
        }

        // Shuffle and pick pilots for this race
        $selectedPilots = collect($pilots)->shuffle()->take($registrationCount)->all();

        switch ($type) {
            case 'completed':
            case 'results_ready':
                $this->createCompletedRaceData($race, $selectedPilots, $staffUsers, $registrationCount);
                break;

            case 'running':
                $this->createRunningRaceData($race, $selectedPilots, $staffUsers);
                break;

            case 'closed':
                $this->createClosedRaceData($race, $selectedPilots, $staffUsers);
                break;

            case 'open':
                $this->createOpenRaceData($race, $selectedPilots, $staffUsers);
                break;
        }

        return $race;
    }

    /**
     * Create completed race data (with results)
     */
    private function createCompletedRaceData(Race $race, array $pilots, array $staffUsers, int $count): void
    {
        $selectedPilots = collect($pilots)->shuffle()->take($count)->values();
        $registrations = [];

        foreach ($selectedPilots as $index => $pilot) {
            $car = $pilot->cars()->inRandomOrder()->first();
            if (! $car) {
                continue;
            }

            $registration = RaceRegistration::create([
                'race_id' => $race->id,
                'pilot_id' => $pilot->id,
                'car_id' => $car->id,
                'status' => 'ACCEPTED',
                'paddock' => 'P'.($index + 1),
                'validated_at' => $race->race_date->subDays(rand(5, 15)),
                'validated_by' => $staffUsers['staff_admin']->id,
            ]);

            // Create QR token
            $this->createQrToken($registration);

            // Create all checkpoint passages
            $this->createAllCheckpointPassages($registration, $staffUsers);

            // Create tech inspection (passed)
            TechInspection::create([
                'race_registration_id' => $registration->id,
                'inspected_by' => $staffUsers['tech']->id,
                'status' => 'OK',
                'inspected_at' => $race->race_date->subDays(rand(1, 3)),
            ]);

            $registrations[] = $registration;
        }

        // Create result import and results
        if (count($registrations) > 0) {
            $import = ResultImport::create([
                'race_id' => $race->id,
                'uploaded_by' => $staffUsers['admin']->id,
                'original_filename' => 'results_'.Str::slug($race->name).'.csv',
                'stored_path' => 'imports/'.Str::uuid().'.csv',
                'row_count' => count($registrations),
                'status' => 'IMPORTED',
                'errors' => null,
            ]);

            // Create results with realistic times
            $baseTime = rand(120000, 180000); // 2-3 minutes base
            foreach ($registrations as $position => $registration) {
                $timeMs = $baseTime + ($position * rand(500, 3000));

                RaceResult::create([
                    'race_id' => $race->id,
                    'race_registration_id' => $registration->id,
                    'result_import_id' => $import->id,
                    'position' => $position + 1,
                    'bib' => $registration->car->getRawOriginal('race_number'),
                    'raw_time' => $this->formatTime($timeMs),
                    'time_ms' => $timeMs,
                    'pilot_name' => $registration->pilot->full_name,
                    'car_description' => $registration->car->make.' '.$registration->car->model,
                    'category_name' => $registration->car->category?->name,
                ]);
            }
        }
    }

    /**
     * Create running race data (race day)
     */
    private function createRunningRaceData(Race $race, array $pilots, array $staffUsers): void
    {
        foreach ($pilots as $index => $pilot) {
            $car = $pilot->cars()->inRandomOrder()->first();
            if (! $car) {
                continue;
            }

            $registration = RaceRegistration::create([
                'race_id' => $race->id,
                'pilot_id' => $pilot->id,
                'car_id' => $car->id,
                'status' => 'ACCEPTED',
                'paddock' => 'P'.($index + 1),
                'validated_at' => now()->subDays(rand(5, 10)),
                'validated_by' => $staffUsers['staff_admin']->id,
            ]);

            $this->createQrToken($registration);

            // All should have admin check
            $this->createCheckpointPassage($registration, 'ADMIN_CHECK', $staffUsers['staff_admin']->id);

            // Most have tech check (90%)
            if (rand(1, 10) <= 9) {
                $this->createCheckpointPassage($registration, 'TECH_CHECK', $staffUsers['tech']->id);

                TechInspection::create([
                    'race_registration_id' => $registration->id,
                    'inspected_by' => $staffUsers['tech']->id,
                    'status' => 'OK',
                    'inspected_at' => now()->subHours(rand(1, 4)),
                ]);

                // 80% have entry
                if (rand(1, 10) <= 8) {
                    $this->createCheckpointPassage($registration, 'ENTRY', $staffUsers['entry']->id);

                    // 70% have bracelet
                    if (rand(1, 10) <= 7) {
                        $this->createCheckpointPassage($registration, 'BRACELET', $staffUsers['entry']->id);
                    }
                }
            }
        }
    }

    /**
     * Create closed race data (upcoming race)
     */
    private function createClosedRaceData(Race $race, array $pilots, array $staffUsers): void
    {
        foreach ($pilots as $index => $pilot) {
            $car = $pilot->cars()->inRandomOrder()->first();
            if (! $car) {
                continue;
            }

            // Mix of statuses: 85% accepted, 10% pending, 5% refused
            $rand = rand(1, 100);
            if ($rand <= 85) {
                $status = 'ACCEPTED';
                $validatedAt = now()->subDays(rand(2, 7));
                $reason = null;
            } elseif ($rand <= 95) {
                $status = 'PENDING_VALIDATION';
                $validatedAt = null;
                $reason = null;
            } else {
                $status = 'REFUSED';
                $validatedAt = now()->subDays(rand(2, 7));
                $reason = 'Documents manquants ou incomplets';
            }

            $registration = RaceRegistration::create([
                'race_id' => $race->id,
                'pilot_id' => $pilot->id,
                'car_id' => $car->id,
                'status' => $status,
                'paddock' => $status === 'ACCEPTED' ? 'P'.($index + 1) : null,
                'validated_at' => $validatedAt,
                'validated_by' => $validatedAt ? $staffUsers['staff_admin']->id : null,
                'reason' => $reason,
            ]);

            if ($status === 'ACCEPTED') {
                $this->createQrToken($registration);
            }
        }
    }

    /**
     * Create open race data (accepting registrations)
     */
    private function createOpenRaceData(Race $race, array $pilots, array $staffUsers): void
    {
        foreach ($pilots as $index => $pilot) {
            $car = $pilot->cars()->inRandomOrder()->first();
            if (! $car) {
                continue;
            }

            // Mix of statuses: 40% accepted, 50% pending, 10% refused
            $rand = rand(1, 100);
            if ($rand <= 40) {
                $status = 'ACCEPTED';
                $validatedAt = now()->subDays(rand(1, 5));
                $reason = null;
            } elseif ($rand <= 90) {
                $status = 'PENDING_VALIDATION';
                $validatedAt = null;
                $reason = null;
            } else {
                $status = 'REFUSED';
                $validatedAt = now()->subDays(rand(1, 5));
                $reason = 'Licence non valide';
            }

            $registration = RaceRegistration::create([
                'race_id' => $race->id,
                'pilot_id' => $pilot->id,
                'car_id' => $car->id,
                'status' => $status,
                'paddock' => $status === 'ACCEPTED' && rand(0, 1) ? 'P'.($index + 1) : null,
                'validated_at' => $validatedAt,
                'validated_by' => $validatedAt ? $staffUsers['staff_admin']->id : null,
                'reason' => $reason,
            ]);

            if ($status === 'ACCEPTED') {
                $this->createQrToken($registration);
            }
        }
    }

    /**
     * Create QR token for registration
     */
    private function createQrToken(RaceRegistration $registration): void
    {
        $token = Str::random(64);
        QrToken::create([
            'race_registration_id' => $registration->id,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addDays(30),
        ]);
    }

    /**
     * Create all checkpoint passages
     */
    private function createAllCheckpointPassages(RaceRegistration $registration, array $staffUsers): void
    {
        $this->createCheckpointPassage($registration, 'ADMIN_CHECK', $staffUsers['staff_admin']->id);
        $this->createCheckpointPassage($registration, 'TECH_CHECK', $staffUsers['tech']->id);
        $this->createCheckpointPassage($registration, 'ENTRY', $staffUsers['entry']->id);
        $this->createCheckpointPassage($registration, 'BRACELET', $staffUsers['entry']->id);
    }

    /**
     * Create single checkpoint passage
     */
    private function createCheckpointPassage(RaceRegistration $registration, string $checkpointCode, int $scannedBy): void
    {
        if (! isset($this->checkpoints[$checkpointCode])) {
            return;
        }

        CheckpointPassage::create([
            'race_registration_id' => $registration->id,
            'checkpoint_id' => $this->checkpoints[$checkpointCode]['id'],
            'scanned_by' => $scannedBy,
            'scanned_at' => now()->subHours(rand(1, 48)),
        ]);
    }

    /**
     * Format time in ms to readable format
     */
    private function formatTime(int $ms): string
    {
        $minutes = floor($ms / 60000);
        $seconds = floor(($ms % 60000) / 1000);
        $milliseconds = $ms % 1000;

        return sprintf('%d:%02d.%03d', $minutes, $seconds, $milliseconds);
    }

    /**
     * Get random car make
     */
    private function getRandomCarMake(): string
    {
        $makes = [
            'Ferrari', 'Porsche', 'BMW', 'Audi', 'Mercedes-AMG',
            'McLaren', 'Lamborghini', 'Alpine', 'Aston Martin',
            'Honda', 'Toyota', 'Nissan', 'Subaru', 'Mazda',
            'Ford', 'Chevrolet', 'Dodge', 'Renault', 'Peugeot', 'Citroën',
            'Alfa Romeo', 'Lotus', 'Caterham', 'Morgan', 'TVR',
        ];

        return $makes[array_rand($makes)];
    }

    /**
     * Get random car model
     */
    private function getRandomCarModel(): string
    {
        $models = [
            '488 GT3', '911 GT3 RS', 'M4 GT3', 'R8 LMS', 'AMG GT3',
            '720S GT3', 'Huracán GT3', 'A110 GT4', 'Vantage GT3',
            'NSX GT3', 'Supra GR', 'GT-R Nismo', 'WRX STI', 'MX-5 Cup',
            'Mustang GT', 'Camaro ZL1', 'Challenger SRT', 'Mégane RS', '308 GTi', 'DS3 Racing',
            'Giulia QV', 'Exige Cup', 'Seven 620R', 'Plus 8', 'Sagaris',
        ];

        return $models[array_rand($models)];
    }

    /**
     * Display summary of created data
     */
    private function displaySummary(): void
    {
        $this->command->table(
            ['Entité', 'Nombre'],
            [
                ['Saisons', Season::count()],
                ['Courses', Race::count()],
                ['Utilisateurs', User::count()],
                ['Pilotes', Pilot::count()],
                ['Voitures', Car::count()],
                ['Inscriptions', RaceRegistration::count()],
                ['QR Tokens', QrToken::count()],
                ['Passages checkpoints', CheckpointPassage::count()],
                ['Inspections techniques', TechInspection::count()],
                ['Imports résultats', ResultImport::count()],
                ['Résultats courses', RaceResult::count()],
            ]
        );

        $this->command->newLine();
        $this->command->info('📧 Comptes de test:');
        $this->command->table(
            ['Email', 'Mot de passe', 'Rôle'],
            [
                ['admin@run200.com', 'password', 'ADMIN'],
                ['marie.dupont@run200.com', 'password', 'STAFF_ADMINISTRATIF'],
                ['pierre.martin@run200.com', 'password', 'CONTROLEUR_TECHNIQUE'],
                ['sophie.bernard@run200.com', 'password', 'STAFF_ENTREE'],
                ['lucas.petit@run200.com', 'password', 'STAFF_SONO'],
                ['max.verstappen@example.com', 'password', 'PILOTE'],
                ['lewis.hamilton@example.com', 'password', 'PILOTE'],
            ]
        );
    }
}
