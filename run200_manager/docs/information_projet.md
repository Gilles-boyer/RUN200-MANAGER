# RUN200 MANAGER
## Document de Référence Global – Métier, Architecture, BDD, Plan, Use Cases, Prompts Agent IA

**Organisation** : ASA CFG  
**Projet** : Run200 Manager  
**Stack** : Laravel 12 / Livewire 3 + Volt / TailwindCSS / MySQL  
**Objectif** : Dématérialisation complète du workflow Run200 (inscriptions → checks → résultats → championnat)

---

# 0. RÉSUMÉ EXÉCUTIF

Run200 Manager est une web app “terrain” (mobile-first) pour gérer :
- le compte pilote (profil + voitures)
- l’inscription à une course (validation licence/paiement)
- les checkpoints (administratif, technique, entrée, bracelet) via QR
- l’import CSV des résultats + publication
- le championnat (général + par catégorie) selon barème + bonus

Contraintes métier confirmées :
- **MySQL**
- **Licence pilote unique** (digits, max 6)
- **Numéro voiture unique à vie** (0–999)
- **Catégories véhicules modifiables par admin**
- Championnat : min 2 courses, bonus +20 si toutes courses, points 25/20/16/14/10/8/5

---

# 1. CONTRAINTES MÉTIER & RÈGLES

## 1.1 Pilotes
Champs obligatoires :
- Nom, prénom
- **Licence** : numérique ≤ 6 chiffres, **unique**
- Date & lieu de naissance
- Adresse
- Téléphone
- Email (via user)
- Photo (upload)

Mineur :
- Tuteur : nom/prénom
- Licence tuteur : numérique ≤ 6 chiffres  
**Pas de document requis**.

## 1.2 Voitures
Un pilote peut déclarer 1..N voitures.
Champs :
- **race_number** : entier **0..999**, **unique à vie**
- marque, modèle
- catégorie (référentiel modifiable)

## 1.3 Inscriptions course
- 1 pilote = 1 inscription par course
- 1 voiture = 1 inscription par course
- Paiement :
  - P0 : manuel (enregistrement + validation staff)
  - P1 : Stripe

## 1.4 Checkpoints (scans QR)
Checkpoints confirmés :
1. **Validation inscription** (administratif : décision accept/refuse)
2. **Pointage vérification administrative**
3. **Pointage vérification technique**
4. **Pointage entrée pilote et voiture**
5. **Pointage récupération bracelet pilote**

Chaque scan :
- est fait par un staff (auth)
- crée un passage horodaté
- fait évoluer le statut si transition autorisée
- est audité

## 1.5 Résultats (CSV)
Colonnes attendues :
- position
- bib
- pilote
- voiture
- catégorie
- temps

Règles :
- bib doit correspondre au **numéro voiture** (hypothèse recommandée ; à verrouiller si bib ≠ race_number)
- bib unique par course
- temps parsé et stocké en ms
- import historisé, erreurs stockées, publication impossible si import invalide

## 1.6 Championnat (saison)
- Points :
  - 1er 25, 2e 20, 3e 16, 4e 14, 5e 10, 6e 8, autres 5
- Pour être classé : **min 2 courses**
- Bonus : **+20** si participation à **toutes les courses** de la saison
- Classement :
  - général
  - par catégorie

---

# 2. STACK TECHNIQUE

## 2.1 Backend
- Laravel 12
- PHP 8.3+
- MySQL 8+ (InnoDB, utf8mb4, strict)

## 2.2 Frontend
- Livewire 3 + Volt
- TailwindCSS
- UI maison optimisée “terrain”

## 2.3 Packages
- Auth : laravel/breeze (Livewire)
- RBAC : spatie/laravel-permission
- Audit : spatie/laravel-activitylog
- QR : simplesoftwareio/simple-qrcode
- PDF : barryvdh/laravel-dompdf
- Import CSV : maatwebsite/excel
- Queue : Redis recommandé
- Paiements : stripe/stripe-php (P1)

---

# 3. ARCHITECTURE LOGICIELLE (Clean)

## 3.1 Structure recommandée
app/
Domain/
Registration/
Enums/RegistrationStatus.php
Rules/RegistrationTransitions.php
Pilot/
ValueObjects/LicenseNumber.php
Car/
ValueObjects/RaceNumber.php
Championship/
Rules/PointsTable.php
Rules/StandingsRules.php

Application/
Registrations/UseCases/
SubmitRegistration.php
ValidateRegistration.php
AssignPaddock.php
ScanCheckpoint.php
Results/UseCases/
ImportRaceResults.php
PublishRaceResults.php
Championship/UseCases/
RebuildSeasonStandings.php

Infrastructure/
Qr/QrTokenService.php
Import/ResultsCsvImporter.php
Payments/Stripe/*
Persistence/Eloquent/*
Http/
Controllers/
Requests/
Policies/

pgsql
Copier le code

## 3.2 Principes
- UI (Volt/Livewire) = rendu + saisie
- Use Cases = logique applicative transactionnelle (DB::transaction)
- Domain = règles (transitions, invariants)
- Infrastructure = Eloquent, import CSV, QR tokens, providers externes
- Chaque action staff = audit (activitylog)

---

# 4. RBAC (Rôles / Permissions)

## 4.1 Rôles
- PILOTE
- STAFF_ADMINISTRATIF
- CONTROLEUR_TECHNIQUE
- STAFF_ENTREE
- STAFF_SONO
- ADMIN

## 4.2 Permissions (exemples stables)
- pilot.manage_own_profile
- car.manage_own
- race.view_open
- race.manage
- race_registration.create
- race_registration.view_own
- race_registration.view_all
- race_registration.validate
- race_registration.assign_paddock
- checkpoint.scan.admin_check
- checkpoint.scan.tech_check
- checkpoint.scan.entry
- checkpoint.scan.bracelet
- tech_inspection.manage
- results.import
- results.publish
- championship.view
- championship.manage_rules
- audit.view

---

# 5. WORKFLOW STATUTS (INSCRIPTION)

## 5.1 Statuts
SUBMITTED
PENDING_VALIDATION
ACCEPTED
REFUSED
ADMIN_CHECKED
TECH_CHECKED_OK
TECH_CHECKED_FAIL
ENTRY_SCANNED
BRACELET_GIVEN
RESULTS_IMPORTED
PUBLISHED

yaml
Copier le code

## 5.2 Transitions autorisées
- SUBMITTED → PENDING_VALIDATION (système ou à la création)
- PENDING_VALIDATION → ACCEPTED | REFUSED (STAFF_ADMINISTRATIF, ADMIN)
- ACCEPTED → ADMIN_CHECKED (scan administratif)
- ADMIN_CHECKED → TECH_CHECKED_OK | TECH_CHECKED_FAIL (contrôle technique)
- TECH_CHECKED_OK → ENTRY_SCANNED (scan entrée)
- ENTRY_SCANNED → BRACELET_GIVEN (scan bracelet)
- BRACELET_GIVEN → RESULTS_IMPORTED (après import)
- RESULTS_IMPORTED → PUBLISHED (publication)

---

# 6. ARCHITECTURE BASE DE DONNÉES (MySQL)

## 6.1 ERD (schéma texte)
users 1──1 pilots 1──N cars N──1 car_categories
│
├──N race_registrations N──1 races N──1 seasons
│ │
│ ├──1 qr_tokens
│ ├──N checkpoint_passages N──1 checkpoints
│ ├──1 tech_inspections
│ └──N payments
│
races 1──N result_imports
races 1──N race_results N──1 race_registrations
seasons 1──N season_points_rules
seasons 1──N season_standings (general)
seasons 1──N season_category_standings (par catégorie)

diff
Copier le code

## 6.2 Tables (détaillées) + contraintes + indexes

### 6.2.1 users (Laravel standard)
- id BIGINT PK
- email VARCHAR(255) UNIQUE
- password, remember_token, timestamps

### 6.2.2 pilots
- id BIGINT PK
- user_id BIGINT FK(users) UNIQUE
- first_name VARCHAR(100)
- last_name VARCHAR(100)
- birth_date DATE
- birth_place VARCHAR(150)
- **license_number VARCHAR(6) NOT NULL UNIQUE** (digits only, validation app)
- phone VARCHAR(30)
- address_line1 VARCHAR(255)
- address_line2 VARCHAR(255) NULL
- city VARCHAR(100)
- postal_code VARCHAR(20)
- photo_path VARCHAR(255) NULL
- is_minor TINYINT(1) NOT NULL DEFAULT 0
- guardian_first_name VARCHAR(100) NULL
- guardian_last_name VARCHAR(100) NULL
- guardian_license_number VARCHAR(6) NULL
- timestamps

Indexes :
- UNIQUE(user_id)
- UNIQUE(license_number)

### 6.2.3 car_categories
- id BIGINT PK
- name VARCHAR(150) UNIQUE
- is_active TINYINT(1) DEFAULT 1
- sort_order INT DEFAULT 0
- timestamps

### 6.2.4 cars
- id BIGINT PK
- pilot_id BIGINT FK(pilots)
- car_category_id BIGINT FK(car_categories)
- **race_number SMALLINT NOT NULL UNIQUE** (0..999)
- make VARCHAR(100)
- model VARCHAR(100)
- notes TEXT NULL
- timestamps

Indexes :
- UNIQUE(race_number)
- INDEX(pilot_id)
- INDEX(car_category_id)

### 6.2.5 seasons
- id BIGINT PK
- year INT UNIQUE
- name VARCHAR(150)
- is_active TINYINT(1) DEFAULT 0
- timestamps

### 6.2.6 races
- id BIGINT PK
- season_id BIGINT FK(seasons)
- name VARCHAR(150)
- race_date DATE
- status VARCHAR(30) (DRAFT/OPEN/CLOSED/RUNNING/RESULTS_READY/PUBLISHED/ARCHIVED)
- location VARCHAR(150) NULL
- timestamps

Indexes :
- INDEX(season_id)
- INDEX(season_id, race_date)

### 6.2.7 race_registrations
- id BIGINT PK
- race_id BIGINT FK(races)
- pilot_id BIGINT FK(pilots)
- car_id BIGINT FK(cars)
- status VARCHAR(30) NOT NULL (RegistrationStatus)
- paddock_slot VARCHAR(20) NULL (ex “10”)
- refused_reason TEXT NULL
- accepted_at DATETIME NULL
- admin_checked_at DATETIME NULL
- tech_checked_at DATETIME NULL
- entry_scanned_at DATETIME NULL
- bracelet_given_at DATETIME NULL
- timestamps

Contraintes :
- UNIQUE(race_id, pilot_id)
- UNIQUE(race_id, car_id)

Indexes :
- INDEX(race_id)
- INDEX(pilot_id)
- INDEX(car_id)
- INDEX(status)

### 6.2.8 payments (P0 manuel + P1 Stripe)
- id BIGINT PK
- race_registration_id BIGINT FK(race_registrations)
- method VARCHAR(20) (MANUAL/STRIPE)
- status VARCHAR(20) (PENDING/PAID/FAILED/REFUNDED)
- amount_cents INT NOT NULL DEFAULT 0
- currency CHAR(3) NOT NULL DEFAULT 'EUR'
- paid_at DATETIME NULL
- provider_ref VARCHAR(255) NULL (Stripe session/payment id)
- created_by_user_id BIGINT FK(users) NULL
- timestamps

Indexes :
- INDEX(race_registration_id)
- INDEX(method, status)

### 6.2.9 checkpoints
- id BIGINT PK
- code VARCHAR(50) UNIQUE
- name VARCHAR(150)
- required_permission VARCHAR(100) NULL (ex checkpoint.scan.entry)
- sort_order INT DEFAULT 0
- timestamps

Codes recommandés :
- ADMIN_CHECK
- TECH_CHECK_OK
- TECH_CHECK_FAIL
- ENTRY
- BRACELET

### 6.2.10 qr_tokens
- id BIGINT PK
- race_registration_id BIGINT FK(race_registrations) UNIQUE
- token_hash CHAR(64) UNIQUE
- expires_at DATETIME NULL
- created_at DATETIME

Indexes :
- UNIQUE(race_registration_id)
- UNIQUE(token_hash)

### 6.2.11 checkpoint_passages
- id BIGINT PK
- race_registration_id BIGINT FK(race_registrations)
- checkpoint_id BIGINT FK(checkpoints)
- scanned_by_user_id BIGINT FK(users)
- scanned_at DATETIME
- meta_json JSON NULL
- timestamps (optionnel)

Contraintes :
- UNIQUE(race_registration_id, checkpoint_id) (1 scan max / étape)

Indexes :
- INDEX(scanned_by_user_id)
- INDEX(scanned_at)

### 6.2.12 tech_inspections
- id BIGINT PK
- race_registration_id BIGINT FK(race_registrations) UNIQUE
- status VARCHAR(10) (OK/FAIL)
- notes TEXT NULL
- inspected_by_user_id BIGINT FK(users)
- inspected_at DATETIME
- timestamps

### 6.2.13 result_imports
- id BIGINT PK
- race_id BIGINT FK(races)
- uploaded_by_user_id BIGINT FK(users)
- original_filename VARCHAR(255)
- stored_path VARCHAR(255)
- row_count INT DEFAULT 0
- status VARCHAR(20) (IMPORTED/FAILED)
- errors_json JSON NULL
- created_at DATETIME

Indexes :
- INDEX(race_id)
- INDEX(uploaded_by_user_id)

### 6.2.14 race_results
- id BIGINT PK
- race_id BIGINT FK(races)
- race_registration_id BIGINT FK(race_registrations) NULL (devrait être NOT NULL si matching strict)
- position INT NOT NULL
- bib INT NOT NULL
- raw_time VARCHAR(50) NOT NULL
- time_ms INT NOT NULL
- category_snapshot VARCHAR(150) NOT NULL
- timestamps

Contraintes :
- UNIQUE(race_id, bib)
- (optionnel) UNIQUE(race_id, position)

Indexes :
- INDEX(race_id, position)

### 6.2.15 season_points_rules
- id BIGINT PK
- season_id BIGINT FK(seasons)
- position_from INT NOT NULL
- position_to INT NOT NULL
- points INT NOT NULL
- timestamps

Exemple :
- 1..1 => 25
- 2..2 => 20
- ...
- 7..9999 => 5

### 6.2.16 season_standings (général)
- id BIGINT PK
- season_id BIGINT FK(seasons)
- pilot_id BIGINT FK(pilots)
- races_count INT NOT NULL DEFAULT 0
- base_points INT NOT NULL DEFAULT 0
- bonus_points INT NOT NULL DEFAULT 0
- total_points INT NOT NULL DEFAULT 0
- rank INT NULL
- computed_at DATETIME
- UNIQUE(season_id, pilot_id)

### 6.2.17 season_category_standings (par catégorie)
- id BIGINT PK
- season_id BIGINT FK(seasons)
- car_category_id BIGINT FK(car_categories)
- pilot_id BIGINT FK(pilots)
- races_count INT DEFAULT 0
- base_points INT DEFAULT 0
- bonus_points INT DEFAULT 0
- total_points INT DEFAULT 0
- rank INT NULL
- computed_at DATETIME
- UNIQUE(season_id, car_category_id, pilot_id)

---

# 7. ROUTES & ENDPOINTS (Synthèse P0)

## 7.1 Web (Volt)
- /pilot/* (profil, voitures, courses, inscriptions, e-carte QR, résultats)
- /staff/* (courses, inscriptions, validation, scan par checkpoint)
- /admin/* (saisons, courses, catégories, import résultats, publication, championnat)

## 7.2 Interne (POST)
- POST /internal/scan
- POST /internal/registrations/{reg}/accept
- POST /internal/registrations/{reg}/refuse
- POST /internal/registrations/{reg}/paddock
- POST /internal/registrations/{reg}/tech/ok
- POST /internal/registrations/{reg}/tech/fail
- POST /internal/races/{race}/results/import
- POST /internal/races/{race}/results/publish

---

# 8. USE CASES (COMPLETS)

> Format standard : **But**, **Acteurs**, **Entrées**, **Préconditions**, **Étapes**, **Règles**, **Sorties**, **Audit**, **Erreurs**

## 8.1 UC-01 Créer/Mettre à jour profil pilote
**But** : gérer les données du pilote (et tuteur si mineur).  
**Acteur** : PILOTE (ou ADMIN).  
**Entrées** : identité, licence, naissance, adresse, photo, is_minor, tuteur.*  
**Préconditions** : user authentifié.  
**Étapes** :
1. Valider champs (FormRequest).
2. Vérifier licence : digits only, max 6, unique.
3. Si is_minor = true :
   - tuteur nom/prénom obligatoires
   - licence tuteur digits max 6
4. Sauver pilote.
5. Sauver photo via Storage (validation MIME/size).
**Règles** :
- license_number unique
- photo : image only, taille max (ex 2MB)
**Sorties** : pilote mis à jour.
**Audit** : optionnel (pilot.profile.updated) (P1).
**Erreurs** : validation, unique constraint.

## 8.2 UC-02 CRUD Voiture (pilote)
**But** : ajouter/éditer/supprimer une voiture.  
**Acteur** : PILOTE.  
**Entrées** : race_number, make, model, car_category_id.  
**Préconditions** : pilote existe.  
**Étapes** :
1. Valider race_number int 0..999.
2. Vérifier unicité race_number (unique DB).
3. Vérifier catégorie active.
4. Persist.
**Règles** : race_number unique à vie.
**Sorties** : car créée/maj.
**Audit** : optionnel.
**Erreurs** : collision race_number.

## 8.3 UC-03 Créer Course (staff/admin)
**But** : créer une course dans une saison.  
**Acteur** : ADMIN (ou STAFF autorisé).  
**Entrées** : season_id, name, race_date, location.  
**Préconditions** : saison existe.  
**Étapes** :
1. Valider date.
2. Créer course status DRAFT.
3. Ouvrir course (status OPEN) via action dédiée.
**Sorties** : course créée.
**Audit** : race.created / race.opened.

## 8.4 UC-04 Soumettre Inscription à une course
**But** : un pilote s’inscrit à une course avec une voiture.  
**Acteur** : PILOTE.  
**Entrées** : race_id, car_id.  
**Préconditions** :
- course OPEN
- voiture appartient au pilote
- pas déjà inscrit (unique constraints)
**Étapes** (transaction) :
1. Vérifier course OPEN.
2. Vérifier car appartient au pilote.
3. Créer race_registration :
   - status = PENDING_VALIDATION
4. Générer QR token (hash stocké).
5. Audit : registration.submitted
**Sorties** : inscription créée + token généré.
**Erreurs** :
- course pas ouverte
- voiture déjà engagée
- pilote déjà inscrit

## 8.5 UC-05 Valider inscription (ACCEPT / REFUSE)
**But** : vérification licence + paiement (manuel P0) et décision.  
**Acteur** : STAFF_ADMINISTRATIF, ADMIN.  
**Entrées** : registration_id, decision, refused_reason (si refuse), payment_status (manuel).  
**Préconditions** : status = PENDING_VALIDATION.  
**Étapes** (transaction) :
1. Vérifier permission race_registration.validate.
2. Vérifier transition autorisée.
3. Si ACCEPT :
   - status = ACCEPTED
   - accepted_at = now
4. Si REFUSE :
   - refused_reason obligatoire
   - status = REFUSED
5. Créer/mettre à jour Payment (MANUAL) si nécessaire.
6. Audit : registration.accepted / registration.refused
**Sorties** : inscription mise à jour.
**Erreurs** : transition invalide, reason manquant.

## 8.6 UC-06 Affecter paddock
**But** : attribuer un emplacement paddock simple (ex “10”).  
**Acteur** : STAFF_ADMINISTRATIF.  
**Entrées** : registration_id, paddock_slot.  
**Préconditions** : inscription existe (recommandé : ACCEPTED minimum).  
**Étapes** :
1. Valider paddock_slot (string courte).
2. Persist.
3. Audit : paddock.assigned
**Sorties** : paddock_slot mis à jour.

## 8.7 UC-07 Scanner checkpoint (QR)
**But** : scan QR à une étape terrain, création passage + update statut.  
**Acteur** : STAFF (selon checkpoint), ADMIN.  
**Entrées** : token, checkpoint_code.  
**Préconditions** :
- token valide (hash match)
- permission requise pour le checkpoint
- transition status autorisée
- pas déjà scanné ce checkpoint (unique)
**Étapes** (transaction) :
1. Résoudre registration via token hash.
2. Charger checkpoint.
3. Vérifier permission requise.
4. Déterminer status cible selon checkpoint_code :
   - ADMIN_CHECK -> ADMIN_CHECKED
   - TECH_CHECK_OK -> TECH_CHECKED_OK
   - TECH_CHECK_FAIL -> TECH_CHECKED_FAIL
   - ENTRY -> ENTRY_SCANNED
   - BRACELET -> BRACELET_GIVEN
5. Vérifier transition Domain.
6. Créer checkpoint_passage (unique).
7. Mettre à jour registration.status + *_at correspondant.
8. Audit : checkpoint.scanned (withProperties from/to, checkpoint)
**Sorties** : registration mise à jour.
**Erreurs** :
- token invalide/expiré
- permission manquante
- transition interdite
- déjà scanné

## 8.8 UC-08 Contrôle technique (OK/FAIL + notes)
**But** : enregistrer inspection + faire évoluer le statut.  
**Acteur** : CONTROLEUR_TECHNIQUE, ADMIN.  
**Entrées** : registration_id, status OK/FAIL, notes (obligatoire si FAIL).  
**Préconditions** : status = ADMIN_CHECKED.  
**Étapes** (transaction) :
1. Créer/maj tech_inspections (unique).
2. Si OK : set registration TECH_CHECKED_OK
3. Si FAIL : notes requises, set TECH_CHECKED_FAIL
4. Audit : tech.ok / tech.fail
**Sorties** : inspection + registration mises à jour.

> NOTE : UC-08 peut être fusionné avec UC-07 via checkpoints TECH_CHECK_OK / TECH_CHECK_FAIL.
> Recommandation P0 : garder UC-07 (scan) + écran tech qui capture notes si FAIL.

## 8.9 UC-09 Importer résultats CSV
**But** : importer classement course à partir d’un CSV et préparer publication.  
**Acteur** : ADMIN, STAFF_ADMINISTRATIF.  
**Entrées** : race_id, fichier CSV.  
**Préconditions** : course existe.  
**Étapes** (transaction) :
1. Upload sécurisé (MIME, size).
2. Créer result_imports (status PENDING).
3. Lire CSV et valider lignes :
   - bib présent, int
   - position int >=1
   - temps parsable
   - bib unique dans fichier + unique DB (race_id,bib)
   - bib correspond à une voiture existante (cars.race_number)
   - matching registration (race_id + car_id) recommandé
4. Si erreurs :
   - result_imports.status = FAILED
   - errors_json = détails
   - rollback race_results
5. Sinon :
   - insérer race_results
   - result_imports.status = IMPORTED, row_count
   - set race_registrations (concernées) => RESULTS_IMPORTED (option)
   - set race.status = RESULTS_READY
6. Audit : results.imported
**Sorties** : import historisé + race_results.
**Erreurs** : fichier invalide, doublons, bib inconnu.

## 8.10 UC-10 Publier résultats
**But** : rendre visibles les résultats + verrouiller.  
**Acteur** : ADMIN, STAFF_ADMINISTRATIF.  
**Entrées** : race_id.  
**Préconditions** :
- race.status = RESULTS_READY
- dernier import réussi
**Étapes** (transaction) :
1. Vérifier résultats présents.
2. race.status = PUBLISHED
3. set registrations => PUBLISHED (si suivi par inscription)
4. Audit : results.published
5. Déclencher job recalcul championnat (si saison)
**Sorties** : résultats publiés.

## 8.11 UC-11 Recalculer championnat saison
**But** : calculer standings général + par catégorie.  
**Acteur** : système (job), ADMIN (manual trigger).  
**Entrées** : season_id.  
**Préconditions** : courses publiées.  
**Étapes** :
1. Charger barème season_points_rules (sinon seed par défaut).
2. Pour chaque race publiée :
   - récupérer race_results
   - attribuer points par position (others = 5)
3. Agréger par pilote :
   - races_count, base_points
4. Appliquer règle “min 2 courses” (exclure du ranking officiel)
5. Appliquer bonus +20 si le pilote a une participation sur **toutes** les courses de la saison
6. Calculer rank (order by total_points desc)
7. Écrire season_standings + season_category_standings
8. Audit : championship.rebuilt (optionnel)
**Sorties** : standings persistés, consultables.

---

# 9. UI (P0) – Pages essentielles (Volt)

## 9.1 Pilote
- /pilot/home
- /pilot/profile
- /pilot/cars (CRUD)
- /pilot/races (liste OPEN)
- /pilot/races/{race}
- /pilot/races/{race}/register
- /pilot/registrations
- /pilot/registrations/{reg}
- /pilot/registrations/{reg}/ecard (QR)
- /pilot/registrations/{reg}/engagement
- /pilot/results

## 9.2 Staff
- /staff/home
- /staff/races
- /staff/races/{race}
- /staff/races/{race}/registrations
- /staff/registrations/{reg}
- /staff/registrations/{reg}/validate
- /staff/registrations/{reg}/paddock
- /staff/registrations/{reg}/tech
- /staff/scan/admin
- /staff/scan/tech
- /staff/scan/entry
- /staff/scan/bracelet

## 9.3 Admin
- /admin/seasons
- /admin/races/create
- /admin/car-categories
- /admin/races/{race}/results/import
- /admin/races/{race}/results/publish
- /admin/championship/{season}

---

# 10. SÉCURITÉ (OWASP) + QUALITÉ

## 10.1 Sécurité
- Rate limit login + scan endpoint
- Uploads : MIME strict + size limit + stockage sécurisé
- QR token : opaque + hash sha256 en DB
- Policies + permissions partout
- Transactions sur opérations critiques
- Audit : activitylog

## 10.2 Qualité
- Tests Pest (unit + feature)
- Factories + seeders
- Migrations strictes + indexes
- Logs structurés + request_id

---

# 11. PLAN D’IMPLÉMENTATION PAR SPRINT (Optimisé Agent IA)

> Chaque sprint est “IA-friendly” : taille limitée, livrables vérifiables, pas de mélange de responsabilités.

## Sprint 0 — Setup & Fondations
**Objectif** : App bootable + auth + RBAC + audit.  
**Livrables** :
- Laravel 12 + Breeze Livewire + Tailwind
- config MySQL strict
- spatie permission + roles/permissions seeder
- spatie activitylog config
- dashboard redirect par rôle
- tests : accès routes par rôle (smoke)

## Sprint 1 — Pilotes & Voitures
**Objectif** : profil pilote + voitures (contraintes fortes).  
**Livrables** :
- migrations pilots, car_categories, cars
- validations licence unique digits ≤6
- validations race_number unique 0..999
- UI pilote : profil + cars CRUD
- policies Pilot/Car
- tests : unique constraints + ownership

## Sprint 2 — Saisons / Courses / Inscriptions
**Objectif** : course OPEN + inscription pilote.  
**Livrables** :
- migrations seasons, races, race_registrations
- UI staff/admin : créer/ouvrir course
- UI pilote : lister courses OPEN + s’inscrire
- UC-04 SubmitRegistration (transaction + audit)
- tests : unique (race_id,pilot_id) / (race_id,car_id)

## Sprint 3 — Validation & Paddock & PDF
**Objectif** : staff valide + affecte paddock + export PDF engagés.  
**Livrables** :
- UC-05 ValidateRegistration
- UC-06 AssignPaddock
- payments MANUAL (table + enregistrement)
- dompdf : PDF engagés
- UI staff : validate/paddock
- tests : transitions accept/refuse + reason obligatoire

## Sprint 4 — QR & Scans
**Objectif** : e-carte QR + scan checkpoint sécurisé.  
**Livrables** :
- migrations checkpoints, qr_tokens, checkpoint_passages
- QrTokenService (token + hash)
- UC-07 ScanCheckpoint
- UI scan (4 pages)
- rate limit scan
- tests : token invalid, role invalid, transition invalid, double scan

## Sprint 5 — Technique & Engagement
**Objectif** : contrôle technique + signature engagement P0 simple.  
**Livrables** :
- migration tech_inspections (+ engagement_signatures si retenu)
- UI tech OK/FAIL + notes
- blocage entrée si TECH_FAIL
- tests : tech ok/fail transitions

## Sprint 6 — Import CSV & Publication
**Objectif** : import résultats + publication.  
**Livrables** :
- migrations result_imports, race_results
- importer CSV + validation
- UC-09 ImportRaceResults
- UC-10 PublishRaceResults
- UI admin import + publish
- tests : doublon bib, bib inconnu, temps invalide, publish preconditions

## Sprint 7 — Championnat
**Objectif** : standings saison général + par catégorie.  
**Livrables** :
- migrations season_points_rules, season_standings, season_category_standings
- seed barème
- UC-11 RebuildSeasonStandings (job)
- UI admin championnat
- tests : min 2 courses, bonus +20, ranking

---

# 12. PROMPTS “AGENT IA” PAR SPRINT (prêts à copier)

> Chaque prompt est conçu pour un agent IA dev.  
> Format : Contexte + Contraintes + Tâches + Livrables + DoD + Interdits.

## Prompt Sprint 0
### CONTEXTE
Tu développes Run200 Manager (Laravel 12, Livewire 3 + Volt, MySQL).  
### CONTRAINTES
- Clean Architecture : Domain/Application/Infrastructure
- RBAC via spatie/permission
- Audit via spatie/activitylog
### TÂCHES
1. Installer Breeze Livewire + Tailwind.
2. Configurer MySQL strict + migrations.
3. Installer spatie permission + activitylog.
4. Créer seeder rôles/permissions (PILOTE, STAFF_*, ADMIN).
5. Dashboard : rediriger selon rôle.
### LIVRABLES
- Commit avec composer/npm install + configs
- Seeders roles/permissions + tests smoke RBAC
### DOD
- `php artisan test` OK
- RBAC fonctionne : un pilote ne peut pas ouvrir /staff/*
### INTERDITS
- Pas de logique métier dans UI
- Pas de packages non listés

## Prompt Sprint 1
### CONTEXTE
Créer le module Pilote/Voitures.  
### CONTRAINTES
- Licence unique digits <=6
- Numéro voiture unique à vie 0..999
### TÂCHES
1. Migrations : pilots, car_categories, cars (contraintes uniques + indexes).
2. FormRequests : validation licence et race_number.
3. UI Volt : profil + cars CRUD.
4. Policies Pilot/Car (ownership).
5. Seed catégories initiales Run200 (liste fournie).
### LIVRABLES
- Migrations + modèles + UI + policies
- Tests : unique license, unique race_number, ownership cars
### DOD
- Un pilote peut créer une voiture #42 et ne peut pas créer une autre #42.
### INTERDITS
- Pas d’enums figées pour catégories (table modifiable admin)

## Prompt Sprint 2
### TÂCHES
- Migrations seasons, races, race_registrations (unique (race,pilot) et (race,car))
- UI : staff crée course, pilote s’inscrit
- Use case SubmitRegistration (transaction + audit) + génération QR token placeholder (si Sprint 4 pas encore)
- Tests : contraintes uniques et permissions
### DOD
- Une inscription est créée en PENDING_VALIDATION.

## Prompt Sprint 3
### TÂCHES
- Table payments (manuel)
- Use case ValidateRegistration (accept/refuse)
- Use case AssignPaddock
- PDF engaged list (dompdf)
- UI staff validate + paddock + export pdf
- Tests transitions + reason
### DOD
- ACCEPTED apparaît et PDF exportable.

## Prompt Sprint 4
### TÂCHES
- Tables checkpoints, qr_tokens, checkpoint_passages
- QrTokenService (token opaque + hash)
- Use case ScanCheckpoint
- Pages scan (admin/tech/entry/bracelet) + endpoint POST /internal/scan
- Rate limit scan + tests (double scan)
### DOD
- Un scan ENTRY refuse si TECH_OK non atteint.

## Prompt Sprint 5
### TÂCHES
- Table tech_inspections + UI tech
- Notes obligatoires si FAIL
- Bloquer entrée si FAIL
- Option : engagement_signatures (P0 simple)
- Tests tech ok/fail
### DOD
- TECH_FAIL empêche transition ENTRY.

## Prompt Sprint 6
### TÂCHES
- Tables result_imports, race_results
- Import CSV via maatwebsite/excel (validation bib/position/temps)
- Historique import + erreurs JSON
- Publish results + visibilité pilote
- Tests import + publish
### DOD
- Publication impossible si import FAILED.

## Prompt Sprint 7
### TÂCHES
- Tables standings + rules
- Calcul min 2 courses + bonus +20 toutes courses
- Classement général + par catégorie
- UI admin championnat
- Tests standings
### DOD
- Un pilote avec 1 course n’apparaît pas classé.

---

# 13. CHECKLIST “MIGRATION QUALITY”
- Tous FK indexés
- Uniques : pilots.license_number, cars.race_number, (race_id,pilot_id), (race_id,car_id), (registration,checkpoint), (race_id,bib)
- Champs status indexés
- Transactions pour : submit, validate, scan, import, publish
- Pas de suppression en cascade non maîtrisée (préférer restrictions)

---

# 14. SEED DATA (référentiels initiaux)

## 14.1 Catégories (car_categories)
- Diesel 100% mecanique
- Diesel 4 cylindres 2rm
- Diesel 4 cylindres 2rm gaz
- Diesel 4 cylindres 4rm
- Diesel 4 cylindres 4rm gaz
- Diesel 6 cylindres 2rm
- Diesel 6 cylindres 2rm gaz
- Diesel 6 cylindres 4rm
- Diesel 6 cylindres 4rm gaz
- Essence 100%
- Essence 4 cylindres 2rm
- Essence 4 cylindres 4rm
- Essence 5 cylindres 4rm
- Essence 6 cylindres 2rm et plus
- Essence 6 cylindres 4rm et plus
- Essence 4 cylindres 2rm gaz
- Essence 4 cylindres 4rm gaz

## 14.2 Checkpoints (checkpoints)
- ADMIN_CHECK
- TECH_CHECK_OK
- TECH_CHECK_FAIL
- ENTRY
- BRACELET

---

# 15. FIN DU DOCUMENT
Ce document est la source de vérité pour :
- le métier
- l’architecture applicative
- l’architecture BDD
- le plan de delivery par sprints
- les prompts agent IA
- les use cases complets
