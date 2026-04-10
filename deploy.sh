#!/bin/bash
set -euo pipefail

# ============================================
# Deploiement Site Mariage
# ============================================
# A executer directement sur le serveur.
# Usage: ./deploy.sh v1.0.0
# ============================================

TAG="${1:-}"
DEPLOY_DIR="/opt/mariage"
REPO_URL="git@github.com:jdenozi/mariage.git"

# Couleurs
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

log()  { echo -e "${GREEN}[+]${NC} $1"; }
warn() { echo -e "${YELLOW}[!]${NC} $1"; }
err()  { echo -e "${RED}[x]${NC} $1"; exit 1; }

[ -z "$TAG" ] && err "Usage: ./deploy.sh v1.0.0"

command -v podman > /dev/null 2>&1 || err "Podman n'est pas installe"
command -v git > /dev/null 2>&1 || err "Git n'est pas installe"

# ============================================
# Recuperation du code
# ============================================
log "Recuperation du tag ${TAG}..."
if [ -d "${DEPLOY_DIR}/.git" ]; then
    cd "${DEPLOY_DIR}"
    git fetch --tags
    git checkout "${TAG}" || err "Tag ${TAG} introuvable"
else
    git clone "${REPO_URL}" "${DEPLOY_DIR}"
    cd "${DEPLOY_DIR}"
    git checkout "${TAG}" || err "Tag ${TAG} introuvable"
fi

# ============================================
# Relance des containers
# ============================================
log "Arret des anciens containers..."
podman pod stop mariage-pod 2>/dev/null || true
podman pod rm mariage-pod 2>/dev/null || true

log "Creation du pod..."
podman pod create --name mariage-pod -p 8888:80

log "Lancement de MySQL..."
podman run -d --pod mariage-pod --name mariage-db \
    -e MYSQL_DATABASE=wordpress \
    -e MYSQL_USER=wordpress \
    -e MYSQL_PASSWORD=wordpress \
    -e MYSQL_ROOT_PASSWORD=rootpassword \
    -v mariage_db:/var/lib/mysql \
    docker.io/library/mysql:8.0

log "Attente de MySQL..."
for i in $(seq 1 30); do
    podman exec mariage-db mysqladmin ping -h localhost --silent 2>/dev/null && break
    sleep 2
done

log "Lancement de WordPress..."
podman run -d --pod mariage-pod --name mariage-wp \
    -e WORDPRESS_DB_HOST=127.0.0.1 \
    -e WORDPRESS_DB_USER=wordpress \
    -e WORDPRESS_DB_PASSWORD=wordpress \
    -e WORDPRESS_DB_NAME=wordpress \
    -v "${DEPLOY_DIR}/wp-content/themes/mariage-julie-julien:/var/www/html/wp-content/themes/mariage-julie-julien:Z" \
    -v "${DEPLOY_DIR}/wp-content/plugins/mariage-forms:/var/www/html/wp-content/plugins/mariage-forms:Z" \
    -v mariage_uploads:/var/www/html/wp-content/uploads \
    docker.io/library/wordpress:latest

log "Tag ${TAG} deploye !"
warn "Pensez a configurer un reverse proxy (nginx/caddy) pour le HTTPS."
