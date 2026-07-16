#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
RUN_DIR=""
OUTPUT=""

while [ "$#" -gt 0 ]; do
  case "$1" in
    --run-dir)
      RUN_DIR="$2"
      shift 2
      ;;
    --output)
      OUTPUT="$2"
      shift 2
      ;;
    *)
      echo "Unknown option: $1" >&2
      exit 2
      ;;
  esac
done

RUN_DIR="${RUN_DIR:-${OVERNIGHT_RUN_DIR:-}}"
if [ -z "$RUN_DIR" ]; then
  echo "Missing --run-dir." >&2
  exit 2
fi

OUTPUT="${OUTPUT:-$RUN_DIR/overnight-report.md}"
mkdir -p "$(dirname "$OUTPUT")"

cd "$ROOT_DIR"

{
  echo "# SouthForsyth.org Overnight Run Report"
  echo ""
  echo "- Generated: $(date -u '+%Y-%m-%d %H:%M:%S UTC')"
  echo "- Run directory: \`$RUN_DIR\`"
  if [ -f "$RUN_DIR/run.env" ]; then
    sed 's/^/- /' "$RUN_DIR/run.env"
  fi
  echo ""

  echo "## Completed Tasks"
  if [ -f "$RUN_DIR/completed.tsv" ]; then
    awk -F '\t' '{printf("- `%s` %s (%s -> %s)\n", $1, $2, $3, $4)}' "$RUN_DIR/completed.tsv"
  else
    echo "- None recorded."
  fi
  echo ""

  echo "## Failed Or Blocked Tasks"
  if [ -f "$RUN_DIR/failed.tsv" ]; then
    awk -F '\t' '{printf("- `%s` %s: %s\n", $1, $2, $3)}' "$RUN_DIR/failed.tsv"
  else
    echo "- None recorded."
  fi
  echo ""

  echo "## Commits"
  if [ -f "$RUN_DIR/commits.tsv" ]; then
    awk -F '\t' '{printf("- `%s` %s\n", $1, $2)}' "$RUN_DIR/commits.tsv"
  else
    echo "- None recorded."
  fi
  echo ""

  echo "## Files Changed"
  git status --short || true
  echo ""

  echo "## WP Post Counts"
  WP_BIN="${WP_BIN:-wp}"
  WP_PATH="${WP_PATH:-$ROOT_DIR/wordpress}"
  if command -v "$WP_BIN" >/dev/null 2>&1 && [ -f "$WP_PATH/wp-load.php" ]; then
    for type in post page event restaurant park neighborhood school church business guide article trail topic community_resource sf_suggestion; do
      count="$("$WP_BIN" --path="$WP_PATH" post list --post_type="$type" --post_status=any --format=count 2>/dev/null || echo n/a)"
      echo "- ${type}: ${count}"
    done
  else
    echo "- WP-CLI unavailable."
  fi
  echo ""

  echo "## Import Results"
  if ls "$RUN_DIR"/task-*.log >/dev/null 2>&1; then
    grep -E 'Import report|Total schools found|Total imported|Total updated|Duplicates prevented|Records skipped|Source failures|Success: Import complete|Dry run complete' "$RUN_DIR"/task-*.log || true
  else
    echo "- No task logs found."
  fi
  echo ""

  echo "## Verification Results"
  if ls "$RUN_DIR"/verify-*.log >/dev/null 2>&1; then
    for file in "$RUN_DIR"/verify-*.log; do
      echo "### $(basename "$file")"
      tail -n 80 "$file"
      echo ""
    done
  else
    echo "- No verification logs found."
  fi
  echo ""

  echo "## Unresolved Decisions"
  if [ -f "$RUN_DIR/decisions.md" ]; then
    cat "$RUN_DIR/decisions.md"
  else
    echo "- None recorded."
  fi
} > "$OUTPUT"

echo "$OUTPUT"
