# RUN200 MANAGER - DOCUMENTATION COMPLÈTE
**Date de mise à jour** : 26 janvier 2026  
**Version** : 3.0  
**Statut** : ✅ Production Ready (Phases 0-7 complètes)

---

## 📊 RÉSUMÉ EXÉCUTIF

### Vue d'ensemble
**Run200 Manager** est une application web complète de gestion de courses automobiles développée avec Laravel 12. Elle couvre l'intégralité du workflow terrain depuis l'inscription des pilotes jusqu'à la publication du championnat.

### Métriques actuelles
- **Tests** : 393 tests / 912 assertions ✅
- **Architecture** : Clean Architecture avec séparation Domain/Application/Infrastructure
- **Modèles** : 17 modèles Eloquent
- **Use Cases** : 13 use cases métier
- **Composants Livewire** : 38 composants UI
- **Permissions** : 34 permissions granulaires
- **Rôles** : 6 rôles (Super Admin, Admin, Staff Admin, Staff Tech, Pilote, Invité)

### Fonctionnalités opérationnelles
✅ **Gestion des pilotes** : Profil complet avec licence unique, photo, permis de conduire  
✅ **Gestion des véhicules** : Numéro de course unique (0-999), catégories configurables  
✅ **Inscriptions aux courses** : Workflow complet avec validation et paiement (Stripe + Manuel)  
✅ **Checkpoints terrain** : 5 points de contrôle avec QR codes sécurisés  
✅ **Contrôle technique** : Inspection véhicule avec validation/refus et notes  
✅ **Validation administrative** : Double validation (admin + technique)  
✅ **E-carte pilote** : Carte digitale avec QR code pour scans terrain  
✅ **Import résultats CSV** : Import avec validation et matching automatique  
✅ **Publication résultats** : Affichage public des résultats par course  
✅ **Championnat** : Calcul automatique général + par catégorie avec barème configurable  
✅ **Fiche d'engagement** : Génération PDF avec signatures électroniques  

---

## 🏗️ ARCHITECTURE TECHNIQUE

### Stack technologique

#### Backend
- **Framework** : Laravel 12.0
- **PHP** : 8.2+
- **Base de données** : MySQL 8+ (SQLite pour dev/tests)
- **ORM** : Eloquent
- **Tests** : Pest 3.8
- **Code Quality** : Laravel Pint 1.24

#### Frontend
- **Framework UI** : Livewire 4.0
- **Composants** : Livewire Flux 2.9.0
- **CSS** : TailwindCSS 4.0.7
- **Build** : Vite

#### Packages principaux
- **Authentification** : Laravel Fortify 1.30
- **RBAC** : Spatie Laravel Permission 6.24
- **Audit** : Spatie Laravel Activity Log 4.10
- **PDF** : barryvdh/laravel-dompdf 3.1
- **Paiements** : Stripe PHP 19.2

### Architecture logicielle

Le projet suit une **Clean Architecture** stricte :

```
app/
├── Domain/               # Entités métier & règles
│   ├── Car/
│   │   ├── ValueObjects/
│   │   │   ├── RaceNumber.php
│   │   │   └── VehicleDetails.php
│   │   └── Exceptions/
│   ├── Championship/
│   │   ├── ValueObjects/
│   │   │   └── PointsRule.php
│   │   └── Services/
│   │       └── StandingsCalculator.php
│   ├── Payment/
│   │   ├── ValueObjects/
│   │   │   ├── Amount.php
│   │   │   └── PaymentMethod.php
│   │   └── Enums/
│   │       └── PaymentStatus.php
│   ├── Pilot/
│   │   ├── ValueObjects/
│   │   │   ├── LicenseNumber.php
│   │   │   └── PersonalInfo.php
│   │   └── Exceptions/
│   └── Registration/
│       ├── ValueObjects/
│       │   └── RegistrationStatus.php
│       └── Exceptions/
│
├── Application/          # Cas d'usage métier
│   ├── Championship/
│   │   └── UseCases/
│   │       └── RebuildSeasonStandings.php
│   ├── Payments/
│   │   └── UseCases/
│   │       ├── CreateStripeCheckout.php
│   │       ├── HandleStripeWebhook.php
│   │       ├── RecordManualPayment.php
│   │       └── RefundStripePayment.php
│   ├── Registrations/
│   │   └── UseCases/
│   │       ├── AssignPaddock.php
│   │       ├── RecordTechInspection.php
│   │       ├── ScanCheckpoint.php
│   │       ├── SubmitRegistration.php
│   │       ├── UpdateEngagementFormValidation.php
│   │       └── ValidateRegistration.php
│   └── Results/
│       └── UseCases/
│           ├── ImportRaceResults.php
│           └── PublishRaceResults.php
│
├── Infrastructure/       # Implémentations techniques
│   ├── Import/
│   │   └── CsvResultsParser.php
│   ├── Payments/
│   │   └── StripePaymentGateway.php
│   ├── Pdf/
│   │   ├── DriverCardPdfService.php
│   │   ├── EngagementFormPdfService.php
│   │   └── EngagedListPdfService.php
│   ├── Persistence/
│   │   └── EloquentResultRepository.php
│   └── Qr/
│       └── QrTokenService.php
│
├── Http/                 # Couche web
│   ├── Controllers/
│   │   └── Webhook/
│   │       └── StripeWebhookController.php
│   ├── Middleware/
│   │   └── EnsurePilotCanRegisterForRace.php
│   └── Requests/
│       └── (Form Requests)
│
├── Livewire/            # Composants UI
│   ├── Public/          # Pages publiques
│   │   ├── ChampionshipStandings.php
│   │   └── RaceCalendar.php
│   ├── Admin/           # Interface admin
│   │   ├── Championship.php
│   │   ├── Dashboard.php
│   │   ├── Races/
│   │   ├── Seasons/
│   │   └── Users/
│   ├── Pilot/           # Interface pilote
│   │   ├── Dashboard.php
│   │   ├── Cars/
│   │   ├── Profile/
│   │   ├── Races/
│   │   ├── Registrations/
│   │   └── RaceResults.php
│   └── Staff/           # Interface staff
│       ├── Pilots/
│       ├── Registrations/
│       ├── Results/
│       └── Scan/
│
├── Models/              # Modèles Eloquent
│   ├── User.php
│   ├── Pilot.php
│   ├── Car.php
│   ├── CarCategory.php
│   ├── Season.php
│   ├── Race.php
│   ├── RaceRegistration.php
│   ├── Payment.php
│   ├── Checkpoint.php
│   ├── CheckpointPassage.php
│   ├── TechInspection.php
│   ├── EngagementForm.php
│   ├── QrToken.php
│   ├── ResultImport.php
│   ├── RaceResult.php
│   ├── SeasonPointsRule.php
│   ├── SeasonStanding.php
│   └── SeasonCategoryStanding.php
│
├── Policies/            # Autorisations
│   ├── CarPolicy.php
│   ├── PilotPolicy.php
│   ├── RacePolicy.php
│   ├── RaceRegistrationPolicy.php
│   └── SeasonPolicy.php
│
└── Jobs/                # Jobs asynchrones
    └── RebuildSeasonStandingsJob.php
```

---

## 📊 MODÈLE DE DONNÉES

### Schéma relationnel complet

#### 1. Gestion des utilisateurs et pilotes

**users**
- id
- name
- email (unique)
- password
- email_verified_at
- two_factor_secret
- two_factor_recovery_codes
- two_factor_confirmed_at
- remember_token
- timestamps

**pilots**
- id
- user_id (FK → users, unique)
- license_number (unique, max 6 digits)
- first_name
- last_name
- birth_date
- birth_place
- address
- postal_code
- city
- country
- phone
- photo_path
- permit_number
- permit_date
- is_minor
- guardian_name
- guardian_license_number
- timestamps

#### 2. Gestion des véhicules

**car_categories**
- id
- name (unique)
- description
- sort_order
- timestamps

**cars**
- id
- pilot_id (FK → pilots)
- car_category_id (FK → car_categories)
- race_number (unique, 0-999)
- make
- model
- cylinders (nullable)
- fuel (nullable)
- drive (nullable)
- has_gas (boolean)
- timestamps
- deleted_at

#### 3. Gestion des saisons et courses

**seasons**
- id
- name
- year
- starts_at
- ends_at
- is_active
- timestamps

**season_points_rules**
- id
- season_id (FK → seasons, unique)
- points_scale (JSON : {1: 25, 2: 20, ...})
- min_races (default: 2)
- all_races_bonus (default: 20)
- timestamps

**races**
- id
- season_id (FK → seasons)
- name
- slug (unique)
- location
- starts_at
- ends_at
- registration_opens_at
- registration_closes_at
- max_registrations
- entry_fee (decimal)
- description
- timestamps

#### 4. Gestion des inscriptions

**race_registrations**
- id
- race_id (FK → races)
- pilot_id (FK → pilots)
- car_id (FK → cars)
- status (enum: PENDING, ACCEPTED, REJECTED, CANCELLED)
- car_race_number (denormalized pour sécurité)
- paddock_number (nullable)
- registered_at
- validated_at (nullable)
- validated_by (FK → users, nullable)
- validation_notes (nullable)
- timestamps

**payments**
- id
- race_registration_id (FK → race_registrations, unique)
- amount (decimal)
- currency (default: EUR)
- method (enum: MANUAL, STRIPE)
- status (enum: PENDING, COMPLETED, FAILED, REFUNDED)
- stripe_payment_intent_id (nullable, unique)
- stripe_checkout_session_id (nullable, unique)
- paid_at (nullable)
- refunded_at (nullable)
- notes (nullable)
- timestamps

#### 5. Système de checkpoints

**checkpoints**
- id
- code (unique : ADMIN_CHECK, TECH_CHECK, ENTRY, BRACELET, VALIDATION_INSCRIPTION)
- name
- description
- sort_order
- timestamps

**checkpoint_passages**
- id
- race_registration_id (FK → race_registrations)
- checkpoint_id (FK → checkpoints)
- scanned_by (FK → users)
- scanned_at
- device_info
- ip_address
- notes (nullable)
- timestamps

**tech_inspections**
- id
- race_registration_id (FK → race_registrations, unique)
- inspector_id (FK → users)
- status (enum: OK, REFUSED)
- inspected_at
- notes (nullable)
- timestamps

**engagement_forms**
- id
- race_registration_id (FK → race_registrations, unique)
- signature_data (longtext)
- pilot_name, pilot_license_number, pilot_birth_date, pilot_address, pilot_phone, pilot_email
- pilot_permit_number, pilot_permit_date
- car_make, car_model, car_category, car_cylinders, car_fuel, car_drive, car_has_gas, car_race_number
- race_name, race_date, race_location
- is_minor, guardian_name, guardian_license_number, guardian_signature_data
- witnessed_by (FK → users, nullable)
- tech_controller_name, tech_checked_at, tech_notes
- admin_validated_by (FK → users, nullable), admin_validated_at, admin_notes
- signed_at, ip_address, device_info
- timestamps

**qr_tokens**
- id
- race_registration_id (FK → race_registrations)
- token (unique, hashed)
- purpose (enum: ECARD, ENGAGEMENT)
- expires_at
- timestamps

#### 6. Résultats et championnat

**result_imports**
- id
- race_id (FK → races)
- imported_by (FK → users)
- file_name
- row_count
- success_count
- error_count
- errors (JSON)
- status (enum: SUCCESS, PARTIAL, FAILED)
- imported_at
- timestamps

**race_results**
- id
- race_id (FK → races)
- race_registration_id (FK → race_registrations, unique per race)
- position
- bib_number
- pilot_name
- car_make_model
- category_name
- time_raw
- time_ms
- published_at (nullable)
- timestamps

**season_standings**
- id
- season_id (FK → seasons)
- pilot_id (FK → pilots)
- total_points
- races_count
- best_position
- all_races_bonus (boolean)
- timestamps

**season_category_standings**
- id
- season_id (FK → seasons)
- car_category_id (FK → car_categories)
- pilot_id (FK → pilots)
- total_points
- races_count
- best_position
- all_races_bonus (boolean)
- timestamps

### Contraintes et index

#### Contraintes d'unicité
- pilots.license_number (unique)
- pilots.user_id (unique)
- cars.race_number (unique global, 0-999)
- car_categories.name (unique)
- races.slug (unique)
- payments.stripe_payment_intent_id (unique)
- checkpoints.code (unique)
- qr_tokens.token (unique)

#### Contraintes composites
- (race_id, pilot_id) sur race_registrations (1 pilote par course)
- (race_id, car_id) sur race_registrations (1 voiture par course)
- (race_id, race_registration_id) sur race_results (1 résultat par inscription)

#### Index de performance
- Toutes les foreign keys sont indexées
- season_standings : (season_id, total_points DESC)
- season_category_standings : (season_id, car_category_id, total_points DESC)
- race_results : (race_id, position)

---

## 🔐 SYSTÈME DE PERMISSIONS (RBAC)

### Rôles

| Rôle | Slug | Description |
|------|------|-------------|
| Super Admin | SUPER_ADMIN | Accès total système |
| Admin | ADMIN | Gestion complète courses et inscriptions |
| Staff Administratif | STAFF_ADMINISTRATIF | Validation inscriptions et scans admin |
| Staff Technique | STAFF_TECHNIQUE | Contrôle technique véhicules |
| Pilote | PILOTE | Gestion profil et inscriptions |
| Invité | GUEST | Consultation publique uniquement |

### Permissions (34 au total)

#### Pilotes (5)
- `pilots.manage` - Gérer tous les pilotes
- `pilots.view-any` - Voir liste pilotes
- `pilots.view` - Voir détail pilote
- `pilots.update-own` - Modifier son profil
- `pilots.delete` - Supprimer pilote

#### Voitures (5)
- `cars.manage` - Gérer toutes voitures
- `cars.view-any` - Voir liste voitures
- `cars.view` - Voir détail voiture
- `cars.manage-own` - Gérer ses voitures
- `cars.delete` - Supprimer voiture

#### Saisons (5)
- `seasons.manage` - Gérer saisons
- `seasons.view-any` - Voir liste saisons
- `seasons.view` - Voir détail saison
- `seasons.create` - Créer saison
- `seasons.delete` - Supprimer saison

#### Courses (5)
- `races.manage` - Gérer courses
- `races.view-any` - Voir liste courses
- `races.view` - Voir détail course
- `races.create` - Créer course
- `races.delete` - Supprimer course

#### Inscriptions (8)
- `registrations.manage` - Gérer toutes inscriptions
- `registrations.view-any` - Voir liste inscriptions
- `registrations.view` - Voir détail inscription
- `registrations.create-own` - Créer inscription
- `registrations.validate` - Valider inscription
- `registrations.assign-paddock` - Assigner emplacement
- `registrations.scan-checkpoints` - Scanner checkpoints
- `registrations.tech-inspection` - Contrôle technique

#### Résultats (3)
- `results.manage` - Gérer résultats
- `results.import` - Importer CSV
- `results.publish` - Publier résultats

#### Championnat (3)
- `championship.view` - Voir classement
- `championship.manage` - Gérer championnat
- `championship.rebuild` - Recalculer standings

---

## 🔄 WORKFLOW MÉTIER

### 1. Inscription d'un pilote

```
1. Création compte User (email/password)
   ↓
2. Complétion profil Pilot (licence, photo, permis)
   ↓
3. Ajout véhicule(s) avec numéro course unique
   ↓
4. Profil validé → peut s'inscrire aux courses
```

**Use Case** : Géré par Livewire (Pilot\Profile\Edit)  
**Validation** : LicenseNumber VO (max 6 digits, unique)  
**Contraintes** : race_number unique 0-999

### 2. Inscription à une course

```
1. Pilote sélectionne course ouverte
   ↓
2. Choix véhicule + génération fiche engagement
   ↓
3. Signature électronique fiche
   ↓
4. Paiement (Stripe ou Manuel)
   ↓
5. Status PENDING → attente validation admin
```

**Use Case** : `SubmitRegistration`  
**Livewire** : `Pilot\Registrations\Create`  
**PDF** : `EngagementFormPdfService`  
**Contraintes** :
- 1 pilote par course
- 1 voiture par course
- Inscription entre registration_opens_at et registration_closes_at

### 3. Validation administrative

```
1. Staff admin consulte inscriptions PENDING
   ↓
2. Vérifie documents et informations
   ↓
3. ACCEPTE ou REFUSE avec notes
   ↓
4. Si ACCEPTE → Status ACCEPTED
   ↓
5. Génération QR code e-carte pilote
```

**Use Case** : `ValidateRegistration`  
**Livewire** : `Staff\Registrations\Validate`  
**Checkpoint** : Scan VALIDATION_INSCRIPTION  
**Permissions** : `registrations.validate`

### 4. Checkpoints terrain (jour de course)

#### Checkpoint 1 : Validation administrative (ADMIN_CHECK)
```
Staff scanne QR e-carte
   ↓
Vérification identité/documents
   ↓
Mise à jour engagement form (admin_validated_at)
   ↓
Status ACCEPTED → ADMIN_CHECKED
```

#### Checkpoint 2 : Contrôle technique (TECH_CHECK)
```
Staff technique scanne QR
   ↓
Inspection véhicule
   ↓
Validation OK ou REFUSED
   ↓
Mise à jour engagement form (tech_checked_at)
   ↓
Status ADMIN_CHECKED → TECH_CHECKED_OK
```

**Use Case** : `RecordTechInspection`, `ScanCheckpoint`, `UpdateEngagementFormValidation`  
**Livewire** : `Staff\Scan\Scanner`, `Staff\Registrations\TechInspection`  
**Permissions** : `registrations.scan-checkpoints`, `registrations.tech-inspection`

#### Checkpoint 3 : Entrée (ENTRY)
```
Scan QR entrée paddock
   ↓
Status TECH_CHECKED_OK → ENTRY_SCANNED
```

#### Checkpoint 4 : Bracelet (BRACELET)
```
Remise bracelet pilote
   ↓
Status ENTRY_SCANNED → BRACELET_GIVEN
```

### 5. Import et publication des résultats

```
1. Admin importe CSV résultats
   ↓
2. CsvResultsParser valide format
   ↓
3. Matching bib → race_registration_id
   ↓
4. Stockage race_results avec erreurs si présentes
   ↓
5. Publication (published_at renseigné)
   ↓
6. Affichage public + trigger recalcul championnat
```

**Use Case** : `ImportRaceResults`, `PublishRaceResults`  
**Livewire** : `Staff\Results\Import`  
**Infrastructure** : `CsvResultsParser`  
**Permissions** : `results.import`, `results.publish`

**Format CSV attendu** :
```csv
position,bib,pilote,voiture,catégorie,temps
1,263,Sergio PEREZ JR,Mercedes-Amg Sagaris,ÉLECTRIQUE/HYBRIDE,01:23.456
```

### 6. Calcul du championnat

```
1. Publication résultats course
   ↓
2. Job RebuildSeasonStandingsJob dispatché
   ↓
3. StandingsCalculator calcule points
   ↓
4. Mise à jour season_standings (général)
   ↓
5. Mise à jour season_category_standings (par catégorie)
   ↓
6. Affichage public classements
```

**Use Case** : `RebuildSeasonStandings`  
**Service** : `StandingsCalculator`  
**Job** : `RebuildSeasonStandingsJob`  
**Livewire** : `Public\ChampionshipStandings`, `Admin\Championship`

**Règles de calcul** :
- Barème : 1er=25pts, 2e=20pts, 3e=16pts, 4e=14pts, 5e=10pts, 6e=8pts, autres=5pts
- Min 2 courses pour être classé
- Bonus +20pts si participation à toutes courses saison
- Classement par total points DESC

---

## 🧪 TESTS

### Organisation des tests

```
tests/
├── Feature/                          # Tests d'intégration
│   ├── Auth/
│   │   ├── LoginTest.php
│   │   └── RegistrationTest.php
│   ├── Car/
│   │   ├── CarCategoryTest.php
│   │   └── CarManagementTest.php
│   ├── Championship/
│   │   ├── SeasonManagementTest.php
│   │   └── StandingsCalculationTest.php
│   ├── Checkpoint/
│   │   └── CheckpointScanTest.php
│   ├── EngagementFormValidationTest.php
│   ├── Payment/
│   │   └── PaymentProcessingTest.php
│   ├── Pilot/
│   │   └── PilotManagementTest.php
│   ├── Rbac/
│   │   └── RolePermissionTest.php
│   ├── Registration/
│   │   ├── RegistrationFlowTest.php
│   │   └── ValidationTest.php
│   └── Result/
│       └── ResultImportTest.php
│
└── Unit/                            # Tests unitaires
    ├── ValueObject/
    │   ├── AmountTest.php
    │   ├── LicenseNumberTest.php
    │   ├── PointsRuleTest.php
    │   └── RaceNumberTest.php
    └── Service/
        ├── CsvResultsParserTest.php
        ├── QrTokenServiceTest.php
        └── StandingsCalculatorTest.php
```

### Couverture des tests

**Résultats actuels** : 393 tests / 912 assertions ✅

#### Par fonctionnalité
- ✅ Authentification : 4 tests
- ✅ RBAC (Rôles/Permissions) : 12 tests
- ✅ Pilotes : 24 tests
- ✅ Voitures : 18 tests
- ✅ Catégories : 8 tests
- ✅ Saisons : 15 tests
- ✅ Courses : 22 tests
- ✅ Inscriptions : 45 tests
- ✅ Paiements : 28 tests
- ✅ Checkpoints : 32 tests
- ✅ Contrôle technique : 18 tests
- ✅ Fiche engagement : 25 tests
- ✅ Import résultats : 38 tests
- ✅ Publication résultats : 12 tests
- ✅ Championnat : 42 tests
- ✅ ValueObjects : 28 tests
- ✅ Services : 22 tests

#### Commandes de test

```bash
# Tous les tests
php artisan test

# Tests spécifiques
php artisan test --filter=Championship
php artisan test --filter=Registration
php artisan test --filter=Rbac

# Avec output détaillé
php artisan test --parallel

# Mode compact
php artisan test --compact

# Coverage (nécessite Xdebug)
php artisan test --coverage
php artisan test --coverage-html=coverage
```

---

## 📱 INTERFACES UTILISATEUR

### Routes publiques
- `GET /` - Page d'accueil
- `GET /public/calendrier` - Calendrier courses
- `GET /public/classement` - Classement championnat

### Interface Pilote (`/pilot/*`)
- `GET /pilot/dashboard` - Tableau de bord
- `GET /pilot/profile` - Profil pilote
- `GET /pilot/profile/edit` - Édition profil
- `GET /pilot/cars` - Mes véhicules
- `GET /pilot/cars/create` - Ajouter véhicule
- `GET /pilot/cars/{car}/edit` - Modifier véhicule
- `GET /pilot/races` - Courses disponibles
- `GET /pilot/registrations` - Mes inscriptions
- `GET /pilot/registrations/create/{race}` - Nouvelle inscription
- `GET /pilot/registrations/{registration}/payment` - Paiement
- `GET /pilot/registrations/{registration}/ecard` - E-carte
- `GET /pilot/results` - Mes résultats

### Interface Staff Admin (`/staff/*`)
- `GET /staff/dashboard` - Tableau de bord staff
- `GET /staff/pilots` - Gestion pilotes
- `GET /staff/pilots/create` - Créer pilote
- `GET /staff/pilots/{pilot}/edit` - Modifier pilote
- `GET /staff/registrations` - Gestion inscriptions
- `GET /staff/registrations/validate` - Valider inscriptions
- `GET /staff/registrations/engagement-sign` - Fiches engagement
- `GET /staff/registrations/tech-inspection` - Contrôle technique
- `GET /staff/scan` - Scanner QR codes
- `GET /staff/results` - Gestion résultats
- `GET /staff/results/import` - Import CSV

### Interface Admin (`/admin/*`)
- `GET /admin/dashboard` - Tableau de bord admin
- `GET /admin/users` - Gestion utilisateurs
- `GET /admin/seasons` - Gestion saisons
- `GET /admin/seasons/create` - Créer saison
- `GET /admin/seasons/{season}/edit` - Modifier saison
- `GET /admin/races` - Gestion courses
- `GET /admin/races/create` - Créer course
- `GET /admin/races/{race}/edit` - Modifier course
- `GET /admin/championship` - Gestion championnat

### Webhooks
- `POST /stripe/webhook` - Webhook Stripe (payment_intent.succeeded, etc.)

---

## 🔧 COMMANDES ARTISAN CUSTOM

### Commandes de migration de données

```bash
# Migrer les validations existantes dans engagement_forms
php artisan engagement:migrate-validations

# Corriger les dates de contrôle technique
php artisan fix:engagement-tech

# Vérifier l'état des validations
php artisan engagement:check-validations
```

### Commandes de maintenance championnat

```bash
# Recalculer tous les standings d'une saison
php artisan championship:rebuild {season_id}

# Recalculer via Job (asynchrone)
php artisan queue:work
```

### Commandes de développement

```bash
# Seed avec données demo
php artisan migrate:fresh --seed

# Build assets
npm run build

# Mode dev avec hot reload
npm run dev

# Formatage code (Pint)
php artisan pint

# Clear caches
php artisan optimize:clear
```

---

## 🔒 SÉCURITÉ

### Authentification
- **Laravel Fortify** : login, registration, password reset, 2FA
- **Sessions** : cookie secure, httponly, samesite=lax
- **CSRF** : protection automatique Laravel

### Autorisations
- **Policies** : CarPolicy, PilotPolicy, RacePolicy, RaceRegistrationPolicy, SeasonPolicy
- **Middleware** : auth, role:PILOTE, permission:registrations.validate
- **Gates** : vérification granulaire via Spatie Permission

### Audit Trail
- **Spatie Activity Log** : tracking automatique sur modèles sensibles
- **Eloquent Observers** : log des modifications pilotes, inscriptions, paiements
- **Checkpoint passages** : IP, device_info, user_id, timestamp

### QR Codes sécurisés
- **Token unique** : SHA256 hash
- **Expiration** : 7 jours après génération
- **Purpose** : ECARD ou ENGAGEMENT (pas d'interchangeabilité)
- **Validation** : vérification token + registration status + expiration

### Paiements Stripe
- **Webhook signature** : validation Stripe-Signature header
- **Idempotence** : stripe_payment_intent_id unique
- **Status tracking** : PENDING → COMPLETED ou FAILED
- **Refunds** : gestion via RefundStripePayment use case

---

## 📦 DÉPLOIEMENT

### Prérequis serveur
- PHP 8.2+ (avec extensions : bcmath, ctype, curl, dom, fileinfo, gd, json, mbstring, openssl, pcre, pdo, tokenizer, xml)
- MySQL 8+
- Composer 2+
- Node.js 18+ / NPM
- Supervisor (pour queue worker)
- SSL certificat (Let's Encrypt recommandé)

### Variables d'environnement (.env)

```env
APP_NAME="Run200 Manager"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://run200.example.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=run200_manager
DB_USERNAME=run200_user
DB_PASSWORD=***

STRIPE_KEY=pk_live_***
STRIPE_SECRET=sk_live_***
STRIPE_WEBHOOK_SECRET=whsec_***

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=***
MAIL_PASSWORD=***
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@run200.example.com
MAIL_FROM_NAME="${APP_NAME}"

QUEUE_CONNECTION=database
```

### Étapes de déploiement

```bash
# 1. Clone du repo
git clone https://github.com/your-org/run200-manager.git
cd run200-manager

# 2. Installation dépendances
composer install --no-dev --optimize-autoloader
npm ci
npm run build

# 3. Configuration
cp .env.example .env
php artisan key:generate
# Éditer .env avec vraies valeurs

# 4. Migrations
php artisan migrate --force
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=CarCategorySeeder
php artisan db:seed --class=CheckpointSeeder

# 5. Optimisations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Permissions fichiers
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# 7. Queue worker (Supervisor)
php artisan queue:work --daemon
```

### Configuration Supervisor

```ini
[program:run200-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/run200/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/run200/storage/logs/worker.log
```

### Configuration Nginx

```nginx
server {
    listen 80;
    server_name run200.example.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name run200.example.com;
    root /var/www/run200/public;

    ssl_certificate /etc/letsencrypt/live/run200.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/run200.example.com/privkey.pem;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 📈 MONITORING & MAINTENANCE

### Logs à surveiller

```bash
# Logs Laravel
tail -f storage/logs/laravel.log

# Logs queue worker
tail -f storage/logs/worker.log

# Logs Nginx
tail -f /var/log/nginx/run200-access.log
tail -f /var/log/nginx/run200-error.log
```

### Métriques importantes
- Taux de succès des imports CSV
- Temps de réponse API Stripe
- Durée calcul standings (doit rester < 5s)
- Nombre de scans QR par checkpoint
- Taux de validation des inscriptions

### Tâches planifiées (Cron)

```bash
# Scheduler Laravel
* * * * * cd /var/www/run200 && php artisan schedule:run >> /dev/null 2>&1
```

**Tâches configurées** :
- Expiration des QR tokens (daily)
- Cleanup des imports échoués > 30j (weekly)
- Backup DB (daily 2AM)

### Backup

```bash
# Backup DB
php artisan db:backup

# Backup fichiers uploads
tar -czf run200-storage-$(date +%Y%m%d).tar.gz storage/app/public
```

---

## 📚 RESSOURCES

### Documentation officielle
- [Laravel 12](https://laravel.com/docs/12.x)
- [Livewire 4](https://livewire.laravel.com/docs/)
- [Livewire Flux](https://fluxui.dev)
- [Spatie Permission](https://spatie.be/docs/laravel-permission/v6)
- [Stripe PHP](https://stripe.com/docs/api?lang=php)

### Fichiers de référence projet
- `docs/information_projet.md` - Spécifications métier complètes
- `docs/bonne_pratique.md` - Standards de code
- `docs/etat_et_plan_developpement.md` - Historique des sprints

### Support
- **Issues** : GitHub Issues
- **Email** : dev@run200.example.com

---

*Document généré le 26 janvier 2026 - Version 3.0*
