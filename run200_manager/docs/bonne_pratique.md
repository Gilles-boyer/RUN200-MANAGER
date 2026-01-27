# Run200 Manager — Dev Best Practices (Laravel 12 + Livewire + Volt)
Version: 1.0  
Audience: Devs / Agents IA / Mainteneurs  
Objectif: garantir un code **maintenable**, **sécurisé**, **testable** et **performant** sur un workflow terrain.

---

## 0) Non-négociables (Project Standards)

### 0.1 Standards de code
- Respect des conventions Laravel (noms, structure, patterns).
- Formatage automatique obligatoire (Pint) + CI qui bloque si non conforme.
- Tests automatisés (Pest) obligatoires sur les use cases critiques.
- Toute action métier critique = **Use Case transactionnel**, jamais en “composant UI”.

### 0.2 Principe de séparation (Clean Architecture)
**UI** (Volt/Livewire) → **Application (Use Cases)** → **Domain (règles)** → **Infrastructure (Eloquent, CSV, Mail, Stripe)**.

Règle : la couche UI ne doit contenir que :
- affichage
- collecte des inputs
- appels aux use cases
- gestion de feedback (success/errors)

---

## 1) Structure du repo (recommandée)

app/
Domain/
...
Application/
...
Infrastructure/
...
Http/
Controllers/
Requests/
Policies/
database/
migrations/
seeders/
tests/
Feature/
Unit/

yaml
Copier le code

### 1.1 Pourquoi cette structure ?
- Permet de découpler l’UI et la logique métier.
- Facilite les tests unitaires sans DB.
- Rend le projet plus “agent-friendly”.

---

## 2) Tooling Qualité (obligatoire)

### 2.1 Formatage
Installer Laravel Pint :
- Un run Pint avant commit
- Un run Pint dans CI

Commande:
```bash
composer require laravel/pint --dev
./vendor/bin/pint
2.2 Analyse statique (fortement recommandé)
Larastan/PHPStan (évite les erreurs “silencieuses”):

bash
Copier le code
composer require nunomaduro/larastan --dev
2.3 Git hooks (optionnel mais très utile)
pre-commit: pint + phpstan + tests ciblés

3) Conventions de nommage (Clean + Laravel)
3.1 Models (singulier)
Pilot, Car, Race, RaceRegistration, Payment, CheckpointPassage

3.2 Tables (snake_case pluriel)
race_registrations, checkpoint_passages

3.3 Colonnes (snake_case)
license_number, race_number, scanned_at

3.4 Enums
RegistrationStatus::PENDING_VALIDATION

4) Base de données : bonnes pratiques MySQL
4.1 Toujours appliquer les contraintes en DB
Même si validation côté PHP, la DB doit protéger l’intégrité.
Exemples essentiels :

pilots.license_number UNIQUE

cars.race_number UNIQUE

race_registrations UNIQUE (race_id, pilot_id)

checkpoint_passages UNIQUE (race_registration_id, checkpoint_id)

race_results UNIQUE (race_id, bib)

4.2 Indexer ce qui est filtré souvent
race_registrations.status

race_registrations.race_id

checkpoint_passages.scanned_at

4.3 Transactions sur opérations critiques
Toujours DB::transaction() sur :

inscription course

accept/refuse

scan checkpoint

import résultats

publication résultats

4.4 Migrations robustes
Toujours prévoir down()

Ne jamais faire de “drop column” en prod sans plan (migration safe)

Utiliser foreignId()->constrained()->restrictOnDelete() par défaut

5) Validation : Form Requests stricts
5.1 Règle d’or
Toute entrée utilisateur passe par un FormRequest, jamais validation “au fil de l’eau” dans le composant.

Exemple :

UpdatePilotProfileRequest

StoreCarRequest

SubmitRegistrationRequest

ImportResultsRequest

5.2 Validation sécurité
numeric, digits_between:1,6 pour les licences

integer|min:0|max:999 pour race_number

uploads: image|mimes:jpg,jpeg,png,webp|max:2048

6) RBAC & Authorization (Spatie + Policies)
6.1 Règle clé “roles vs permissions”
Les rôles groupent des permissions

Les permissions sont granulaires et explicites

6.2 Organisation recommandée
Permissions affectées aux rôles via seeder

Pas de permissions “magiques” dans le code

Exemples:

race_registration.validate

checkpoint.scan.entry

results.import

6.3 Policies partout
Même avec Spatie, les Policies sont la source propre de vérité métier :

RaceRegistrationPolicy@validate

CarPolicy@update (ownership)

Règle :

Une route staff/admin doit être protégée par :

middleware (role/permission)

policy (business rules)

7) Livewire 3 + Volt : bonnes pratiques
7.1 Organisation des composants
Page Components : pages complètes (index, show)

Nested Components : widgets réutilisables (badge statut, timeline)

7.2 Responsabilités
Dans un composant Livewire:
✅ OK:

charger la liste à afficher

appeler un Use Case

afficher messages succès/erreur

❌ Interdit:

effectuer une transition de statut directement

faire des requêtes complexes imbriquées sans service

écrire de la logique métier

7.3 Performance Livewire
Pagination obligatoire sur les listes (inscriptions, résultats)

Charger les relations avec ->with() (évite N+1)

Utiliser wire:key sur les boucles

Utiliser lazy loading pour composants non critiques

7.4 Gestion des formulaires
Petits formulaires → Livewire direct

Gros formulaires → utiliser FormRequest + DTO côté Use Case

7.5 Communication events
Utiliser dispatch() (client) et events Livewire pour refresh ciblé

Ne pas rafraîchir toute la page si un widget suffit

8) Workflow & State Machine (statuts)
8.1 Centraliser les transitions
Créer une classe Domain:

RegistrationTransitions::can($from, $to)

8.2 Refuser explicitement
Si transition invalide :

throw DomainException

log + audit “refus”

8.3 Source de vérité
Le statut en DB est la vérité

Le front n’est qu’un reflet

9) QR Code & anti-fraude
9.1 Token opaque + hash en DB
Le QR contient un token long (64 chars)

En base, stocker uniquement sha256(token) (jamais le token en clair)

9.2 Un scan = un passage unique
Contrainte DB :

UNIQUE(race_registration_id, checkpoint_id)

9.3 Rate limit
Appliquer throttle:scan

ex: 30 req/min/user

10) Import CSV (robustesse)
10.1 Principes
Upload sécurisé

Validation stricte des colonnes

Refus si bib inconnu ou doublon

Historique import conservé

10.2 Matching recommandé
bib = cars.race_number

retrouver race_registration de la course

10.3 Stratégie d’erreur
Si une ligne invalide → import FAILED

Stocker erreurs en JSON

Ne jamais publier si import invalidé

11) Logs & Audit (observabilité)
11.1 Audit (spatie/activitylog)
Audit obligatoire sur actions staff :

accept/refuse

scan checkpoint

import résultats

publish

Contenu properties recommandé :

race_id

registration_id

from/to status

checkpoint_code

car_number

11.2 Logs applicatifs
un channel dédié run200

logs JSON si possible

ajouter request_id via middleware

12) Files & Storage (uploads)
stocker via Storage::disk()

jamais utiliser un chemin fourni par user

valider MIME + taille

générer noms uniques (uuid)

13) Queues / Jobs
Recommandé pour :

import résultats (si gros CSV)

recalcul championnat

envoi notifications mail (P1)

Règles :

jobs idempotents si possible

retries + timeout définis

logs sur failure

14) Tests (Pest)
14.1 Minimum P0
Unit tests :

transitions statuts

calcul points championnat

Feature tests :

RBAC (accès routes)

scan checkpoint (happy path + refus)

import CSV (doublons, bib inconnu)

publication (préconditions)

14.2 Philosophie
tester la logique dans Use Cases

tests UI Livewire seulement si utile

15) CI/CD (recommandé)
15.1 CI obligatoire
Pipeline minimal :

composer install

npm build

pint

phpstan/larastan

tests

15.2 Environnements
local

staging (pré-prod)

production

En prod :

php artisan config:cache

php artisan route:cache

queue active (redis)

16) Sécurité (OWASP checklist)
CSRF (Laravel par défaut)

validation + FormRequests stricts

mass assignment: $fillable strict, jamais $guarded = []

policies partout

rate limiting login + scan

uploads sécurisés

secrets uniquement via .env (jamais commit)

Stripe webhooks: signature verification (P1)

17) “Definition of Done” (DoD)
Une feature est “done” si :

migrations + contraintes DB OK

policy + permission OK

use case transactionnel OK

tests passent

audit log présent si action staff

UI mobile acceptable

18) Checklist “Agent IA compatible”
Pour maximiser la réussite d’un agent IA :

une tâche = une PR

scope petit (1 use case / 1 migration)

toujours fournir “inputs / outputs / erreurs”

ne jamais mélanger UI + Domain

écrire tests pour verrouiller

