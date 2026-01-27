# RUN200 MANAGER 🏁

Application web de gestion complète pour l'organisation de courses automobiles Run200.

[![Tests](https://img.shields.io/badge/tests-393%20passing-success)](tests/)
[![Assertions](https://img.shields.io/badge/assertions-912-success)](tests/)
[![Laravel](https://img.shields.io/badge/Laravel-12-red)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-4-purple)](https://livewire.laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue)](https://php.net)
[![Status](https://img.shields.io/badge/status-Production%20Ready-brightgreen)](docs/)

---

## 📋 À propos

Run200 Manager est une application terrain (mobile-first) pour dématérialiser le workflow complet des courses automobiles :
- 📝 Gestion des pilotes et véhicules avec licence et permis
- 🎟️ Inscriptions aux courses avec paiement Stripe
- ✅ Checkpoints terrain via QR codes sécurisés
- 🔧 Contrôle technique avec validation/refus
- 📄 Fiche d'engagement PDF avec signatures électroniques
- 🏆 Import CSV et publication des résultats
- 🥇 Calcul automatique du championnat (général + catégories)

---

## 🚀 Installation

### Prérequis
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8+ (ou SQLite pour dev)

### Étapes

```bash
# 1. Cloner le projet
git clone <url-repo>
cd run200_manager

# 2. Installer les dépendances
composer install
npm install

# 3. Configuration
cp .env.example .env
php artisan key:generate

# 4. Base de données
php artisan migrate:fresh --seed

# 5. Build assets
npm run build

# 6. Lancer l'application
php artisan serve
```

L'application est accessible sur `http://localhost:8000`

---

## 👥 Comptes de test

Après le seed, 3 comptes sont créés :

| Email | Mot de passe | Rôle |
|-------|--------------|------|
| admin@run200.com | password | ADMIN |
| pilot@run200.com | password | PILOTE |
| staff@run200.com | password | STAFF_ADMINISTRATIF |

---

## 🧪 Tests

```bash
# Tous les tests
php artisan test

# Tests spécifiques
php artisan test --filter=Rbac
php artisan test --filter=Auth

# Avec coverage
php artisan test --coverage
```

**Statut actuel** : 393 tests / 912 assertions ✅

---

## 📚 Documentation

**🔍 [INDEX COMPLET DE LA DOCUMENTATION](docs/INDEX.md)** ← Commencez ici !

### Documents principaux
- [📋 Information Projet](docs/information_projet.md) - Spécifications complètes métier
- [📈 État & Plan de développement](docs/etat_et_plan_developpement.md) - Historique des sprints
- [📖 Documentation Complète](docs/documentation_complete.md) - **Documentation technique complète**
- [🚀 Évolutions & Roadmap](docs/evolutions_et_roadmap.md) - **Fonctionnalités futures et améliorations**
- [🔧 Guide Technique Développeur](docs/guide_technique_developpeur.md) - **Guide pratique pour développeurs**
- [✨ Bonnes pratiques](docs/bonne_pratique.md) - Standards de code Laravel
- [✅ Rapport Phase 0](docs/phase0_rapport.md) - RBAC complété

---

## 🏗️ Architecture

### Stack technique
- **Backend** : Laravel 12 + Fortify
- **Frontend** : Livewire 4 + Flux UI + TailwindCSS 4
- **RBAC** : Spatie Permission
- **Audit** : Spatie Activity Log
- **Tests** : Pest 3

### Structure Clean Architecture

```
app/
├── Domain/              # Règles métier pures
│   ├── Registration/
│   ├── Pilot/
│   ├── Car/
│   └── Championship/
├── Application/         # Use Cases
│   ├── Registrations/
│   ├── Results/
│   └── Championship/
├── Infrastructure/      # Services externes
│   ├── Qr/
│   ├── Import/
│   └── Payments/
└── Http/               # Controllers & Routes
```

---

## 🔐 RBAC (Rôles & Permissions)

### 6 Rôles définis

1. **PILOTE** - Gérer profil, voitures, inscriptions
2. **STAFF_ADMINISTRATIF** - Validation inscriptions, paddock
3. **CONTROLEUR_TECHNIQUE** - Contrôles techniques
4. **STAFF_ENTREE** - Scan checkpoint entrée
5. **STAFF_SONO** - Distribution bracelets
6. **ADMIN** - Accès complet

### 34 Permissions granulaires

Organisées par domaine : pilot, car, race, registration, checkpoint, results, championship, admin.

Voir `database/seeders/RolesAndPermissionsSeeder.php` pour le détail.

---

## 📊 État du projet

### Phase 0 (Sprint 0) - ✅ COMPLÉTÉ
- [x] RBAC complet (6 rôles + 34 permissions)
- [x] Architecture Clean mise en place
- [x] Audit log configuré
- [x] 14 tests RBAC validés

### Phase 1 (Sprint 1) - 🚧 EN COURS
- [ ] Migrations pilotes/voitures
- [ ] Models Eloquent
- [ ] ValueObjects (License, RaceNumber)
- [ ] Form Requests & Policies
- [ ] UI Livewire

**Avancement global** : 10% complété

---

## 🛠️ Commandes utiles

```bash
# Développement
composer dev              # Lance server + queue + vite
composer lint             # Formater le code (Pint)
composer test             # Lancer les tests

# Base de données
php artisan migrate:fresh --seed
php artisan db:seed --class=RolesAndPermissionsSeeder

# Cache
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## 📖 Routes principales

### Public
- `/` - Page d'accueil (redirige selon rôle si connecté)

### Pilote
- `/pilot/home` - Dashboard pilote
- `/pilot/profile` - Profil (à venir)
- `/pilot/cars` - Mes voitures (à venir)
- `/pilot/races` - Courses disponibles (à venir)

### Staff
- `/staff/home` - Dashboard staff
- `/staff/registrations` - Liste inscriptions (à venir)
- `/staff/scan/*` - Checkpoints QR (à venir)

### Admin
- `/admin/home` - Dashboard admin
- `/admin/seasons` - Gestion saisons (à venir)
- `/admin/races` - Gestion courses (à venir)
- `/admin/championship` - Championnat (à venir)

---

## 🐛 Résolution de problèmes

### Base de données
Si erreur de connexion MySQL, le projet utilise SQLite par défaut en développement.

Pour utiliser MySQL :
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=run200manager
DB_USERNAME=root
DB_PASSWORD=votre_password
```

### Permissions
Si erreur "Permission denied" sur les routes :
```bash
php artisan permission:cache-reset
php artisan optimize:clear
```

### Tests échouent
```bash
php artisan test:lint  # Vérifier le code
php artisan config:clear
php artisan test
```

---

## 🤝 Contribution

Ce projet suit des conventions strictes :

1. **Code style** : Laravel Pint (obligatoire)
2. **Tests** : Pest (obligatoires pour Use Cases)
3. **Architecture** : Clean Architecture (Domain/Application/Infrastructure)
4. **Commits** : Messages descriptifs en français

Voir [docs/bonne_pratique.md](docs/bonne_pratique.md) pour les détails.

---

## 📄 Licence

Projet propriétaire - ASA CFG © 2026

---

## 📞 Contact

Pour toute question sur le projet, consulter la documentation dans `/docs`.

**Version** : 1.0.0-dev  
**Date** : Janvier 2026  
**Status** : 🚧 En développement actif
