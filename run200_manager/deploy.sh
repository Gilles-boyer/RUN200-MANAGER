#!/bin/bash
#------------------------------------------------------------------------------
# RUN200 MANAGER - Script de déploiement o2switch
#------------------------------------------------------------------------------
# Usage: ./deploy.sh [action]
# Actions: deploy, update, rollback, cache, status
#------------------------------------------------------------------------------

set -e

# Configuration - À MODIFIER
APP_PATH="/home/USERNAME/laravel_app"
PUBLIC_PATH="/home/USERNAME/public_html/run200"
BACKUP_PATH="/home/USERNAME/backups"
PHP_BIN="/usr/local/bin/php"

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Fonctions utilitaires
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Vérifier que le chemin existe
check_paths() {
    if [ ! -d "$APP_PATH" ]; then
        log_error "Le chemin APP_PATH n'existe pas: $APP_PATH"
        exit 1
    fi
}

# Créer un backup
backup() {
    log_info "Création du backup..."
    TIMESTAMP=$(date +%Y%m%d_%H%M%S)

    mkdir -p "$BACKUP_PATH"

    # Backup base de données
    if command -v mysqldump &> /dev/null; then
        log_info "Backup base de données..."
        cd "$APP_PATH"
        $PHP_BIN artisan db:backup 2>/dev/null || log_warn "db:backup non disponible, utiliser mysqldump manuellement"
    fi

    # Backup fichiers
    log_info "Backup fichiers (vendor exclu)..."
    tar --exclude='vendor' --exclude='node_modules' -czf "$BACKUP_PATH/app_$TIMESTAMP.tar.gz" -C "$(dirname $APP_PATH)" "$(basename $APP_PATH)"

    log_info "Backup créé: $BACKUP_PATH/app_$TIMESTAMP.tar.gz"
}

# Mettre en maintenance
maintenance_on() {
    log_info "Activation du mode maintenance..."
    cd "$APP_PATH"
    $PHP_BIN artisan down --secret="maintenance-secret-$(date +%s)"
}

# Désactiver maintenance
maintenance_off() {
    log_info "Désactivation du mode maintenance..."
    cd "$APP_PATH"
    $PHP_BIN artisan up
}

# Installer/Mettre à jour les dépendances
install_deps() {
    log_info "Installation des dépendances Composer..."
    cd "$APP_PATH"
    composer install --no-dev --optimize-autoloader --no-interaction
}

# Exécuter les migrations
migrate() {
    log_info "Exécution des migrations..."
    cd "$APP_PATH"
    $PHP_BIN artisan migrate --force
}

# Créer les caches
cache_all() {
    log_info "Création des caches d'optimisation..."
    cd "$APP_PATH"
    $PHP_BIN artisan config:cache
    $PHP_BIN artisan route:cache
    $PHP_BIN artisan view:cache
    $PHP_BIN artisan event:cache
    log_info "Caches créés avec succès"
}

# Vider les caches
cache_clear() {
    log_info "Vidage des caches..."
    cd "$APP_PATH"
    $PHP_BIN artisan config:clear
    $PHP_BIN artisan route:clear
    $PHP_BIN artisan view:clear
    $PHP_BIN artisan cache:clear
    log_info "Caches vidés"
}

# Vérifier les permissions
fix_permissions() {
    log_info "Correction des permissions..."
    cd "$APP_PATH"
    chmod -R 775 storage bootstrap/cache
    log_info "Permissions corrigées"
}

# Afficher le statut
status() {
    log_info "Statut de l'application:"
    cd "$APP_PATH"
    $PHP_BIN artisan about
    echo ""
    log_info "Derniers logs:"
    tail -20 storage/logs/laravel.log 2>/dev/null || log_warn "Pas de logs disponibles"
}

# Déploiement initial
deploy() {
    log_info "=== DÉPLOIEMENT INITIAL ==="
    check_paths

    cd "$APP_PATH"

    # Vérifier .env
    if [ ! -f ".env" ]; then
        log_error "Fichier .env manquant!"
        log_info "Copier .env.production.example vers .env et configurer"
        exit 1
    fi

    install_deps

    # Générer la clé si nécessaire
    if ! grep -q "APP_KEY=base64:" .env; then
        log_info "Génération de la clé d'application..."
        $PHP_BIN artisan key:generate
    fi

    migrate
    cache_all
    fix_permissions

    # Storage link
    log_info "Création du lien storage..."
    $PHP_BIN artisan storage:link 2>/dev/null || log_warn "storage:link a échoué, créer manuellement"

    log_info "=== DÉPLOIEMENT TERMINÉ ==="
    status
}

# Mise à jour
update() {
    log_info "=== MISE À JOUR ==="
    check_paths

    backup
    maintenance_on

    # Si Git est utilisé
    if [ -d "$APP_PATH/.git" ]; then
        log_info "Pull des modifications Git..."
        cd "$APP_PATH"
        git pull origin main
    else
        log_warn "Pas de repo Git, uploader les fichiers manuellement"
    fi

    install_deps
    migrate
    cache_all
    fix_permissions

    maintenance_off

    log_info "=== MISE À JOUR TERMINÉE ==="
    status
}

# Rollback
rollback() {
    log_info "=== ROLLBACK ==="

    # Lister les backups disponibles
    log_info "Backups disponibles:"
    ls -la "$BACKUP_PATH"/*.tar.gz 2>/dev/null || log_error "Aucun backup trouvé"

    echo ""
    read -p "Entrer le nom du backup à restaurer (ou 'cancel'): " BACKUP_FILE

    if [ "$BACKUP_FILE" == "cancel" ]; then
        log_info "Rollback annulé"
        exit 0
    fi

    if [ ! -f "$BACKUP_PATH/$BACKUP_FILE" ]; then
        log_error "Backup non trouvé: $BACKUP_FILE"
        exit 1
    fi

    maintenance_on

    # Sauvegarder la version actuelle
    TIMESTAMP=$(date +%Y%m%d_%H%M%S)
    mv "$APP_PATH" "${APP_PATH}_failed_$TIMESTAMP"

    # Restaurer
    log_info "Restauration du backup..."
    mkdir -p "$APP_PATH"
    tar -xzf "$BACKUP_PATH/$BACKUP_FILE" -C "$(dirname $APP_PATH)"

    # Réinstaller vendor
    install_deps
    cache_clear
    cache_all
    fix_permissions

    maintenance_off

    log_info "=== ROLLBACK TERMINÉ ==="
}

# Menu principal
case "${1:-help}" in
    deploy)
        deploy
        ;;
    update)
        update
        ;;
    rollback)
        rollback
        ;;
    cache)
        cache_all
        ;;
    cache-clear)
        cache_clear
        ;;
    status)
        status
        ;;
    backup)
        backup
        ;;
    maintenance-on)
        maintenance_on
        ;;
    maintenance-off)
        maintenance_off
        ;;
    permissions)
        fix_permissions
        ;;
    *)
        echo "Usage: $0 {deploy|update|rollback|cache|cache-clear|status|backup|maintenance-on|maintenance-off|permissions}"
        echo ""
        echo "Actions:"
        echo "  deploy          - Déploiement initial complet"
        echo "  update          - Mise à jour (backup + pull + migrate)"
        echo "  rollback        - Restaurer depuis un backup"
        echo "  cache           - Créer tous les caches"
        echo "  cache-clear     - Vider tous les caches"
        echo "  status          - Afficher le statut de l'application"
        echo "  backup          - Créer un backup"
        echo "  maintenance-on  - Activer le mode maintenance"
        echo "  maintenance-off - Désactiver le mode maintenance"
        echo "  permissions     - Corriger les permissions"
        exit 1
        ;;
esac
