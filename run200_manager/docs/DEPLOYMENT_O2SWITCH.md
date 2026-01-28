# 🚀 Guide de Déploiement - RUN200 Manager sur o2switch

> **Version:** 1.0.0  
> **Date:** Janvier 2026  
> **Environnement:** Production (o2switch mutualisé + cPanel)  
> **Stack:** Laravel 12 + Livewire 4 + Tailwind 4 + MySQL + Stripe

---

## 📋 Table des matières

1. [Prérequis](#-prérequis)
2. [Architecture de déploiement](#-architecture-de-déploiement)
3. [Préparation locale](#-préparation-locale)
4. [Configuration o2switch](#-configuration-o2switch)
5. [Déploiement avec SSH](#-déploiement-avec-ssh-recommandé)
6. [Déploiement sans SSH](#-déploiement-sans-ssh-alternative)
7. [Configuration post-déploiement](#-configuration-post-déploiement)
8. [Variables d'environnement](#-variables-denvironnement)
9. [Tâches planifiées (Cron)](#-tâches-planifiées-cron)
10. [Gestion des queues](#-gestion-des-queues)
11. [Maintenance et mises à jour](#-maintenance-et-mises-à-jour)
12. [Rollback](#-procédure-de-rollback)
13. [Dépannage](#-dépannage)
14. [Checklist de déploiement](#-checklist-de-déploiement)

---

## 📦 Prérequis

### Côté serveur (o2switch)

| Élément | Requis | Vérification |
|---------|--------|--------------|
| PHP | 8.2+ | cPanel > Sélecteur PHP |
| Extensions PHP | Voir liste ci-dessous | cPanel > Sélecteur PHP > Extensions |
| MySQL | 5.7+ / MariaDB 10.3+ | ✅ Inclus o2switch |
| Composer | 2.x | Via SSH ou Terminal cPanel |
| Espace disque | ~500 Mo minimum | cPanel > Utilisation disque |
| SSL | Let's Encrypt / AutoSSL | cPanel > SSL/TLS |

### Extensions PHP requises

```
bcmath, ctype, curl, dom, fileinfo, gd, json, mbstring, 
openssl, pdo, pdo_mysql, tokenizer, xml, zip
```

### Côté local (développement)

- PHP 8.2+
- Composer 2.x
- Node.js 18+ & npm
- Git (optionnel mais recommandé)

---

## 🏗 Architecture de déploiement

### Structure recommandée (Option A - Sécurisée)

```
/home/USERNAME/
│
├── laravel_app/                    # ⚠️ HORS public_html (sécurisé)
│   ├── app/
│   ├── bootstrap/
│   │   └── cache/                  # chmod 775
│   ├── config/
│   ├── database/
│   ├── lang/
│   ├── resources/
│   ├── routes/
│   ├── storage/                    # chmod 775 récursif
│   │   ├── app/
│   │   │   ├── private/
│   │   │   ├── public/
│   │   │   └── race-documents/
│   │   ├── framework/
│   │   │   ├── cache/
│   │   │   ├── sessions/
│   │   │   └── views/
│   │   └── logs/
│   ├── vendor/
│   ├── .env                        # ⚠️ Jamais dans Git
│   ├── artisan
│   └── composer.json
│
└── public_html/                    # Document root
    └── run200/                     # Sous-domaine: run200.votredomaine.fr
        ├── .htaccess
        ├── index.php               # Modifié (voir ci-dessous)
        ├── robots.txt
        ├── favicon.ico
        ├── favicon.svg
        ├── apple-touch-icon.png
        ├── build/                  # Assets Vite compilés
        │   ├── assets/
        │   └── manifest.json
        ├── images/
        │   └── logorun200.svg
        └── storage/                # Symlink → ../../laravel_app/storage/app/public
```

### Pourquoi cette structure ?

1. **Sécurité** : Le code PHP, `.env`, et `vendor/` sont hors de `public_html`
2. **Performance** : Seuls les assets publics sont servis directement
3. **Maintenance** : Facilite les mises à jour et rollbacks

---

## 💻 Préparation locale

### 1. Build des assets

```bash
# Dans le répertoire du projet
npm install
npm run build

# Vérifier que le build est complet
ls -la public/build/
# Doit contenir: assets/, manifest.json
```

### 2. Optimisation Composer

```bash
# Installation production (sans dev dependencies)
composer install --no-dev --optimize-autoloader
```

### 3. Préparation du .env de production

Créer un fichier `.env.production` (ne pas commiter) :

```bash
cp .env.example .env.production
# Éditer avec les valeurs de production
```

### 4. Créer l'archive de déploiement

```bash
# Option A: Via Git (recommandé)
git archive --format=zip HEAD -o deploy.zip

# Option B: Archive manuelle (exclure node_modules, .git, tests)
zip -r deploy.zip . -x "node_modules/*" -x ".git/*" -x "tests/*" -x "*.log"
```

---

## ⚙️ Configuration o2switch

### 1. Créer la base de données MySQL

1. Connexion cPanel → **Bases de données MySQL**
2. Créer une base : `USERNAME_run200`
3. Créer un utilisateur : `USERNAME_run200user`
4. Mot de passe : **générer un mot de passe fort**
5. Ajouter l'utilisateur à la base avec **TOUS LES PRIVILÈGES**

> ⚠️ **Note o2switch** : Le préfixe `USERNAME_` est automatiquement ajouté.

### 2. Configurer le sous-domaine

1. cPanel → **Sous-domaines**
2. Créer : `run200.votredomaine.fr`
3. Racine : `public_html/run200`
4. Créer le dossier si demandé

### 3. Configurer PHP

1. cPanel → **Sélecteur PHP** (ou MultiPHP Manager)
2. Sélectionner le domaine/sous-domaine
3. Version : **PHP 8.2** (ou 8.3 si disponible)
4. Extensions à activer :
   - `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`
   - `gd`, `intl`, `json`, `mbstring`, `openssl`
   - `pdo`, `pdo_mysql`, `tokenizer`, `xml`, `zip`

### 4. Configurer les options PHP

cPanel → **Options PHP** ou `php.ini` personnalisé :

```ini
memory_limit = 256M
max_execution_time = 120
max_input_time = 120
post_max_size = 64M
upload_max_filesize = 32M
max_file_uploads = 20
```

### 5. Activer SSL

1. cPanel → **SSL/TLS** → **Gérer les sites SSL**
2. Installer certificat Let's Encrypt / AutoSSL
3. Forcer HTTPS (via `.htaccess` ou option cPanel)

### 6. Activer l'accès SSH (recommandé)

1. cPanel → **Accès SSH** ou **Terminal**
2. Générer une paire de clés ou utiliser le Terminal intégré

---

## 🔐 Déploiement avec SSH (Recommandé)

### Étape 1 : Connexion SSH

```bash
ssh USERNAME@SERVEUR.o2switch.net
# Ou utiliser le Terminal cPanel
```

### Étape 2 : Préparer la structure

```bash
cd ~

# Créer le dossier de l'application
mkdir -p laravel_app

# Créer le dossier public
mkdir -p public_html/run200
```

### Étape 3 : Upload et extraction

**Option A - Git (si disponible)**
```bash
cd ~/laravel_app
git clone https://github.com/VOTRE-REPO/run200-manager.git .
```

**Option B - Upload ZIP**
```bash
# Upload deploy.zip via File Manager ou SCP
cd ~
unzip deploy.zip -d laravel_app
```

### Étape 4 : Installation des dépendances

```bash
cd ~/laravel_app

# Installer Composer si nécessaire (o2switch le fournit généralement)
composer install --no-dev --optimize-autoloader
```

### Étape 5 : Configuration environnement

```bash
# Copier et éditer le fichier .env
cp .env.example .env
nano .env
```

Configurer toutes les variables (voir section [Variables d'environnement](#-variables-denvironnement)).

### Étape 6 : Générer la clé d'application

```bash
php artisan key:generate
```

### Étape 7 : Migrations et initialisation base de données

```bash
# ⚠️ ATTENTION : --force est requis en production
php artisan migrate --force

# Initialiser avec le seeder PRODUCTION (crée admin + données de référence)
# ⚠️ NE PAS utiliser db:seed sans --class en production !
php artisan db:seed --class=ProductionSeeder --force
```

> **Important** : Le `ProductionSeeder` crée :
> - Les rôles et permissions
> - Les catégories de voitures
> - Les checkpoints
> - Les catégories de documents
> - Le compte administrateur (configurable via `.env`)

### Étape 8 : Créer les caches d'optimisation

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### Étape 9 : Configurer le dossier public

```bash
# Copier les fichiers publics
cp -r ~/laravel_app/public/* ~/public_html/run200/

# Modifier index.php pour pointer vers laravel_app
nano ~/public_html/run200/index.php
```

**Contenu de `index.php` modifié :**

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Chemin vers l'application Laravel (hors public_html)
$laravelPath = '/home/USERNAME/laravel_app';

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $laravelPath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $laravelPath.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $laravelPath.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
```

> ⚠️ **Remplacer `USERNAME`** par votre nom d'utilisateur o2switch.

### Étape 10 : Créer le lien symbolique storage

```bash
# Méthode 1 : Via Artisan (si symlinks autorisés)
cd ~/laravel_app
php artisan storage:link --relative

# Méthode 2 : Manuellement
cd ~/public_html/run200
ln -s ../../laravel_app/storage/app/public storage
```

**Si les symlinks sont bloqués**, voir [Alternative sans symlink](#alternative-sans-symlink).

### Étape 11 : Configurer les permissions

```bash
cd ~/laravel_app

# Permissions sur storage
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# S'assurer que le propriétaire est correct
chown -R USERNAME:USERNAME storage bootstrap/cache
```

### Étape 12 : Vérification

```bash
# Tester l'application
php artisan about

# Vérifier les routes
php artisan route:list --compact

# Tester la connexion BDD
php artisan tinker
>>> DB::connection()->getPdo();
>>> exit
```

---

## 📁 Déploiement sans SSH (Alternative)

Si SSH n'est pas disponible, suivre cette procédure.

### Préparation locale complète

```bash
# 1. Installer les dépendances
composer install --no-dev --optimize-autoloader

# 2. Build assets
npm run build

# 3. Créer les caches (config locale temporaire)
cp .env.example .env
# Éditer .env avec APP_ENV=production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Note: Ces caches devront être régénérés après config serveur
```

### Upload via File Manager

1. **Créer l'archive** du projet (sans `node_modules`, `.git`)
2. **cPanel → File Manager**
3. **Upload** `deploy.zip` dans `/home/USERNAME/`
4. **Extraire** dans `laravel_app`
5. **Copier** `laravel_app/public/*` vers `public_html/run200/`
6. **Modifier** `public_html/run200/index.php` (voir étape 9 SSH)

### Configuration via File Manager

1. **Éditer** `/home/USERNAME/laravel_app/.env`
2. **Configurer** toutes les variables de production
3. **Sauvegarder**

### Migrations via endpoint temporaire

Créer un fichier temporaire pour les commandes Artisan :

**`public_html/run200/deploy-temp.php`** (À SUPPRIMER APRÈS UTILISATION)

```php
<?php
/**
 * FICHIER TEMPORAIRE DE DÉPLOIEMENT
 * ⚠️ SUPPRIMER IMMÉDIATEMENT APRÈS UTILISATION
 * 
 * Accès : https://run200.votredomaine.fr/deploy-temp.php?key=VOTRE_CLE_SECRETE&action=ACTION
 */

// Clé de sécurité (générer une clé unique)
$secretKey = 'GENERER_UNE_CLE_ALEATOIRE_LONGUE_ICI';

if (!isset($_GET['key']) || $_GET['key'] !== $secretKey) {
    http_response_code(403);
    die('Forbidden');
}

$laravelPath = '/home/USERNAME/laravel_app';
require $laravelPath . '/vendor/autoload.php';
$app = require_once $laravelPath . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$action = $_GET['action'] ?? 'status';

echo "<pre>";
echo "=== RUN200 Deploy Tool ===\n\n";

switch ($action) {
    case 'migrate':
        echo "Running migrations...\n";
        $kernel->call('migrate', ['--force' => true]);
        echo $kernel->output();
        break;
        
    case 'cache':
        echo "Creating caches...\n";
        $kernel->call('config:cache');
        echo $kernel->output();
        $kernel->call('route:cache');
        echo $kernel->output();
        $kernel->call('view:cache');
        echo $kernel->output();
        break;
        
    case 'clear':
        echo "Clearing caches...\n";
        $kernel->call('cache:clear');
        echo $kernel->output();
        $kernel->call('config:clear');
        echo $kernel->output();
        break;
        
    case 'key':
        echo "Generating app key...\n";
        $kernel->call('key:generate', ['--force' => true]);
        echo $kernel->output();
        break;
        
    case 'storage':
        echo "Creating storage link...\n";
        $kernel->call('storage:link');
        echo $kernel->output();
        break;
        
    case 'status':
        echo "Application Status:\n";
        $kernel->call('about');
        echo $kernel->output();
        break;
        
    default:
        echo "Actions disponibles: migrate, cache, clear, key, storage, status\n";
}

echo "\n⚠️ SUPPRIMER CE FICHIER APRÈS UTILISATION !\n";
echo "</pre>";
```

**Utilisation :**
1. Accéder à `https://run200.votredomaine.fr/deploy-temp.php?key=VOTRE_CLE&action=key`
2. Puis `?action=migrate`
3. Puis `?action=cache`
4. **SUPPRIMER** `deploy-temp.php` immédiatement !

---

## 🔧 Configuration post-déploiement

### Configurer le webhook Stripe

1. Connectez-vous au [Dashboard Stripe](https://dashboard.stripe.com)
2. Allez dans **Développeurs → Webhooks**
3. Créer un endpoint :
   - URL : `https://run200.votredomaine.fr/stripe/webhook`
   - Événements à écouter :
     - `checkout.session.completed`
     - `payment_intent.succeeded`
     - `payment_intent.payment_failed`
4. Copier le **Signing secret** (`whsec_...`)
5. Mettre à jour `.env` : `STRIPE_WEBHOOK_SECRET=whsec_...`
6. Régénérer le cache : `php artisan config:cache`

### Configurer l'email o2switch

1. cPanel → **Comptes de messagerie**
2. Créer : `noreply@votredomaine.fr`
3. Noter le mot de passe
4. Configurer `.env` :

```env
MAIL_MAILER=smtp
MAIL_HOST=mail.votredomaine.fr
MAIL_PORT=465
MAIL_USERNAME=noreply@votredomaine.fr
MAIL_PASSWORD=motdepasse_email
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=noreply@votredomaine.fr
MAIL_FROM_NAME="RUN200 Manager"
```

### Tester l'envoi d'email

```bash
php artisan tinker
>>> Mail::raw('Test email', fn($m) => $m->to('votre@email.fr')->subject('Test'));
```

---

## 📝 Variables d'environnement

### Fichier `.env` de production complet

```env
#------------------------------------------------------------------------------
# APPLICATION
#------------------------------------------------------------------------------
APP_NAME="RUN200 Manager"
APP_ENV=production
APP_KEY=base64:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
APP_DEBUG=false
APP_TIMEZONE=Indian/Reunion
APP_URL=https://run200.votredomaine.fr

APP_LOCALE=fr
APP_FALLBACK_LOCALE=fr
APP_FAKER_LOCALE=fr_FR

#------------------------------------------------------------------------------
# COMPTE ADMINISTRATEUR (pour ProductionSeeder)
#------------------------------------------------------------------------------
ADMIN_EMAIL=admin@votredomaine.fr
ADMIN_PASSWORD=MotDePasseSecurise123!
ADMIN_NAME="Administrateur RUN200"

#------------------------------------------------------------------------------
# MAINTENANCE
#------------------------------------------------------------------------------
APP_MAINTENANCE_DRIVER=file

#------------------------------------------------------------------------------
# SÉCURITÉ
#------------------------------------------------------------------------------
BCRYPT_ROUNDS=12

#------------------------------------------------------------------------------
# LOGGING
#------------------------------------------------------------------------------
LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

#------------------------------------------------------------------------------
# BASE DE DONNÉES
#------------------------------------------------------------------------------
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=USERNAME_run200
DB_USERNAME=USERNAME_run200user
DB_PASSWORD=motdepasse_securise_bdd
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

#------------------------------------------------------------------------------
# SESSIONS
#------------------------------------------------------------------------------
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_PATH=/
SESSION_DOMAIN=run200.votredomaine.fr
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

#------------------------------------------------------------------------------
# CACHE & QUEUE
#------------------------------------------------------------------------------
CACHE_STORE=database
QUEUE_CONNECTION=database
BROADCAST_CONNECTION=log

#------------------------------------------------------------------------------
# FILESYSTEM
#------------------------------------------------------------------------------
FILESYSTEM_DISK=local

#------------------------------------------------------------------------------
# EMAIL (o2switch SMTP)
#------------------------------------------------------------------------------
MAIL_MAILER=smtp
MAIL_HOST=mail.votredomaine.fr
MAIL_PORT=465
MAIL_USERNAME=noreply@votredomaine.fr
MAIL_PASSWORD=motdepasse_email
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=noreply@votredomaine.fr
MAIL_FROM_NAME="RUN200 Manager"

#------------------------------------------------------------------------------
# STRIPE (PRODUCTION)
#------------------------------------------------------------------------------
STRIPE_KEY=pk_live_votre_cle_publique_ici
STRIPE_SECRET=sk_live_votre_cle_secrete_ici
STRIPE_WEBHOOK_SECRET=whsec_votre_webhook_secret_ici
STRIPE_CURRENCY=EUR
STRIPE_REGISTRATION_FEE_CENTS=5000
STRIPE_TEST_MODE=false

#------------------------------------------------------------------------------
# VITE
#------------------------------------------------------------------------------
VITE_APP_NAME="${APP_NAME}"
```

### Variables critiques à ne jamais exposer

| Variable | Importance |
|----------|------------|
| `APP_KEY` | 🔴 Critique - Chiffrement |
| `DB_PASSWORD` | 🔴 Critique - Accès BDD |
| `STRIPE_SECRET` | 🔴 Critique - Paiements |
| `STRIPE_WEBHOOK_SECRET` | 🔴 Critique - Webhooks |
| `MAIL_PASSWORD` | 🟠 Important - Emails |

---

## ⏰ Tâches planifiées (Cron)

### Configuration cron o2switch

1. cPanel → **Tâches Cron**
2. Ajouter une nouvelle tâche :

**Commande :**
```
/usr/local/bin/php /home/USERNAME/laravel_app/artisan schedule:run >> /dev/null 2>&1
```

**Fréquence :** Toutes les minutes (`* * * * *`)

### Tâches planifiées actuelles

| Commande | Fréquence | Description |
|----------|-----------|-------------|
| `send:race-reminders --days=3` | Tous les jours à 09:00 | Rappels J-3 aux pilotes |
| `send:tech-reminders` | Tous les jours à 10:00 | Rappels contrôle technique |

### Vérifier que le scheduler fonctionne

```bash
# Manuellement
php artisan schedule:list

# Test d'exécution
php artisan schedule:run
```

---

## 📬 Gestion des queues

### Configuration recommandée (mutualisé)

Sur hébergement mutualisé **sans supervisor**, utiliser une des stratégies suivantes :

#### Option 1 : Queue synchrone (Simple)

```env
QUEUE_CONNECTION=sync
```

Les jobs s'exécutent immédiatement. Peut ralentir les requêtes utilisateur.

#### Option 2 : Queue database + Cron worker (Recommandé)

```env
QUEUE_CONNECTION=database
```

Ajouter un cron pour traiter les jobs :

```
* * * * * /usr/local/bin/php /home/USERNAME/laravel_app/artisan queue:work --stop-when-empty --max-time=50 >> /dev/null 2>&1
```

#### Option 3 : Traitement via scheduler

Dans `routes/console.php`, ajouter :

```php
Schedule::command('queue:work --stop-when-empty --max-time=50')
    ->everyMinute()
    ->withoutOverlapping();
```

### Jobs du projet

| Job | Usage | Priorité |
|-----|-------|----------|
| `SendBulkEmailJob` | Emails en masse | Normal |
| `ImportRaceResultsJob` | Import résultats | Normal |
| `RebuildSeasonStandingsJob` | Calcul classements | Basse |

---

## 🔄 Maintenance et mises à jour

### Procédure de mise à jour standard

```bash
# 1. Activer le mode maintenance
php artisan down --secret="acces-maintenance-secret"

# 2. Backup base de données (via cPanel ou mysqldump)
mysqldump -u USERNAME_run200user -p USERNAME_run200 > backup_$(date +%Y%m%d_%H%M%S).sql

# 3. Pull des modifications (si Git)
git pull origin main

# 4. Ou upload des nouveaux fichiers

# 5. Mise à jour dépendances
composer install --no-dev --optimize-autoloader

# 6. Migrations
php artisan migrate --force

# 7. Recréer les caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 8. Mise à jour assets (si nécessaire)
# Uploader le nouveau public/build/ 

# 9. Désactiver le mode maintenance
php artisan up
```

### Mode maintenance avec accès secret

```bash
# Activer avec secret
php artisan down --secret="mon-secret-maintenance-2026"

# Accéder au site en maintenance
https://run200.votredomaine.fr/mon-secret-maintenance-2026

# Désactiver
php artisan up
```

---

## ⏪ Procédure de Rollback

### En cas de problème après déploiement

#### 1. Rollback rapide (fichiers)

```bash
# Renommer les dossiers
mv ~/laravel_app ~/laravel_app_failed_$(date +%Y%m%d)
mv ~/laravel_app_backup ~/laravel_app

# Vider les caches
php artisan cache:clear
php artisan config:clear
```

#### 2. Rollback base de données

```bash
# Restaurer le backup
mysql -u USERNAME_run200user -p USERNAME_run200 < backup_YYYYMMDD_HHMMSS.sql

# Ou rollback de la dernière migration
php artisan migrate:rollback --step=1
```

#### 3. Rollback complet

1. Restaurer les fichiers depuis backup
2. Restaurer la BDD depuis backup
3. Vider tous les caches
4. Régénérer les caches

### Stratégie de backup recommandée

| Élément | Fréquence | Rétention |
|---------|-----------|-----------|
| BDD complète | Quotidien | 7 jours |
| Fichiers storage/ | Hebdomadaire | 4 semaines |
| Code source | À chaque déploiement | 3 versions |

---

## �️ Gestion des données

### Purger les données de démonstration/test

Si vous avez utilisé le `DatabaseSeeder` complet (avec données de démo) pendant les tests, vous pouvez purger ces données tout en conservant les données de référence :

```bash
# ⚠️ ATTENTION : Cette commande supprime TOUTES les données utilisateur !

# Version interactive (avec confirmation)
php artisan data:purge-demo

# Version non-interactive (scripts, CI/CD)
php artisan data:purge-demo --force

# Conserver le compte admin
php artisan data:purge-demo --keep-admin

# Conserver les saisons (avec courses)
php artisan data:purge-demo --keep-seasons

# Combinaison
php artisan data:purge-demo --force --keep-admin --keep-seasons
```

### Données supprimées par la commande

| Modèle | Description |
|--------|-------------|
| `Notification` | Toutes les notifications |
| `Activity` | Journal d'activités |
| `PaddockReservation` | Réservations paddock |
| `EngagementPayment` | Paiements d'engagements |
| `EngagementCarDocument` | Documents véhicules |
| `EngagementDocument` | Documents engagements |
| `Engagement` | Engagements (inscriptions) |
| `CautionPayment` | Paiements de cautions |
| `Caution` | Cautions |
| `Car` | Véhicules |
| `TechnicalControlHistory` | Historique contrôle technique |
| `TechnicalControl` | Contrôles techniques |
| `Result` | Résultats |
| `Timing` | Chronos |
| `Race` | Courses (si `--keep-seasons` non utilisé) |
| `Season` | Saisons (si `--keep-seasons` non utilisé) |
| `Pilot` | Pilotes |
| `User` | Utilisateurs (sauf admin si `--keep-admin`) |

### Réinitialisation complète en production

```bash
# ⚠️ DANGER - Supprime TOUT et recrée la structure
php artisan migrate:fresh --force

# Réinitialiser avec données de production uniquement
php artisan db:seed --class=ProductionSeeder --force
```

---

## �🔧 Dépannage

### Erreurs courantes et solutions

#### 500 Internal Server Error

```bash
# Vérifier les logs
tail -f ~/laravel_app/storage/logs/laravel.log

# Vérifier les permissions
chmod -R 775 storage bootstrap/cache

# Vérifier le .env
php artisan config:clear
```

#### Page blanche

```bash
# Activer temporairement le debug
# .env : APP_DEBUG=true

# Vérifier les logs Apache
cat ~/logs/error.log | tail -50
```

#### SQLSTATE[HY000] Connection refused

```bash
# Vérifier les infos BDD dans .env
# Tester la connexion
php artisan tinker
>>> DB::connection()->getPdo();
```

#### Class not found

```bash
# Régénérer l'autoloader
composer dump-autoload --optimize

# Vider les caches
php artisan cache:clear
php artisan config:clear
```

#### Symlink storage non fonctionnel

Voir section [Alternative sans symlink](#alternative-sans-symlink).

#### Permissions denied

```bash
# Vérifier le propriétaire
ls -la storage/

# Corriger
chown -R USERNAME:USERNAME storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

#### Sessions/Cache non persistants

```bash
# Vérifier que les tables existent
php artisan migrate:status

# Recréer si nécessaire
php artisan session:table
php artisan cache:table
php artisan migrate
```

#### CSRF Token Mismatch

```bash
# Vérifier SESSION_DOMAIN dans .env
# Doit correspondre au domaine exact

# Vider les sessions
php artisan session:clear
```

### Alternative sans symlink

Si les symlinks sont bloqués sur o2switch :

**Option 1 : Copie manuelle**

```bash
# Au lieu de storage:link, copier les fichiers
cp -r ~/laravel_app/storage/app/public/* ~/public_html/run200/storage/

# À refaire après chaque upload de fichier
```

**Option 2 : Route personnalisée**

Créer une route pour servir les fichiers storage :

```php
// routes/web.php
Route::get('/storage/{path}', function ($path) {
    $file = storage_path('app/public/' . $path);
    
    if (!file_exists($file)) {
        abort(404);
    }
    
    return response()->file($file);
})->where('path', '.*')->name('storage.serve');
```

**Option 3 : .htaccess redirect**

```apache
# public_html/run200/.htaccess
RewriteRule ^storage/(.*)$ /home/USERNAME/laravel_app/storage/app/public/$1 [L]
```

---

## ✅ Checklist de déploiement

### Avant déploiement

- [ ] Tests locaux passent (`php artisan test`)
- [ ] Build assets (`npm run build`)
- [ ] `.env.example` à jour
- [ ] Migrations testées localement
- [ ] Backup code actuel (si mise à jour)
- [ ] Backup BDD (si mise à jour)

### Configuration serveur

- [ ] Base de données MySQL créée
- [ ] Utilisateur BDD avec privilèges
- [ ] Sous-domaine configuré
- [ ] SSL activé (HTTPS)
- [ ] PHP 8.2+ sélectionné
- [ ] Extensions PHP activées
- [ ] Accès SSH activé (optionnel)

### Déploiement

- [ ] Fichiers uploadés/clonés
- [ ] `.env` configuré (toutes les variables)
- [ ] `composer install --no-dev`
- [ ] `php artisan key:generate`
- [ ] `php artisan migrate --force`
- [ ] `php artisan config:cache`
- [ ] `php artisan route:cache`
- [ ] `php artisan view:cache`
- [ ] Storage link créé
- [ ] Permissions 775 sur storage/
- [ ] Permissions 775 sur bootstrap/cache/

### Post-déploiement

- [ ] Site accessible en HTTPS
- [ ] Connexion/Inscription fonctionne
- [ ] Cron scheduler configuré
- [ ] Webhook Stripe configuré
- [ ] Test envoi email
- [ ] Test génération PDF
- [ ] Test paiement Stripe (mode test puis live)
- [ ] Logs accessibles et sans erreurs
- [ ] Mode debug désactivé (`APP_DEBUG=false`)

### Monitoring

- [ ] Vérifier logs quotidiennement (première semaine)
- [ ] Surveiller espace disque
- [ ] Vérifier exécution des crons
- [ ] Tester les notifications email

---

## 📞 Support

### Contacts utiles

| Service | Contact |
|---------|---------|
| Support o2switch | support@o2switch.fr |
| Documentation Laravel | https://laravel.com/docs |
| Status Stripe | https://status.stripe.com |

### Logs importants

```bash
# Logs Laravel
~/laravel_app/storage/logs/laravel.log

# Logs Apache (si accessible)
~/logs/error.log

# Logs cron
~/logs/cron.log
```

---

## 📚 Ressources

- [Documentation o2switch](https://faq.o2switch.fr/)
- [Laravel 12 Documentation](https://laravel.com/docs/12.x)
- [Livewire Documentation](https://livewire.laravel.com/docs)
- [Stripe Documentation](https://stripe.com/docs)

---

> **Document maintenu par** : Équipe RUN200  
> **Dernière mise à jour** : Janvier 2026
