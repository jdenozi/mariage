#!/bin/bash
set -euo pipefail

# ============================================
# Deploiement Site Mariage
# ============================================
# Usage:
#   ./deploy.sh local              → lance en local avec podman
#   ./deploy.sh user@host v1.0.0   → deploie le tag sur le serveur
#
# Prerequis sur le serveur:
#   - Podman installe
#   - Acces SSH avec cle
#   - Git installe
# ============================================

REMOTE="${1:-}"
TAG="${2:-}"
REMOTE_DIR="/opt/mariage"
REPO_URL="git@github.com:jdenozi/mariage.git"

# Couleurs
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

log()  { echo -e "${GREEN}[+]${NC} $1"; }
warn() { echo -e "${YELLOW}[!]${NC} $1"; }
err()  { echo -e "${RED}[x]${NC} $1"; exit 1; }

start_pod() {
    local CMD="$1"

    $CMD pod stop mariage-pod 2>/dev/null || true
    $CMD pod rm mariage-pod 2>/dev/null || true

    log "Creation du pod..."
    $CMD pod create --name mariage-pod -p 8888:80

    log "Lancement de MySQL..."
    $CMD run -d --pod mariage-pod --name mariage-db \
        -e MYSQL_DATABASE=wordpress \
        -e MYSQL_USER=wordpress \
        -e MYSQL_PASSWORD=wordpress \
        -e MYSQL_ROOT_PASSWORD=rootpassword \
        -v mariage_db:/var/lib/mysql \
        docker.io/library/mysql:8.0

    log "Attente de MySQL..."
    for i in $(seq 1 30); do
        $CMD exec mariage-db mysqladmin ping -h localhost --silent 2>/dev/null && break
        sleep 2
    done

    log "Lancement de WordPress..."
    $CMD run -d --pod mariage-pod --name mariage-wp \
        -e WORDPRESS_DB_HOST=127.0.0.1 \
        -e WORDPRESS_DB_USER=wordpress \
        -e WORDPRESS_DB_PASSWORD=wordpress \
        -e WORDPRESS_DB_NAME=wordpress \
        -v "${REMOTE_DIR}/wp-content/themes/mariage-julie-julien:/var/www/html/wp-content/themes/mariage-julie-julien:Z" \
        -v "${REMOTE_DIR}/wp-content/plugins/mariage-forms:/var/www/html/wp-content/plugins/mariage-forms:Z" \
        -v mariage_uploads:/var/www/html/wp-content/uploads \
        docker.io/library/wordpress:latest
}

# ============================================
# Mode local
# ============================================
if [ "$REMOTE" = "local" ]; then
    log "Deploiement local avec Podman..."
    REMOTE_DIR="$(pwd)"
    command -v podman > /dev/null 2>&1 || err "Podman n'est pas installe"
    start_pod "podman"
    log "Site accessible sur http://localhost:8888"
    exit 0
fi

# ============================================
# Mode distant
# ============================================
[ -z "$REMOTE" ] && err "Usage: ./deploy.sh local | ./deploy.sh user@host v1.0.0"
[ -z "$TAG" ] && err "Tag manquant. Usage: ./deploy.sh user@host v1.0.0"

log "Deploiement du tag ${TAG} vers ${REMOTE}..."

# Verifications
ssh -q -o ConnectTimeout=5 "$REMOTE" "echo ok" > /dev/null 2>&1 || err "Impossible de se connecter a $REMOTE"
ssh "$REMOTE" "command -v podman > /dev/null 2>&1" || err "Podman n'est pas installe sur $REMOTE"
ssh "$REMOTE" "command -v git > /dev/null 2>&1" || err "Git n'est pas installe sur $REMOTE"

log "Clone/mise a jour du repo..."
ssh "$REMOTE" "
    if [ -d ${REMOTE_DIR}/.git ]; then
        cd ${REMOTE_DIR} && git fetch --tags && git checkout ${TAG}
    else
        git clone ${REPO_URL} ${REMOTE_DIR} && cd ${REMOTE_DIR} && git checkout ${TAG}
    fi
"

log "Arret des anciens containers..."
ssh "$REMOTE" "podman pod stop mariage-pod 2>/dev/null; podman pod rm mariage-pod 2>/dev/null; true"

log "Lancement du pod sur le serveur..."
ssh "$REMOTE" "
    cd ${REMOTE_DIR}

    podman pod create --name mariage-pod -p 8888:80

    podman run -d --pod mariage-pod --name mariage-db \
        -e MYSQL_DATABASE=wordpress \
        -e MYSQL_USER=wordpress \
        -e MYSQL_PASSWORD=wordpress \
        -e MYSQL_ROOT_PASSWORD=rootpassword \
        -v mariage_db:/var/lib/mysql \
        docker.io/library/mysql:8.0

    echo 'Attente de MySQL...'
    for i in \$(seq 1 30); do
        podman exec mariage-db mysqladmin ping -h localhost --silent 2>/dev/null && break
        sleep 2
    done

    podman run -d --pod mariage-pod --name mariage-wp \
        -e WORDPRESS_DB_HOST=127.0.0.1 \
        -e WORDPRESS_DB_USER=wordpress \
        -e WORDPRESS_DB_PASSWORD=wordpress \
        -e WORDPRESS_DB_NAME=wordpress \
        -v ${REMOTE_DIR}/wp-content/themes/mariage-julie-julien:/var/www/html/wp-content/themes/mariage-julie-julien:Z \
        -v ${REMOTE_DIR}/wp-content/plugins/mariage-forms:/var/www/html/wp-content/plugins/mariage-forms:Z \
        -v mariage_uploads:/var/www/html/wp-content/uploads \
        docker.io/library/wordpress:latest
"

log "Tag ${TAG} deploye !"
log "Site accessible sur http://$(echo "$REMOTE" | cut -d@ -f2):8888"
warn "Pensez a configurer un reverse proxy (nginx/caddy) pour le HTTPS."
