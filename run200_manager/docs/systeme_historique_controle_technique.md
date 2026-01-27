# Système d'Historique de Contrôle Technique des Voitures

## 📋 Vue d'ensemble

Système complet de traçabilité des contrôles techniques effectués sur les voitures, permettant aux contrôleurs techniques d'accéder à un historique détaillé avec annotations pour chaque contrôle.

## 🗂️ Architecture

### Base de données

**Table: `car_tech_inspection_histories`**
```sql
- id (primary key)
- car_id (foreign key → cars)
- race_registration_id (nullable, foreign key → race_registrations)
- tech_inspection_id (nullable, foreign key → tech_inspections)
- status (enum: 'OK', 'FAIL')
- notes (text, nullable) - Annotations du contrôleur
- inspection_details (json, nullable) - Détails techniques structurés
- inspected_by (foreign key → users)
- inspected_at (timestamp)
- created_at, updated_at
```

**Indexes optimisés** pour:
- Recherche par voiture
- Recherche par inspecteur
- Recherche par statut
- Recherche par date
- Requêtes combinées (car_id + inspected_at)

### Modèles

#### **CarTechInspectionHistory** 
`app/Models/CarTechInspectionHistory.php`

**Relations:**
- `car()` - BelongsTo Car
- `registration()` - BelongsTo RaceRegistration (nullable)
- `techInspection()` - BelongsTo TechInspection (nullable)
- `inspector()` - BelongsTo User

**Scopes disponibles:**
```php
->forCar($carId)           // Filtrer par voiture
->byStatus($status)        // Filtrer par statut (OK/FAIL)
->byInspector($inspectorId) // Filtrer par inspecteur
->okOnly()                 // Uniquement les contrôles OK
->failedOnly()             // Uniquement les contrôles échoués
->latestFirst()            // Trier par date décroissante
```

**Helpers:**
```php
$history->isOk()           // bool
$history->isFail()         // bool
$history->car_full_name    // string (ex: "Porsche 911 #42")
$history->inspector_name   // string
$history->race_name        // ?string
```

#### **Car** (Modifié)
`app/Models/Car.php`

**Nouvelles relations:**
```php
$car->techInspectionHistory()  // HasMany - Tout l'historique
$car->latestTechInspection()   // HasOne - Dernier contrôle
```

### Use Case

#### **RecordTechInspection** (Mis à jour)
`app/Application/Registrations/UseCases/RecordTechInspection.php`

**Fonctionnement:**
1. Crée l'entrée dans `tech_inspections` (pour l'inscription)
2. **Crée automatiquement l'entrée dans `car_tech_inspection_histories`** (pour l'historique de la voiture)
3. Met à jour le statut de l'inscription
4. Dispatch l'événement `TechInspectionCompleted`
5. Log l'activité

**Avantage:** Historique persistant même si l'inscription est supprimée

### Interface Utilisateur

#### **Composant Livewire**
`app/Livewire/Staff/Cars/TechInspectionHistory.php`

**Fonctionnalités:**
- ✅ Affichage paginé de l'historique (15 par page)
- ✅ Filtres multiples:
  - Par statut (OK/FAIL/Tous)
  - Par inspecteur
  - Par période (date début - date fin)
- ✅ Statistiques en temps réel:
  - Total des contrôles
  - Nombre de contrôles OK
  - Nombre de contrôles échoués
  - Date du dernier contrôle
- ✅ Tri par date (plus récents en premier)
- ✅ Affichage des annotations

#### **Vue Blade**
`resources/views/livewire/staff/cars/tech-inspection-history.blade.php`

**Éléments d'interface:**
- 4 cartes de statistiques en haut
- Filtres avancés avec réinitialisation
- Tableau responsive avec:
  - Date et heure du contrôle
  - Course associée (si applicable)
  - Statut (badge coloré avec icône)
  - Nom de l'inspecteur
  - Notes/Annotations complètes
- Pagination
- Mode dark compatible

### Routes

```php
// Route principale
GET /staff/cars/{car}/tech-history
Route: staff.cars.tech-history
Middleware: auth, role:STAFF|ADMIN, permission:tech_inspection.manage
```

### Navigation

**Accès à l'historique:**
1. **Depuis le formulaire de contrôle technique:**
   - Bouton "Historique" en haut à droite
   - Lien direct: `/staff/registrations/{registration}/tech`
   - Bouton visible à côté du statut de l'inscription

2. **Accès direct par URL:**
   - `/staff/cars/{car_id}/tech-history`

## 📊 Cas d'usage

### 1. Contrôleur technique vérifie une voiture
```
Scénario: Le contrôleur veut voir l'historique d'une voiture avant de commencer le contrôle

1. Sur la page de contrôle technique (/staff/registrations/{id}/tech)
2. Clic sur le bouton "Historique" (icône horloge)
3. Affichage de l'historique complet de la voiture
4. Visualisation des contrôles précédents avec annotations
5. Retour au formulaire de contrôle
```

### 2. Recherche de problèmes récurrents
```
Scénario: Trouver pourquoi une voiture échoue souvent

1. Accès à l'historique de la voiture
2. Filtre: Statut = "FAIL"
3. Lecture des annotations de chaque échec
4. Identification du problème récurrent
```

### 3. Audit des contrôles par inspecteur
```
Scénario: Vérifier les contrôles effectués par un inspecteur spécifique

1. Accès à l'historique d'une voiture
2. Filtre: Inspecteur = "Jean Dupont"
3. Analyse des contrôles effectués
4. Vérification de la qualité des annotations
```

### 4. Statistiques d'une voiture
```
Scénario: Obtenir le taux de réussite d'une voiture

En haut de la page d'historique:
- Total: 15 contrôles
- OK: 12 (80%)
- Échoués: 3 (20%)
- Dernier contrôle: 15/01/2026
```

## 🔐 Permissions

**Permission requise:** `tech_inspection.manage`

**Rôles autorisés:**
- STAFF
- ADMIN

## 📝 Exemples de données

### Historique type d'une voiture

```
#42 Porsche 911 GT3 - Historique

Statistiques:
- Total: 8 contrôles
- OK: 6
- Échoués: 2
- Dernier: 20/01/2026

Historique:
┌──────────────┬────────────────────┬─────────┬──────────────┬─────────────────────────┐
│ Date         │ Course             │ Statut  │ Inspecteur   │ Notes                   │
├──────────────┼────────────────────┼─────────┼──────────────┼─────────────────────────┤
│ 20/01/26 14h │ Course Barcelone   │ ✓ OK    │ M. Dubois    │ RAS, tout conforme      │
│ 15/01/26 14h │ Course Paris       │ ✗ FAIL  │ M. Martin    │ Freins usés, remplacer  │
│ 10/01/26 14h │ Course Lyon        │ ✓ OK    │ M. Dubois    │ Bon état général        │
│ 05/01/26 14h │ Course Marseille   │ ✓ OK    │ Mme Durand   │ Pneus neufs, parfait    │
└──────────────┴────────────────────┴─────────┴──────────────┴─────────────────────────┘
```

## 🔄 Workflow complet

```
1. Inscription à une course
   ↓
2. Validation administrative
   ↓
3. Contrôle technique
   ├─→ Création dans tech_inspections (lié à l'inscription)
   └─→ Création dans car_tech_inspection_histories (historique permanent)
   ↓
4. Statut inscription mis à jour (TECH_CHECKED_OK/FAIL)
   ↓
5. Email envoyé au pilote
   ↓
6. Historique consultable à tout moment
```

## 🎯 Avantages du système

1. **Traçabilité complète**: Tous les contrôles sont enregistrés
2. **Persistance**: L'historique survit à la suppression des inscriptions
3. **Annotations détaillées**: Notes libres pour chaque contrôle
4. **Recherche puissante**: Filtres multiples et rapides
5. **Statistiques**: Vue d'ensemble instantanée
6. **Audit**: Suivi des inspecteurs
7. **Dark mode**: Compatible avec tous les thèmes
8. **Performance**: Indexes optimisés pour les grosses bases

## 📁 Fichiers créés/modifiés

### Nouveaux fichiers (6)
```
database/migrations/2026_01_26_120000_create_car_tech_inspection_histories_table.php
app/Models/CarTechInspectionHistory.php
app/Livewire/Staff/Cars/TechInspectionHistory.php
resources/views/livewire/staff/cars/tech-inspection-history.blade.php
```

### Fichiers modifiés (3)
```
app/Models/Car.php (ajout relations techInspectionHistory)
app/Application/Registrations/UseCases/RecordTechInspection.php (ajout création historique)
routes/web.php (ajout route staff.cars.tech-history)
resources/views/livewire/staff/registrations/tech-inspection-form.blade.php (ajout bouton)
```

## 🧪 Tests suggérés

### Tests manuels à effectuer

1. **Créer un contrôle technique**
   - Aller sur une inscription
   - Effectuer un contrôle (OK ou FAIL)
   - Vérifier que l'entrée apparaît dans l'historique

2. **Tester les filtres**
   - Filtre par statut
   - Filtre par inspecteur
   - Filtre par période
   - Combinaison de filtres

3. **Vérifier les statistiques**
   - Total correspond au nombre de lignes
   - Somme OK + FAIL = Total
   - Dernier contrôle = date la plus récente

4. **Tests de performance**
   - Créer 50+ contrôles pour une voiture
   - Vérifier la pagination
   - Tester la rapidité des filtres

## 🚀 Déploiement

```bash
# 1. Exécuter la migration
php artisan migrate

# 2. Vider le cache (si nécessaire)
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 3. Tester l'accès
# Aller sur: /staff/registrations/{id}/tech
# Cliquer sur "Historique"
```

## 🔮 Évolutions possibles

1. **Export PDF/Excel** de l'historique
2. **Graphiques** d'évolution des contrôles
3. **Alertes automatiques** si trop d'échecs
4. **Photos** attachées aux contrôles
5. **Checklist détaillée** (freins, pneus, sécurité, etc.)
6. **Comparaison** entre deux contrôles
7. **Signature électronique** du contrôleur
8. **QR Code** pour accès rapide à l'historique

## 📞 Support

Pour toute question sur ce système:
- Architecture: Clean Architecture (Domain-Driven Design)
- Framework: Laravel 12
- Frontend: Livewire 3 + Tailwind CSS
- Base de données: MySQL 8.0+
