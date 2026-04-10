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

command -v docker-compose > /dev/null 2>&1 || err "docker-compose n'est pas installe"
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
docker-compose down 2>/dev/null || true

log "Lancement des containers..."
docker-compose up -d --build

log "Tag ${TAG} deploye !"
warn "Pensez a configurer un reverse proxy (nginx/caddy) pour le HTTPS."
