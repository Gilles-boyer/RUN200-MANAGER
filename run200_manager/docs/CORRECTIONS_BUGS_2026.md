# Corrections de bugs - RUN200 Manager

## Date : 2 mars 2026

## Résumé des erreurs du log de production

| Erreur | Cause | Solution |
|--------|-------|----------|
| `MissingAppKeyException` | `.env` sans `APP_KEY` en production | Exécuter `php artisan key:generate` et configurer `.env` |
| `Command "seed" is not defined` | Mauvaise commande utilisée | Utiliser `php artisan db:seed --class=ProductionSeeder` |
| `Duplicate entry 'RACING GT'` | Seeder utilisant `create()` au lieu de `firstOrCreate()` | ✅ Corrigé - Tous les seeders utilisent des méthodes idempotentes |
| `Vite manifest not found` | Build front-end non exécuté | Exécuter `npm run build` avant déploiement |

---

## Corrections apportées

### 1. Inscription d'une deuxième voiture sur une course

**Problème :** Les pilotes ne pouvaient pas inscrire une deuxième voiture sur une course car le système bloquait toute inscription si le pilote avait déjà une inscription active.

**Fichiers modifiés :**
- [app/Livewire/Pilot/Registrations/Create.php](../app/Livewire/Pilot/Registrations/Create.php)
- [resources/views/livewire/pilot/registrations/create.blade.php](../resources/views/livewire/pilot/registrations/create.blade.php)

**Corrections :**
- Suppression de la vérification qui bloquait le pilote s'il avait déjà une inscription
- Ajout d'une vérification que la **voiture** n'est pas déjà inscrite (contrainte correcte)
- Affichage des voitures déjà inscrites avec un badge "Déjà inscrite"
- Les voitures inscrites sont grisées et non sélectionnables

---

### 2. QR Code et code d'inscription dans la liste des pilotes

**Problème :** L'administrateur ne pouvait pas voir facilement le code d'inscription ni le QR code des pilotes inscrits.

**Fichiers modifiés :**
- [app/Livewire/Staff/Registrations/Index.php](../app/Livewire/Staff/Registrations/Index.php)
- [resources/views/livewire/staff/registrations/index.blade.php](../resources/views/livewire/staff/registrations/index.blade.php)

**Corrections :**
- Ajout du code d'inscription (format: `XXX-NNNNNN-RRRR`) visible dans la colonne pilote
- Ajout d'un bouton QR Code dans les actions de chaque inscription
- Modal affichant le QR Code, le code d'inscription et les informations du pilote

---

### 3. Tri alphabétique de la liste des pilotes engagés

**Problème :** La liste n'était pas triée par ordre alphabétique.

**Fichiers modifiés :**
- [app/Livewire/Staff/Registrations/Index.php](../app/Livewire/Staff/Registrations/Index.php)

**Corrections :**
- Tri par nom de famille puis prénom (ordre alphabétique croissant)

---

### 4. Loading pour le chargement de la photo pilote

**Problème :** Aucun feedback visuel lors du chargement de la photo de profil.

**Fichiers modifiés :**
- [resources/views/livewire/pilot/profile/edit.blade.php](../resources/views/livewire/pilot/profile/edit.blade.php)

**Corrections :**
- Ajout d'un indicateur de chargement animé avec spinner
- Message "Chargement de la photo en cours..."
- L'aperçu s'affiche une fois le chargement terminé

---

### 5. Statistiques du dashboard admin

**Problème :** Les statistiques ne comptaient pas correctement tous les statuts d'inscription.

**Fichiers modifiés :**
- [app/Livewire/Admin/Dashboard.php](../app/Livewire/Admin/Dashboard.php)

**Corrections :**
- `pending_registrations` compte maintenant `SUBMITTED`, `PENDING_PAYMENT` et `PENDING_VALIDATION`
- `paymentStats['accepted']` compte tous les statuts validés (ACCEPTED jusqu'à PUBLISHED)
- `paymentStats['pending']` compte tous les statuts d'attente
- `paymentStats['refused']` compte REFUSED et CANCELLED
- Calcul du taux de conversion corrigé

---

## Actions requises pour le déploiement

### Avant le déploiement

1. **Générer la clé d'application (si manquante) :**
   ```bash
   php artisan key:generate
   ```

2. **Exécuter le build front-end :**
   ```bash
   npm install
   npm run build
   ```

3. **Vérifier le fichier .env :**
   - `APP_KEY` doit être défini
   - `APP_ENV=production`
   - `APP_DEBUG=false`

### Après le déploiement

1. **Exécuter les migrations :**
   ```bash
   php artisan migrate --force
   ```

2. **Exécuter les seeders (si nécessaire) :**
   ```bash
   php artisan db:seed --class=ProductionSeeder --force
   ```

3. **Vider les caches :**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

4. **Vérifier les permissions de stockage :**
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```

---

## Tests recommandés après déploiement

1. ✅ Connexion en tant que pilote
2. ✅ Inscription à une course avec une première voiture
3. ✅ Inscription à la même course avec une deuxième voiture
4. ✅ Vérification du QR code dans l'interface staff
5. ✅ Upload d'une photo de profil (vérifier le spinner de chargement)
6. ✅ Vérification des statistiques du dashboard admin
7. ✅ Vérification du tri alphabétique dans la liste staff

---

## Structure des codes d'inscription

Format : `XXX-NNNNNN-RRRR`

- `XXX` : 3 premières lettres du nom de la course (majuscules)
- `NNNNNN` : Numéro de licence du pilote (padded avec des zéros)
- `RRRR` : ID de l'inscription (4 chiffres, padded)

Exemple : `RUN-001234-0042`
