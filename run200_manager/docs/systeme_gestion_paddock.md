# Système de Gestion des Emplacements de Paddock

## Vue d'ensemble

Système complet de gestion des emplacements de paddock pour les courses RUN200, permettant aux pilotes de choisir leur emplacement de stationnement et au staff/admin de gérer les assignations.

**Capacité totale:** 90 emplacements répartis en 3 zones (A, B, C)

> **⚠️ IMPORTANT - NOUVELLE ARCHITECTURE (Juin 2024)**
>
> Les réservations de paddock sont **spécifiques à chaque course**. Un emplacement peut être réservé par différents pilotes pour différentes courses. Le champ `is_available` indique désormais si l'emplacement est "en service" (pas en maintenance), et non plus s'il est "libre".

---

## Architecture

### Base de données

#### Table `paddock_spots`
```sql
- id (bigint, PK)
- spot_number (string, unique) -- Ex: "A1", "B15", "C30"
- zone (string) -- "A", "B", ou "C"
- position_x (integer, nullable) -- Position X sur le plan (pixels)
- position_y (integer, nullable) -- Position Y sur le plan (pixels)
- is_available (boolean) -- En service (true) ou hors service (false)
- notes (text, nullable) -- Notes sur l'emplacement
- created_at, updated_at (timestamps)

Indexes:
- zone
- is_available
```

> **Note:** `is_available` ne signifie PAS "non réservé" mais "en service". Un emplacement peut être `is_available = true` et avoir plusieurs réservations pour différentes courses.

#### Modification table `race_registrations`
```sql
- paddock_spot_id (bigint, nullable, FK → paddock_spots.id)
- paddock (string, nullable) -- Maintenu pour compatibilité
```

### Modèles

#### `App\Models\PaddockSpot`

**Relations:**
- `registrations()` → HasMany RaceRegistration (toutes les réservations de l'emplacement)

**Scopes:**
- `inService()` → Emplacements en service (is_available = true)
- `outOfService()` → Emplacements hors service
- `availableForRace(int $raceId)` → Emplacements libres pour une course spécifique
- `occupiedForRace(int $raceId)` → Emplacements occupés pour une course spécifique
- `inZone(string $zone)` → Filtrer par zone
- `byNumber(string $direction)` → Trier par numéro

**Méthodes d'instance:**
- `isAvailableForRace(int $raceId)` → bool - Vérifie si libre pour cette course
- `isOccupiedForRace(int $raceId)` → bool - Vérifie si occupé pour cette course
- `registrationForRace(int $raceId)` → ?RaceRegistration - Récupère la réservation pour cette course
- `getPilotForRace(int $raceId)` → ?Pilot - Récupère le pilote pour cette course
- `getFullNameAttribute()` → string (ex: "Zone A - Emplacement A12")
- `getCoordinates()` → ?array ['x' => int, 'y' => int]

**Méthodes statiques:**
- `getStatisticsForRace(int $raceId)` → array - Stats pour une course
- `getGlobalStatistics()` → array - Stats globales (en service/hors service)

#### `App\Models\RaceRegistration` (Modifications)

**Nouvelle relation:**
- `paddockSpot()` → BelongsTo PaddockSpot

**Champs ajoutés au fillable:**
- `paddock_spot_id`

**Activity Log:**
- Ajout de `paddock_spot_id` aux champs loggés

---

## Use Cases (Application Layer)

### `App\Application\Registrations\UseCases\AssignPaddockSpot`

**Responsabilité:** Assigner un emplacement à une inscription pour une course

**Méthode:** 
```php
execute(
    RaceRegistration $registration,
    PaddockSpot $spot,
    User $assignedBy,
    bool $force = false
): RaceRegistration
```

**Validations:**
1. L'inscription doit être acceptée (sauf si force admin)
2. L'emplacement doit être en service (`is_available = true`)
3. L'emplacement ne doit pas être déjà réservé **pour cette course** (sauf si force admin)
4. Seul admin peut forcer une assignation

**Comportement:**
- Libère l'ancien emplacement de l'inscription si présent
- Assigne le nouvel emplacement
- Log l'activité avec race_id et race_name

- Dispatch l'événement `PaddockSpotAssigned`

### `App\Application\Registrations\UseCases\ReleasePaddockSpot`

**Responsabilité:** Libérer un emplacement assigné à une inscription

**Méthode:**
```php
execute(
    RaceRegistration $registration,
    User $releasedBy
): RaceRegistration
```

**Comportement:**
- Supprime la référence paddock_spot_id de l'inscription
- Log l'activité avec race_id et race_name
- Dispatch l'événement `PaddockSpotReleased`

> **Note:** Ne modifie PAS le champ `is_available` de l'emplacement car celui-ci représente désormais l'état "en service" et non "libre/occupé".

---

## Événements

### `App\Events\PaddockSpotAssigned`
Déclenché quand un emplacement est assigné à une inscription

**Propriétés:**
- `RaceRegistration $registration`
- `PaddockSpot $spot`
- `User $assignedBy`

### `App\Events\PaddockSpotReleased`
Déclenché quand un emplacement est libéré

**Propriétés:**
- `RaceRegistration $registration`
- `PaddockSpot $spot`
- `User $releasedBy`

---

## Permissions & Policies

### `RaceRegistrationPolicy` (Nouvelles méthodes)

#### `selectPaddockSpot(User $user, RaceRegistration $registration)`
**Autorisation:**
- Staff/Admin: TOUJOURS
- Pilote: Uniquement si c'est son inscription ET elle est acceptée

#### `releasePaddockSpot(User $user, RaceRegistration $registration)`
**Autorisation:**
- Staff/Admin: TOUJOURS
- Pilote: Uniquement si c'est son inscription

---

## Interface Utilisateur

### Pour les Pilotes

#### Route: `pilot.registrations.paddock.select`
**URL:** `/pilot/registrations/{registration}/paddock`

**Composant:** `App\Livewire\Pilot\Registrations\PaddockSelection`

**Fonctionnalités:**
1. **Contexte de course**
   - Affichage du nom de la course concernée
   - Les statistiques et disponibilités sont pour cette course uniquement

2. **Statistiques en temps réel (pour la course)**
   - Total emplacements en service
   - Disponibles pour cette course
   - Occupés pour cette course
   - Taux d'occupation pour cette course

3. **Filtres**
   - Par zone (A, B, C, Toutes)

4. **Plan interactif**
   - Grille visuelle de tous les emplacements
   - Code couleur:
     - ✅ Vert = Disponible pour cette course
     - ❌ Rouge = Occupé pour cette course
     - 🔵 Bleu = Sélectionné
   - Numéro d'emplacement visible
   - Badge zone
   - Statut visible

5. **Actions**
   - Cliquer sur un emplacement disponible → Sélectionner
   - Cliquer sur un emplacement occupé → Voir qui l'occupe
   - Bouton "Confirmer et Réserver" pour valider
   - Bouton "Libérer l'emplacement" si déjà assigné

6. **Modal détails**
   - Informations sur l'emplacement occupé
   - Nom du pilote
   - Voiture (#numéro, marque, modèle)
   - Notes éventuelles

**Restrictions:**
- Accessible uniquement si inscription acceptée
- Ne peut réserver qu'un emplacement disponible pour cette course
- Peut voir mais pas prendre un emplacement occupé
- L'emplacement réservé est lié à l'inscription, donc à une course spécifique

### Pour le Staff/Admin

#### Route: `staff.paddock.manage`
**URL:** `/staff/paddock`

**Composant:** `App\Livewire\Staff\Paddock\ManagePaddock`

**Fonctionnalités:**
1. **Sélection de course obligatoire**
   - Statistiques et disponibilités dépendent de la course sélectionnée
   - Sans course sélectionnée : vue globale des emplacements en service

2. **Statistiques contextuelles**
   - Si course sélectionnée : Stats pour cette course (disponibles/occupés)
   - Si pas de course : Stats globales (en service/hors service)

3. **Filtres avancés**
   - Sélection de course (obligatoire pour assigner)
   - Par zone
   - Afficher seulement disponibles (pour la course)
   - Recherche pilote (pour assignation)

4. **Plan interactif avec gestion**
   - Grille grisée si pas de course sélectionnée
   - Code couleur selon disponibilité pour la course
   - Bouton "X" sur emplacements occupés pour libérer
   - Cliquer sur emplacement → Ouvrir modal d'assignation

5. **Modal d'assignation**
   - Liste des inscriptions acceptées pour la course
   - Recherche par nom de pilote
   - Voir si pilote a déjà un emplacement pour cette course
   - Bouton "Assigner l'emplacement"

6. **Pouvoirs admin**
   - Peut forcer l'assignation d'un emplacement occupé
   - Peut libérer n'importe quel emplacement
   - Peut assigner n'importe quelle inscription

**Permission requise:** `registration.manage`

---

## Routes

### Routes Pilotes
```php
GET /pilot/registrations/{registration}/paddock
→ App\Livewire\Pilot\Registrations\PaddockSelection
→ Nom: pilot.registrations.paddock.select
→ Middleware: auth, role:PILOTE, EnsurePilotCanRegisterForRace
```

### Routes Staff/Admin
```php
GET /staff/paddock
→ App\Livewire\Staff\Paddock\ManagePaddock
→ Nom: staff.paddock.manage
→ Middleware: auth, role:ADMIN|STAFF_ADMINISTRATIF..., permission:registration.manage
```

---

## Navigation

### Menu Pilote
- Dans "Mes Inscriptions" → Lien "Choisir mon emplacement" sur chaque inscription acceptée

### Menu Staff
Section "Paddock" (sidebar):
- 📍 Gestion des Emplacements

### Menu Admin
Section "Paddock" (sidebar):
- 📍 Gestion des Emplacements

---

## Commandes Artisan

### `php artisan paddock:seed`
Crée les 90 emplacements de paddock répartis en 3 zones

**Options:**
- `--reset` : Supprime tous les emplacements existants avant de recréer

**Utilisation:**
```bash
# Créer les emplacements (première fois)
php artisan paddock:seed

# Réinitialiser et recréer tous les emplacements
php artisan paddock:seed --reset
```

**Zones créées:**
- Zone A: A1 → A30 (côté gauche)
- Zone B: B1 → B30 (zone centrale/piste)
- Zone C: C1 → C30 (côté droit)

---

## Workflow Utilisateur

### Scénario 1: Pilote choisit son emplacement

1. Pilote s'inscrit à une course
2. Inscription validée par staff (status → ACCEPTED)
3. Pilote accède à "Mes Inscriptions"
4. Clique sur "Choisir mon emplacement"
5. Voit le plan avec emplacements disponibles (vert) et occupés (rouge)
6. Filtre par zone si souhaité
7. Clique sur un emplacement disponible
8. Clique sur "Confirmer et Réserver"
9. ✅ Emplacement réservé !
10. Peut libérer et choisir un autre si changement d'avis

### Scénario 2: Staff assigne un emplacement

1. Staff accède à "Gestion du Paddock"
2. Sélectionne la course
3. Voit tous les emplacements
4. Clique sur un emplacement
5. Modal s'ouvre avec liste des inscriptions
6. Recherche le pilote
7. Sélectionne l'inscription
8. Clique sur "Assigner l'emplacement"
9. ✅ Emplacement assigné !

### Scénario 3: Admin force une assignation

1. Admin accède à "Gestion du Paddock"
2. Clique sur un emplacement OCCUPÉ
3. Assigne une nouvelle inscription
4. System libère automatiquement l'ancien occupant
5. ✅ Nouvel emplacement assigné !

---

## Sécurité & Validation

### Validations métier

1. **Inscription acceptée**
   - Seules les inscriptions avec status = "ACCEPTED" peuvent réserver
   - Admin peut forcer pour autres statuts

2. **Emplacement disponible**
   - Pilote ne peut réserver qu'un emplacement disponible
   - Admin peut forcer sur emplacement occupé

3. **Pas de conflit**
   - Vérification qu'un pilote n'a pas déjà cet emplacement pour une course le même jour

4. **Un emplacement par inscription**
   - Si pilote change d'emplacement, l'ancien est libéré automatiquement

### Activity Logging

Toutes les actions sont loggées via Spatie Activity Log:
- Qui a assigné/libéré
- Quel emplacement
- Quelle inscription
- Rôle de l'utilisateur (admin/staff/pilot)

---

## Statistiques & Monitoring

### Disponibles via `PaddockSpot::getStatistics()`

```php
[
    'total' => 90,
    'available' => 45,
    'occupied' => 45,
    'occupancy_rate' => 50.00
]
```

Affichées en temps réel sur:
- Page de sélection pilote
- Page de gestion staff

---

## Tests

### Tests unitaires suggérés

1. **PaddockSpot Model**
   - `test_spot_can_be_marked_as_occupied()`
   - `test_spot_can_be_marked_as_available()`
   - `test_spot_returns_current_pilot()`
   - `test_statistics_are_calculated_correctly()`

2. **AssignPaddockSpot UseCase**
   - `test_can_assign_spot_to_accepted_registration()`
   - `test_cannot_assign_occupied_spot_without_force()`
   - `test_admin_can_force_assign_occupied_spot()`
   - `test_releases_old_spot_when_assigning_new_one()`
   - `test_validates_registration_must_be_accepted()`

3. **ReleasePaddockSpot UseCase**
   - `test_can_release_assigned_spot()`
   - `test_marks_spot_as_available_after_release()`

4. **Policy**
   - `test_pilot_can_select_spot_for_own_accepted_registration()`
   - `test_pilot_cannot_select_spot_for_other_registration()`
   - `test_staff_can_always_assign_spots()`

---

## Évolutions futures possibles

1. **Plan visuel personnalisé**
   - Upload d'une image de plan
   - Positionnement drag & drop des emplacements

2. **Réservations temporaires**
   - Réserver un emplacement pour X minutes avant validation

3. **Préférences**
   - Pilote peut marquer des emplacements favoris
   - Système suggère emplacements selon préférences

4. **Historique**
   - Voir tous les emplacements utilisés par un pilote
   - Statistiques par zone (plus populaire)

5. **Notifications**
   - Email quand emplacement assigné
   - Email si emplacement libéré par admin

6. **Export**
   - PDF du plan avec noms de pilotes
   - Excel liste des assignations

---

## Fichiers créés/modifiés

### Nouveaux fichiers (14)

1. `database/migrations/2026_01_26_150000_create_paddock_spots_table.php`
2. `app/Models/PaddockSpot.php`
3. `app/Application/Registrations/UseCases/AssignPaddockSpot.php`
4. `app/Application/Registrations/UseCases/ReleasePaddockSpot.php`
5. `app/Events/PaddockSpotAssigned.php`
6. `app/Events/PaddockSpotReleased.php`
7. `app/Livewire/Pilot/Registrations/PaddockSelection.php`
8. `resources/views/livewire/pilot/registrations/paddock-selection.blade.php`
9. `app/Livewire/Staff/Paddock/ManagePaddock.php`
10. `resources/views/livewire/staff/paddock/manage-paddock.blade.php`
11. `app/Console/Commands/SeedPaddockSpots.php`
12. `docs/systeme_gestion_paddock.md` (ce fichier)

### Fichiers modifiés (4)

1. `app/Models/RaceRegistration.php`
   - Ajout `paddock_spot_id` au fillable
   - Ajout relation `paddockSpot()`
   - Ajout `paddock_spot_id` à l'activity log

2. `app/Policies/RaceRegistrationPolicy.php`
   - Ajout méthode `selectPaddockSpot()`
   - Ajout méthode `releasePaddockSpot()`

3. `routes/web.php`
   - Route pilote: `pilot.registrations.paddock.select`
   - Route staff: `staff.paddock.manage`

4. `resources/views/layouts/app/sidebar.blade.php`
   - Section "Paddock" pour Admin
   - Section "Paddock" pour Staff

---

## Déploiement

### Étapes d'installation

1. **Exécuter la migration**
```bash
php artisan migrate
```

2. **Créer les emplacements**
```bash
php artisan paddock:seed
```

3. **Vérifier les permissions**
```bash
# S'assurer que la permission "registration.manage" existe
# Déjà présente dans le système, utilisée par staff
```

4. **Tester**
- Créer une inscription test
- La valider (status = ACCEPTED)
- Accéder à la sélection d'emplacement
- Vérifier l'assignation

### Rollback si nécessaire

```bash
# Supprimer tous les emplacements
php artisan paddock:seed --reset

# Ou rollback migration
php artisan migrate:rollback
```

---

## Support & Maintenance

### Commandes utiles

```bash
# Voir statistiques paddock
php artisan tinker
>>> PaddockSpot::getStatistics()

# Libérer tous les emplacements
>>> PaddockSpot::query()->update(['is_available' => true]);
>>> RaceRegistration::query()->update(['paddock_spot_id' => null]);

# Voir emplacements occupés
>>> PaddockSpot::occupied()->with('currentRegistration.pilot')->get()
```

### Logs & Activité

Toutes les actions paddock sont loggées dans `activity_log` table:
```php
activity()->log('Emplacement de paddock assigné');
```

Requête pour voir activité:
```sql
SELECT * FROM activity_log 
WHERE description LIKE '%paddock%' 
ORDER BY created_at DESC;
```

---

## Conclusion

Système complet de gestion des emplacements de paddock:
- ✅ 90 emplacements (zones A, B, C)
- ✅ Interface pilote intuitive
- ✅ Interface staff/admin puissante
- ✅ Validations métier strictes
- ✅ Activity logging complet
- ✅ Events pour extensibilité
- ✅ Clean Architecture respectée
- ✅ Documentation complète

**Status:** Production Ready 🚀
