#!/usr/bin/env bash
set -euo pipefail

# Pull the live DreamHost theme into the local theme folder via rsync over SSH.
# Usage: cp deploy.example.env .env && source .env && ./pull-live.sh

if [ -f .env ]; then
  # shellcheck disable=SC1091
  set -a
  source .env
  set +a
fi

REQUIRED_VARS=("DREAMHOST_USER" "DREAMHOST_SERVER" "DREAMHOST_REMOTE_PATH")

for var in "${REQUIRED_VARS[@]}"; do
  if [ -z "${!var:-}" ]; then
    echo "Error: ${var} is not set. Update .env or export it before pulling from DreamHost." >&2
    exit 1
  fi
done

LOCAL_THEME_PATH="${LOCAL_THEME_PATH:-wordpress/wp-content/themes/southforsyth/}"
REMOTE_PATH="${DREAMHOST_REMOTE_PATH}"

mkdir -p "$LOCAL_THEME_PATH"

echo "Pulling live theme from ${DREAMHOST_USER}@${DREAMHOST_SERVER}:${REMOTE_PATH}"
echo "into $LOCAL_THEME_PATH"
rsync -avz --delete \
  --exclude='.DS_Store' \
  --exclude='node_modules' \
  --exclude='.git' \
  --exclude='*.log' \
  --exclude='logs' \
  --exclude='*.cache' \
  --exclude='.env' \
  --exclude='deploy.example.env' \
  --exclude='deploy.sh' \
  --exclude='pull-live.sh' \
  "${DREAMHOST_USER}@${DREAMHOST_SERVER}:${REMOTE_PATH}/" \
  "$LOCAL_THEME_PATH"

echo "Live theme pull complete."
