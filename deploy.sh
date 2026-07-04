#!/usr/bin/env bash
set -euo pipefail

# Deploy the local South Forsyth theme to DreamHost via rsync over SSH.
# Usage: cp deploy.example.env .env && source .env && ./deploy.sh

if [ -f .env ]; then
  # shellcheck disable=SC1091
  set -a
  source .env
  set +a
fi

REQUIRED_VARS=("DREAMHOST_USER" "DREAMHOST_SERVER" "DREAMHOST_REMOTE_PATH")

for var in "${REQUIRED_VARS[@]}"; do
  if [ -z "${!var:-}" ]; then
    echo "Error: ${var} is not set. Update .env or export it before deploying." >&2
    exit 1
  fi
done

LOCAL_THEME_PATH="${LOCAL_THEME_PATH:-wordpress/wp-content/themes/southforsyth/}"
REMOTE_PATH="${DREAMHOST_REMOTE_PATH}"

if [ ! -d "$LOCAL_THEME_PATH" ]; then
  echo "Error: Local theme path not found: $LOCAL_THEME_PATH" >&2
  exit 1
fi

echo "Deploying theme from $LOCAL_THEME_PATH"
echo "to ${DREAMHOST_USER}@${DREAMHOST_SERVER}:${REMOTE_PATH}"

echo "Starting rsync transfer..."
rsync -avz --delete \
  --exclude='.DS_Store' \
  --exclude='node_modules' \
  --exclude='.git' \
  --exclude='*.log' \
  --exclude='logs' \
  --exclude='cache' \
  --exclude='*.cache' \
  --exclude='.env' \
  --exclude='deploy.example.env' \
  --exclude='deploy.sh' \
  --exclude='pull-live.sh' \
  "$LOCAL_THEME_PATH" \
  "${DREAMHOST_USER}@${DREAMHOST_SERVER}:${REMOTE_PATH}/"

echo "Deployment complete."
