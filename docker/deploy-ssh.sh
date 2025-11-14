#!/usr/bin/env bash
set -euo pipefail
[ -n "${PREPROD_HOST:-}" ] || { echo "::error::PREPROD_HOST manquant"; exit 1; }
[ -n "${PREPROD_USER:-}" ] || { echo "::error::PREPROD_USER manquant"; exit 1; }
[ -n "${PREPROD_PATH:-}" ] || { echo "::error::PREPROD_PATH manquant"; exit 1; }

ssh -p "${PREPROD_PORT:-22}" "${PREPROD_USER}@${PREPROD_HOST}" bash <<EOSSH
set -euo pipefail
cd "${PREPROD_PATH}"
if [ -n "${GITHUB_USER:-}" ] && [ -n "${GITHUB_TOKEN:-}" ]; then echo "$GITHUB_TOKEN" | docker login ghcr.io -u "$GITHUB_USER" --password-stdin; fi
docker compose pull
docker compose up -d --remove-orphans
docker compose exec -T patrimeo php artisan migrate --force
docker compose exec -T patrimeo php artisan optimize
docker image prune -f
EOSSH