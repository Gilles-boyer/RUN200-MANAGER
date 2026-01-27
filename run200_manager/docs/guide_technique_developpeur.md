# RUN200 MANAGER - GUIDE TECHNIQUE DÉVELOPPEUR
**Date** : 26 janvier 2026  
**Version** : 1.0  
**Audience** : Développeurs Backend/Frontend

---

## 📋 TABLE DES MATIÈRES

1. [Configuration Environnement](#configuration-environnement)
2. [Architecture du Code](#architecture-du-code)
3. [Modèles & Relations](#modèles--relations)
4. [Use Cases Métier](#use-cases-métier)
5. [Composants Livewire](#composants-livewire)
6. [Testing](#testing)
7. [Best Practices](#best-practices)
8. [Debugging](#debugging)

---

## 🔧 CONFIGURATION ENVIRONNEMENT

### Prérequis

```bash
PHP 8.2+ avec extensions :
- bcmath
- ctype
- curl
- dom
- fileinfo
- gd          # IMPORTANT pour génération PDF
- json
- mbstring
- openssl
- pcre
- pdo
- pdo_mysql
- tokenizer
- xml

Composer 2+
Node.js 18+
MySQL 8+ (ou SQLite pour dev)
```

### Installation locale

```bash
# 1. Clone
git clone https://github.com/your-org/run200-manager.git
cd run200-manager

# 2. Dépendances
composer install
npm install

# 3. Configuration
cp .env.example .env
php artisan key:generate

# Éditer .env :
DB_CONNECTION=mysql
DB_DATABASE=run200_manager
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...

# 4. Base de données
php artisan migrate:fresh --seed

# 5. Assets
npm run dev

# 6. Serveur
php artisan serve
```

### Comptes de test (après seed)

| Email | Password | Rôle |
|-------|----------|------|
| admin@run200.com | password | ADMIN |
| pilot@run200.com | password | PILOTE |
| staff@run200.com | password | STAFF_ADMINISTRATIF |

---

## 🏗️ ARCHITECTURE DU CODE

### Principes fondamentaux

Le projet suit **Clean Architecture** avec 4 couches :

```
Domain (règles métier) 
   ↓
Application (cas d'usage)
   ↓
Infrastructure (implémentations techniques)
   ↓
Presentation (UI Livewire)
```

### Structure des dossiers

```
app/
├── Domain/                    # Couche métier pure
│   ├── Car/
│   │   ├── ValueObjects/
│   │   │   ├── RaceNumber.php         # VO pour n° course (0-999)
│   │   │   └── VehicleDetails.php     # VO détails véhicule
│   │   └── Exceptions/
│   │       └── DuplicateRaceNumberException.php
│   │
│   ├── Championship/
│   │   ├── ValueObjects/
│   │   │   └── PointsRule.php         # VO règles de points
│   │   └── Services/
│   │       └── StandingsCalculator.php # Calcul classements
│   │
│   ├── Payment/
│   │   ├── ValueObjects/
│   │   │   ├── Amount.php             # VO montant
│   │   │   └── PaymentMethod.php      # VO méthode paiement
│   │   └── Enums/
│   │       └── PaymentStatus.php      # PENDING, COMPLETED, FAILED, REFUNDED
│   │
│   ├── Pilot/
│   │   ├── ValueObjects/
│   │   │   ├── LicenseNumber.php      # VO licence (max 6 digits, unique)
│   │   │   └── PersonalInfo.php       # VO infos perso
│   │   └── Exceptions/
│   │       └── InvalidLicenseNumberException.php
│   │
│   └── Registration/
│       ├── ValueObjects/
│       │   └── RegistrationStatus.php # PENDING, ACCEPTED, REJECTED, CANCELLED
│       └── Exceptions/
│           ├── RegistrationClosedException.php
│           └── DuplicateRegistrationException.php
│
├── Application/               # Use cases métier
│   ├── Championship/UseCases/
│   │   └── RebuildSeasonStandings.php
│   │
│   ├── Payments/UseCases/
│   │   ├── CreateStripeCheckout.php
│   │   ├── HandleStripeWebhook.php
│   │   ├── RecordManualPayment.php
│   │   └── RefundStripePayment.php
│   │
│   ├── Registrations/UseCases/
│   │   ├── AssignPaddock.php
│   │   ├── RecordTechInspection.php
│   │   ├── ScanCheckpoint.php
│   │   ├── SubmitRegistration.php
│   │   ├── UpdateEngagementFormValidation.php
│   │   └── ValidateRegistration.php
│   │
│   └── Results/UseCases/
│       ├── ImportRaceResults.php
│       └── PublishRaceResults.php
│
├── Infrastructure/            # Implémentations techniques
│   ├── Import/
│   │   └── CsvResultsParser.php       # Parsing CSV résultats
│   │
│   ├── Payments/
│   │   └── StripePaymentGateway.php   # Gateway Stripe
│   │
│   ├── Pdf/
│   │   ├── DriverCardPdfService.php   # E-carte pilote PDF
│   │   ├── EngagementFormPdfService.php # Fiche engagement PDF
│   │   └── EngagedListPdfService.php  # Liste engagés PDF
│   │
│   ├── Persistence/
│   │   └── EloquentResultRepository.php # Repository résultats
│   │
│   └── Qr/
│       └── QrTokenService.php         # Génération QR codes sécurisés
│
├── Models/                    # Modèles Eloquent (17)
│   ├── User.php              # Utilisateur (HasRoles)
│   ├── Pilot.php             # Pilote
│   ├── Car.php               # Véhicule
│   ├── CarCategory.php       # Catégorie véhicule
│   ├── Season.php            # Saison
│   ├── Race.php              # Course
│   ├── RaceRegistration.php  # Inscription course
│   ├── Payment.php           # Paiement
│   ├── Checkpoint.php        # Point de contrôle
│   ├── CheckpointPassage.php # Passage checkpoint
│   ├── TechInspection.php    # Contrôle technique
│   ├── EngagementForm.php    # Fiche d'engagement
│   ├── QrToken.php           # Token QR
│   ├── ResultImport.php      # Import résultats
│   ├── RaceResult.php        # Résultat course
│   ├── SeasonPointsRule.php  # Règles points saison
│   ├── SeasonStanding.php    # Classement général
│   └── SeasonCategoryStanding.php # Classement catégorie
│
├── Livewire/                 # Composants UI (38)
│   ├── Public/               # Pages publiques
│   │   ├── ChampionshipStandings.php
│   │   └── RaceCalendar.php
│   │
│   ├── Admin/                # Interface admin
│   │   ├── Championship.php
│   │   ├── Dashboard.php
│   │   ├── Races/
│   │   │   ├── Form.php
│   │   │   └── Index.php
│   │   ├── Seasons/
│   │   │   ├── Form.php
│   │   │   └── Index.php
│   │   └── Users/
│   │       └── Index.php
│   │
│   ├── Pilot/                # Interface pilote
│   │   ├── Dashboard.php
│   │   ├── Cars/
│   │   │   ├── Form.php
│   │   │   └── Index.php
│   │   ├── Profile/
│   │   │   ├── Edit.php
│   │   │   └── Show.php
│   │   ├── Races/
│   │   │   └── Index.php
│   │   ├── Registrations/
│   │   │   ├── Checkout.php
│   │   │   ├── Create.php
│   │   │   ├── Ecard.php
│   │   │   ├── Index.php
│   │   │   ├── Payment.php
│   │   │   ├── PaymentCancel.php
│   │   │   └── PaymentSuccess.php
│   │   └── RaceResults.php
│   │
│   └── Staff/                # Interface staff
│       ├── Dashboard.php
│       ├── Pilots/
│       │   ├── Create.php
│       │   ├── Edit.php
│       │   └── Index.php
│       ├── Registrations/
│       │   ├── EngagementSign.php
│       │   ├── Index.php
│       │   ├── TechInspection.php
│       │   └── Validate.php
│       ├── Results/
│       │   └── Import.php
│       └── Scan/
│           └── Scanner.php
│
├── Http/
│   ├── Controllers/
│   │   └── Webhook/
│   │       └── StripeWebhookController.php
│   ├── Middleware/
│   │   ├── RedirectBasedOnRole.php
│   │   └── EnsurePilotCanRegisterForRace.php
│   └── Policies/
│       ├── CarPolicy.php
│       ├── PilotPolicy.php
│       ├── RacePolicy.php
│       ├── RaceRegistrationPolicy.php
│       └── SeasonPolicy.php
│
├── Jobs/
│   └── RebuildSeasonStandingsJob.php
│
└── Console/Commands/
    ├── MigrateEngagementValidations.php
    ├── FixEngagementTechDate.php
    └── CheckEngagementValidations.php
```

---

## 🗄️ MODÈLES & RELATIONS

### Diagramme ERD simplifié

```
User ──1:1── Pilot ──1:N── Car
                │            │
                │            │
                └────1:N─── RaceRegistration ──N:1── Race ──N:1── Season
                              │
                              ├──1:1── Payment
                              ├──1:1── EngagementForm
                              ├──1:1── TechInspection
                              ├──1:N── CheckpointPassage
                              ├──1:N── QrToken
                              └──1:1── RaceResult
```

### Relations importantes

#### Pilot
```php
class Pilot extends Model
{
    public function user(): BelongsTo           // 1:1 User
    public function cars(): HasMany             // 1:N Car
    public function raceRegistrations(): HasMany // 1:N RaceRegistration
    public function seasonStandings(): HasMany   // 1:N SeasonStanding
}
```

#### RaceRegistration
```php
class RaceRegistration extends Model
{
    public function race(): BelongsTo           // N:1 Race
    public function pilot(): BelongsTo          // N:1 Pilot
    public function car(): BelongsTo            // N:1 Car
    public function payment(): HasOne           // 1:1 Payment
    public function engagementForm(): HasOne    // 1:1 EngagementForm
    public function techInspection(): HasOne    // 1:1 TechInspection
    public function checkpointPassages(): HasMany // 1:N CheckpointPassage
    public function qrTokens(): HasMany         // 1:N QrToken
    public function result(): HasOne            // 1:1 RaceResult
}
```

#### EngagementForm (Fiche d'engagement)
```php
class EngagementForm extends Model
{
    public function registration(): BelongsTo   // N:1 RaceRegistration
    public function witness(): BelongsTo        // N:1 User
    public function adminValidator(): BelongsTo // N:1 User (nullable)
    
    // Colonnes importantes :
    // - signature_data (longtext)
    // - tech_controller_name, tech_checked_at, tech_notes
    // - admin_validated_by, admin_validated_at, admin_notes
    // - pilot_permit_number, pilot_permit_date (ajoutés récemment)
}
```

---

## 💼 USE CASES MÉTIER

### Pattern Use Case

Tous les use cases suivent le même pattern :

```php
namespace App\Application\[Domain]\UseCases;

class [ActionName]
{
    public function __construct(
        private DependencyInterface $dependency
    ) {}
    
    public function execute([DTOClass] $dto): [ReturnType]
    {
        DB::beginTransaction();
        
        try {
            // 1. Validation métier
            $this->validate($dto);
            
            // 2. Logique métier
            $result = $this->performAction($dto);
            
            // 3. Side effects (events, notifications)
            $this->dispatchEvents($result);
            
            DB::commit();
            
            return $result;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
```

### Exemples de Use Cases clés

#### 1. SubmitRegistration
**Responsabilité** : Créer une inscription à une course

```php
// Utilisation dans Livewire
use App\Application\Registrations\UseCases\SubmitRegistration;

class Create extends Component
{
    public function submit(SubmitRegistration $submitRegistration)
    {
        $registration = $submitRegistration->execute(
            race: $this->race,
            pilot: auth()->user()->pilot,
            car: $this->selectedCar,
            engagementData: $this->engagementForm
        );
        
        return redirect()->route('pilot.registrations.payment', $registration);
    }
}
```

#### 2. ScanCheckpoint
**Responsabilité** : Scanner un QR code checkpoint

```php
// Utilisation
use App\Application\Registrations\UseCases\ScanCheckpoint;

$passage = $scanCheckpoint->execute(
    token: $qrToken,
    checkpointCode: 'ADMIN_CHECK',
    scannedBy: auth()->user(),
    deviceInfo: request()->userAgent(),
    ipAddress: request()->ip()
);
```

#### 3. RecordTechInspection
**Responsabilité** : Enregistrer contrôle technique

```php
$inspection = $recordTechInspection->execute(
    registration: $registration,
    inspector: auth()->user(),
    status: 'OK', // ou 'REFUSED'
    notes: 'Véhicule conforme, RAS'
);

// Side effect automatique : 
// - Mise à jour engagement form (tech_checked_at, tech_controller_name)
// - Changement status registration : ADMIN_CHECKED → TECH_CHECKED_OK
```

#### 4. RebuildSeasonStandings
**Responsabilité** : Recalculer classement saison

```php
$rebuildStandings->execute($season);

// Algorithme :
// 1. Récupère tous résultats publiés de la saison
// 2. Calcule points selon barème (25-20-16-14-10-8-5)
// 3. Applique bonus +20 si participation à toutes courses
// 4. Met à jour season_standings (général)
// 5. Met à jour season_category_standings (par catégorie)
```

---

## 🎨 COMPOSANTS LIVEWIRE

### Structure d'un composant Livewire

```php
namespace App\Livewire\[Role]\[Resource];

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Page Title')]
class ComponentName extends Component
{
    // Props publiques (bindées à la vue)
    public $property;
    
    // Props protégées (internes)
    protected $internalState;
    
    // Lifecycle hooks
    public function mount($param)
    {
        $this->property = $param;
    }
    
    // Actions
    public function save()
    {
        $this->validate();
        
        // Appel Use Case
        $this->useCase->execute([...]);
        
        // Flash message
        session()->flash('success', 'Opération réussie');
        
        // Redirection
        return redirect()->route('...');
    }
    
    // Computed properties
    public function getRenderDataProperty()
    {
        return Model::all();
    }
    
    public function render()
    {
        return view('livewire.[role].[resource].component-name');
    }
}
```

### Communication entre composants

```php
// Dispatch event
$this->dispatch('entity-updated', id: $entity->id);

// Listen event
use Livewire\Attributes\On;

#[On('entity-updated')]
public function refreshData($id)
{
    $this->entity = Entity::find($id);
}
```

### Composants clés à connaître

#### Staff\Scan\Scanner
- Scanner QR codes terrain
- Validation token + status registration
- Appel ScanCheckpoint use case
- Feedback temps réel (succès/erreur)

#### Staff\Registrations\TechInspection
- Liste inscriptions ADMIN_CHECKED
- Modal validation/refus
- Appel RecordTechInspection use case
- Mise à jour auto engagement form

#### Admin\Championship
- Affichage standings général + catégories
- Bouton rebuild standings
- Dispatch RebuildSeasonStandingsJob

---

## 🧪 TESTING

### Organisation des tests

```
tests/
├── Feature/                    # Tests d'intégration
│   ├── Auth/                   # Tests authentification
│   ├── Car/                    # Tests gestion voitures
│   ├── Championship/           # Tests championnat
│   ├── Checkpoint/             # Tests checkpoints
│   ├── Payment/                # Tests paiements
│   ├── Pilot/                  # Tests gestion pilotes
│   ├── Rbac/                   # Tests RBAC
│   ├── Registration/           # Tests inscriptions
│   ├── Result/                 # Tests résultats
│   └── EngagementFormValidationTest.php
│
└── Unit/                       # Tests unitaires
    ├── Service/
    │   ├── CsvResultsParserTest.php
    │   ├── QrTokenServiceTest.php
    │   └── StandingsCalculatorTest.php
    └── ValueObject/
        ├── AmountTest.php
        ├── LicenseNumberTest.php
        ├── PointsRuleTest.php
        └── RaceNumberTest.php
```

### Écrire un test Feature

```php
use Tests\TestCase;
use App\Models\User;
use App\Models\Pilot;

test('un pilote peut créer une voiture', function () {
    // Arrange
    $user = User::factory()->create();
    $pilot = Pilot::factory()->for($user)->create();
    
    $this->actingAs($user);
    
    // Act
    $response = $this->post(route('pilot.cars.store'), [
        'race_number' => 263,
        'make' => 'Mercedes-AMG',
        'model' => 'Sagaris',
        'car_category_id' => 1,
    ]);
    
    // Assert
    $response->assertRedirect(route('pilot.cars.index'));
    
    $this->assertDatabaseHas('cars', [
        'pilot_id' => $pilot->id,
        'race_number' => 263,
    ]);
});
```

### Écrire un test Unit

```php
use App\Domain\Pilot\ValueObjects\LicenseNumber;

test('le numéro de licence doit avoir max 6 chiffres', function () {
    $license = new LicenseNumber('123456');
    
    expect($license->value())->toBe('123456');
});

test('le numéro de licence ne peut pas dépasser 6 chiffres', function () {
    new LicenseNumber('1234567'); // Devrait throw exception
})->throws(InvalidLicenseNumberException::class);
```

### Commandes de test

```bash
# Tous les tests
php artisan test

# Tests spécifiques
php artisan test --filter=Championship
php artisan test --filter=Registration

# Avec output détaillé
php artisan test --parallel

# Mode compact
php artisan test --compact

# Coverage (nécessite Xdebug)
php artisan test --coverage
php artisan test --coverage-html=coverage
```

### Factories & Seeders

```php
// UserFactory
User::factory()->create([
    'email' => 'test@example.com',
]);

// PilotFactory avec User
Pilot::factory()
    ->for(User::factory())
    ->create([
        'license_number' => '100001',
    ]);

// CarFactory avec Pilot
Car::factory()
    ->for(Pilot::factory())
    ->create([
        'race_number' => 263,
    ]);
```

---

## ✨ BEST PRACTICES

### 1. Validation des données

```php
// Toujours valider dans le Livewire
protected $rules = [
    'race_number' => 'required|integer|min:0|max:999|unique:cars,race_number',
    'make' => 'required|string|max:100',
];

public function save()
{
    $this->validate();
    // ...
}
```

### 2. Transactions DB

```php
// Toujours wrapper les opérations métier dans une transaction
DB::transaction(function () {
    $registration = RaceRegistration::create([...]);
    $payment = Payment::create([...]);
    $this->generateQrToken($registration);
});
```

### 3. Eager Loading

```php
// Éviter N+1 queries
$registrations = RaceRegistration::with([
    'pilot',
    'car.category',
    'race.season',
    'payment',
    'engagementForm.witness',
])->get();
```

### 4. Autorisations

```php
// Toujours vérifier les permissions
$this->authorize('create', Car::class);

// Ou dans Policy
public function create(User $user): bool
{
    return $user->can('cars.manage-own');
}
```

### 5. Flash Messages

```php
// Succès
session()->flash('success', 'Opération réussie');

// Erreur
session()->flash('error', 'Une erreur est survenue');

// Info
session()->flash('info', 'Information importante');
```

### 6. Events & Listeners

```php
// Dispatch event
event(new RegistrationValidated($registration));

// Listener
class SendRegistrationEmail
{
    public function handle(RegistrationValidated $event)
    {
        Mail::to($event->registration->pilot->user)
            ->send(new RegistrationAcceptedMail($event->registration));
    }
}
```

---

## 🐛 DEBUGGING

### Laravel Telescope (recommandé)

```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate

# Accès : http://localhost:8000/telescope
```

### Log Debugging

```php
// Log simple
\Log::info('Debug info', ['data' => $data]);

// Log dans use case
\Log::channel('daily')->info('Use case executed', [
    'use_case' => static::class,
    'user_id' => auth()->id(),
    'params' => $params,
]);

// Consulter logs
tail -f storage/logs/laravel.log
```

### Livewire Debugging

```php
// Dans composant Livewire
public function debug()
{
    dd($this->all()); // Dump toutes les props
}

// Dans blade
@dump($variable)
@dd($variable)

// Wire:loading indicator
<div wire:loading>
    Chargement...
</div>
```

### Database Queries

```php
// Log queries
DB::listen(function ($query) {
    \Log::info($query->sql, $query->bindings);
});

// Ou utiliser Query Log
DB::enableQueryLog();
// ... opérations DB
dd(DB::getQueryLog());
```

### Pest Debugging

```php
test('debug test', function () {
    $user = User::factory()->create();
    
    dump($user); // Affiche pendant test
    ray($user);  // Avec Ray (https://myray.app)
    
    $this->actingAs($user);
    
    $response = $this->get('/dashboard');
    
    $response->dump(); // Dump response
    $response->dumpHeaders();
    $response->dumpSession();
});
```

### Common Issues

#### Issue : PDF génération échoue
```
Solution : Vérifier extension GD activée dans php.ini
extension=gd
```

#### Issue : QR codes invalides
```
Solution : Vérifier expiration token (7 jours par défaut)
$token->expires_at > now()
```

#### Issue : Tests échouent aléatoirement
```
Solution : Utiliser RefreshDatabase trait + seed deterministe
use Illuminate\Foundation\Testing\RefreshDatabase;
```

---

## 📚 RESSOURCES UTILES

### Documentation officielle
- [Laravel 12](https://laravel.com/docs/12.x)
- [Livewire 4](https://livewire.laravel.com/docs/)
- [Pest](https://pestphp.com/docs)
- [Spatie Permission](https://spatie.be/docs/laravel-permission/v6)

### Outils recommandés
- **Laravel Pint** : Formatage automatique code
- **Laravel Telescope** : Debug en développement
- **Ray** : Debugging avancé (https://myray.app)
- **PHPStan** : Analyse statique (niveau 5+)

### Commandes utiles

```bash
# Formatage code
php artisan pint

# Clear caches
php artisan optimize:clear

# Rebuild assets
npm run build

# Queue worker
php artisan queue:work

# Seed data
php artisan db:seed --class=DemoDataSeeder
```

---

*Guide technique mis à jour le 26 janvier 2026*
