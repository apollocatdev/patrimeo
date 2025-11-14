#!/usr/bin/env bash
set -euo pipefail

# --- CONFIG -------------------------------------------------------
IMAGE="${IMAGE:-ghcr.io/apollocatdev/patrimeo}"
PLATFORMS="${PLATFORMS:-linux/amd64}"
BUILDER_NAME="${BUILDER_NAME:-relit-builder}"
# Auth GHCR requis : GHCR_USER (login GitHub) + GHCR_TOKEN (PAT avec write:packages)
# -----------------------------------------------------------------

here="$(cd "$(dirname "$0")/.." && pwd)"
VERSION="$(jq -r '.version // empty' "$here/composer.json")"
[ -n "$VERSION" ] || { echo "::error::champ \"version\" manquant dans composer.json"; exit 1; }
[ -n "${GITHUB_USER:-}" ] || { echo "::error::GITHUB_USER non défini"; exit 1; }
[ -n "${GITHUB_TOKEN:-}" ] || { echo "::error::GITHUB_TOKEN non défini"; exit 1; }

echo "$GITHUB_TOKEN" | docker login ghcr.io -u "$GITHUB_USER" --password-stdin
docker buildx create --use --name "$BUILDER_NAME" >/dev/null 2>&1 || true
docker buildx use "$BUILDER_NAME"
docker buildx build --platform "$PLATFORMS" -t "$IMAGE:$VERSION" -t "$IMAGE:latest" --push "$here"
echo "✅ Poussé: $IMAGE:$VERSION et $IMAGE:latest"