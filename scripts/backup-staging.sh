#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BACKUP_ROOT="${OVERNIGHT_BACKUP_ROOT:-/private/tmp/southforsyth-overnight/backups}"
RUN_ID="${OVERNIGHT_RUN_ID:-$(date +%Y%m%d-%H%M%S)}"
BACKUP_DIR="$BACKUP_ROOT/$RUN_ID"
WP_BIN="${WP_BIN:-wp}"
STAGING_WP_PATH="${STAGING_WP_PATH:-$ROOT_DIR/wordpress}"
STAGING_THEME_PATH="${STAGING_THEME_PATH:-$ROOT_DIR/wordpress/wp-content/themes/southforsyth}"
STAGING_URL="${STAGING_URL:-}"

if [ "${OVERNIGHT_ENV:-staging}" = "production" ]; then
  echo "Refusing backup: OVERNIGHT_ENV=production is not allowed." >&2
  exit 1
fi

if [ "${ALLOW_PRODUCTION_DB:-0}" = "1" ]; then
  echo "Refusing backup: production database access is forbidden for overnight runs." >&2
  exit 1
fi

case "$BACKUP_DIR" in
  "$ROOT_DIR"/*)
    echo "Refusing backup: backups must be outside the Git repository." >&2
    exit 1
    ;;
esac

if [ -n "$STAGING_URL" ] && printf '%s' "$STAGING_URL" | grep -Eiq '(^https?://)?(www\.)?southforsyth\.org(/|$)'; then
  echo "Refusing backup: STAGING_URL points at production." >&2
  exit 1
fi

mkdir -p "$BACKUP_DIR"

if [ ! -d "$STAGING_THEME_PATH" ]; then
  echo "Theme path not found: $STAGING_THEME_PATH" >&2
  exit 1
fi

tar -czf "$BACKUP_DIR/theme-southforsyth.tgz" -C "$(dirname "$STAGING_THEME_PATH")" "$(basename "$STAGING_THEME_PATH")"
echo "theme_backup=$BACKUP_DIR/theme-southforsyth.tgz"

if command -v "$WP_BIN" >/dev/null 2>&1 && [ -f "$STAGING_WP_PATH/wp-load.php" ]; then
  "$WP_BIN" --path="$STAGING_WP_PATH" db export "$BACKUP_DIR/database.sql" --quiet
  echo "database_backup=$BACKUP_DIR/database.sql"
else
  echo "WP-CLI unavailable or WordPress path missing; staging database backup could not be created." >&2
  exit 1
fi

echo "backup_dir=$BACKUP_DIR"
