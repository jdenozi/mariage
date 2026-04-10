#!/bin/bash
set -euo pipefail

# ============================================
# Deploiement Site Mariage
# ============================================
# Usage: ./deploy.sh [user@host]
# Ex:    ./deploy.sh julien@monserveur.com
#
# Prerequis sur le serveur:
#   - Podman installe
#   - Acces SSH avec cle (sans mot de passe)
# ============================================

REMOTE="${1:-}"
REMOTE_DIR="/opt/mariage"

# Couleurs
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

log()  { echo -e "${GREEN}[+]${NC} $1"; }
warn() { echo -e "${YELLOW}[!]${NC} $1"; }
err()  { echo -e "${RED}[x]${NC} $1"; exit 1; }

# ============================================
# Mode local (sans argument)
# ============================================
if [ -z "$REMOTE" ]; then
    log "Deploiement local avec Docker Compose..."
    docker compose down 2>/dev/null || true
    docker compose up -d --build
    log "Site accessible sur http://localhost:8888"
    log "phpMyAdmin sur http://localhost:8889"
    exit 0
fi

# ============================================
# Mode distant (avec user@host)
# ============================================
log "Deploiement vers ${REMOTE}:${REMOTE_DIR}..."

# Verification SSH
ssh -q -o ConnectTimeout=5 "$REMOTE" "echo ok" > /dev/null 2>&1 || err "Impossible de se connecter a $REMOTE"

# Verification Podman sur le serveur
ssh "$REMOTE" "command -v podman > /dev/null 2>&1" || err "Podman n'est pas installe sur $REMOTE"

log "Creation du repertoire distant..."
ssh "$REMOTE" "mkdir -p ${REMOTE_DIR}"

log "Synchronisation des fichiers..."
rsync -avz --delete \
    --exclude '.git' \
    --exclude '.idea' \
    --exclude 'node_modules' \
    --exclude '.DS_Store' \
    --exclude 'package-lock.json' \
    ./ "${REMOTE}:${REMOTE_DIR}/"

# ============================================
# Creation du pod et des containers Podman
# ============================================
log "Arret des anciens containers..."
ssh "$REMOTE" "podman pod stop mariage-pod 2>/dev/null; podman pod rm mariage-pod 2>/dev/null; true"

log "Creation du pod..."
ssh "$REMOTE" "podman pod create --name mariage-pod -p 8888:80 -p 8889:80"

log "Lancement de MySQL..."
ssh "$REMOTE" "podman run -d --pod mariage-pod --name mariage-db \
    -e MYSQL_DATABASE=wordpress \
    -e MYSQL_USER=wordpress \
    -e MYSQL_PASSWORD=wordpress \
    -e MYSQL_ROOT_PASSWORD=rootpassword \
    -v mariage_db:/var/lib/mysql \
    docker.io/library/mysql:8.0"

log "Attente de MySQL..."
ssh "$REMOTE" "for i in \$(seq 1 30); do podman exec mariage-db mysqladmin ping -h localhost --silent 2>/dev/null && break; sleep 2; done"

log "Lancement de WordPress..."
ssh "$REMOTE" "podman run -d --pod mariage-pod --name mariage-wp \
    -e WORDPRESS_DB_HOST=127.0.0.1 \
    -e WORDPRESS_DB_USER=wordpress \
    -e WORDPRESS_DB_PASSWORD=wordpress \
    -e WORDPRESS_DB_NAME=wordpress \
    -v ${REMOTE_DIR}/wp-content/themes/mariage-julie-julien:/var/www/html/wp-content/themes/mariage-julie-julien:Z \
    -v ${REMOTE_DIR}/wp-content/plugins/mariage-forms:/var/www/html/wp-content/plugins/mariage-forms:Z \
    -v mariage_uploads:/var/www/html/wp-content/uploads \
    docker.io/library/wordpress:latest"

log "Deploiement termine !"
log "Site accessible sur http://\$(echo $REMOTE | cut -d@ -f2):8888"
warn "Pensez a configurer un reverse proxy (nginx/caddy) pour le HTTPS."
