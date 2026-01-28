# Système de Tableau d'Affichage Numérique

## Vue d'ensemble

Le **Tableau d'Affichage Numérique** remplace le tableau d'affichage physique obligatoire lors des courses automobiles. Il permet :

- L'accès public aux documents officiels de chaque course via une URL dédiée
- Le scan d'un QR Code sur site pour un accès mobile rapide
- La gestion centralisée des documents par les administrateurs

## Accès Public

### URL du tableau d'affichage

Chaque course possède une URL publique basée sur son slug :

```
https://run200.fr/board/{slug-de-la-course}
```

Exemple : `https://run200.fr/board/grand-prix-nogaro-2026`

### Fonctionnalités visiteur

- **Consultation sans authentification** pour les documents publics
- **Interface mobile-first** optimisée pour les smartphones
- **Affichage par catégorie** (Règlement, Assurance, Visa FFSA, etc.)
- **Prévisualisation PDF inline** dans le navigateur
- **Téléchargement** des documents
- **Indicateur de complétude** des documents obligatoires

### QR Code

Un QR Code peut être généré pour afficher physiquement sur le circuit. L'URL est disponible via :

```php
$race->board_url // https://run200.fr/board/slug-course
```

## Administration

### Accès

- **Route** : `/admin/races/{race}/documents`
- **Permission requise** : `manage-documents` (rôle ADMIN ou STAFF_ADMINISTRATIF)

### Fonctionnalités

#### Upload de documents

1. Cliquer sur "Ajouter un document" (global) ou sur l'icône "+" d'une catégorie
2. Sélectionner la catégorie
3. Donner un titre au document
4. Uploader le fichier PDF (max 10 MB)
5. Optionnel : ajouter une description

#### Gestion du cycle de vie

| Statut | Description | Visible publiquement |
|--------|-------------|---------------------|
| **DRAFT** | Brouillon, en préparation | Non |
| **PUBLISHED** | Publié et accessible | Oui |
| **ARCHIVED** | Archivé, plus visible | Non |

Actions disponibles :
- **Publier** : Rendre le document accessible au public
- **Archiver** : Retirer de l'affichage public
- **Supprimer** : Effacer définitivement (avec versions)

#### Versioning

- Chaque mise à jour crée une nouvelle version
- L'historique des versions est conservé
- Seule la dernière version est visible publiquement

## Catégories de documents

Les catégories sont définies en base de données (seedées) :

| Catégorie | Obligatoire | Multiple | Description |
|-----------|-------------|----------|-------------|
| Règlement particulier | ✅ | Non | Règlement spécifique de la course |
| Assurance | ✅ | Non | Attestation d'assurance |
| Arrêté préfectoral | Non | Non | Autorisation préfectorale |
| Visa FFSA | ✅ | Non | Visa de la fédération |
| Visa LSAR | Non | Non | Visa ligue régionale |
| Liste des engagés | Non | Oui | Liste participants |
| Programme | Non | Non | Programme de la journée |
| Additif | Non | Oui | Additifs au règlement |
| Plan parcours | Non | Non | Tracé du circuit |
| Résultats | Non | Oui | Résultats (scratchs, catégories) |
| Autre | Non | Oui | Documents divers |

## Architecture technique

### Modèles

- `DocumentCategory` - Catégories de documents
- `RaceDocument` - Document attaché à une course
- `RaceDocumentVersion` - Versions d'un document

### Relations

```
Race
  └── hasMany: RaceDocument
        └── hasMany: RaceDocumentVersion
        └── belongsTo: DocumentCategory
```

### Stockage

- **Disk** : `race-documents` (configurable dans `config/filesystems.php`)
- **Chemin** : `races/{race_id}/{category_slug}/{uuid}.pdf`
- **Production** : Compatible S3 (AWS, MinIO, etc.)

### Sécurité

1. **Anti-énumération** : Les URLs utilisent des UUIDs, pas des IDs séquentiels
2. **Validation PDF** : 
   - Vérification MIME type
   - Vérification extension
   - Vérification magic bytes (%PDF-)
3. **Streaming sécurisé** :
   - Headers `X-Content-Type-Options: nosniff`
   - `Content-Disposition` contrôlé
   - Pas d'accès direct au filesystem
4. **Audit** : Toutes les actions sont loggées via Spatie Activity Log

## Routes

### Publiques

```php
GET /board/{race:slug}              // Tableau d'affichage
GET /board/doc/{slug}               // Prévisualiser document
GET /board/doc/{slug}/download      // Télécharger document
```

### Administration

```php
GET /admin/races/{race}/documents   // Gestion des documents
```

## Commandes Artisan

### Génération des slugs de courses

Si des courses existantes n'ont pas de slug :

```bash
php artisan races:generate-slugs
```

Pour regénérer tous les slugs :

```bash
php artisan races:generate-slugs --force
```

## API (P2 - Future)

Un endpoint API est prévu pour les intégrations externes :

```
GET /api/v1/races/{slug}/documents
```

Retourne la liste des documents publiés avec leurs URLs de téléchargement.

## Tests

### Tests unitaires

```bash
php artisan test --filter=RaceDocument
```

### Tests manuels recommandés

1. ✅ Accéder au tableau d'affichage public sans auth
2. ✅ Uploader un PDF valide (< 10MB)
3. ✅ Uploader un fichier invalide (rejet)
4. ✅ Publier/archiver un document
5. ✅ Vérifier l'affichage mobile (responsive)
6. ✅ Tester le téléchargement

## Roadmap

### P0 (Implémenté) ✅
- Upload PDF sécurisé
- Publication/archivage
- Page publique mobile-first
- Versioning automatique
- Audit trail

### P1 (À venir)
- Génération QR Code PNG
- Notifications aux pilotes inscrits lors de nouveaux documents
- Mode "inscrit uniquement" pour certains documents

### P2 (Futur)
- API REST pour intégrations
- Bulk upload (ZIP)
- Preview PDF dans l'admin
- Statistiques de téléchargement

## Fichiers créés

```
app/
├── Console/Commands/
│   └── GenerateRaceSlugsCommand.php
├── Http/Controllers/
│   └── RaceBoardController.php
├── Infrastructure/Documents/
│   └── DocumentUploadService.php
├── Livewire/
│   ├── Admin/Races/
│   │   └── Documents.php
│   └── Public/
│       └── RaceBoard.php
├── Models/
│   ├── DocumentCategory.php
│   ├── RaceDocument.php
│   └── RaceDocumentVersion.php
├── Policies/
│   └── RaceDocumentPolicy.php

database/
├── migrations/
│   ├── 2026_01_28_100000_create_document_categories_table.php
│   ├── 2026_01_28_100001_create_race_documents_table.php
│   ├── 2026_01_28_100002_create_race_document_versions_table.php
│   └── 2026_01_28_100003_add_slug_to_races_table.php
├── seeders/
│   └── DocumentCategoriesSeeder.php

resources/views/livewire/
├── admin/races/
│   └── documents.blade.php
└── public/
    └── race-board.blade.php

config/filesystems.php (modifié - ajout disk race-documents)
routes/web.php (modifié - ajout routes)
```
