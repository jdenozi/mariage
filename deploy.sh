#!/bin/bash
set -euo pipefail

# ============================================
# Deploiement Site Mariage
# ============================================
# Usage: ./deploy.sh [user@host]
# Ex:    ./deploy.sh julien@monserveur.com
#
# Prerequis sur le serveur:
#   - Docker & Docker Compose installes
#   - Acces SSH avec cle (sans mot de passe)
# ============================================

REMOTE="${1:-}"
REMOTE_DIR="/opt/mariage"
PROJECT_NAME="mariage"

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

# Verification Docker sur le serveur
ssh "$REMOTE" "command -v docker > /dev/null 2>&1" || err "Docker n'est pas installe sur $REMOTE"

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

log "Lancement des containers..."
ssh "$REMOTE" "cd ${REMOTE_DIR} && docker compose down 2>/dev/null; docker compose up -d --build"

log "Deploiement termine !"
warn "Pensez a configurer un reverse proxy (nginx/traefik) pour le HTTPS."
