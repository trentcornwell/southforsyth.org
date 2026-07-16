#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
TASK_DIR="$ROOT_DIR/tasks/overnight"
LOG_ROOT="${OVERNIGHT_LOG_ROOT:-/private/tmp/southforsyth-overnight/logs}"
LOCK_PATH="${OVERNIGHT_LOCK_FILE:-/private/tmp/southforsyth-overnight/overnight.lock}"
RUN_ID="${OVERNIGHT_RUN_ID:-$(date +%Y%m%d-%H%M%S)}"
RUN_DIR="$LOG_ROOT/$RUN_ID"
FROM_TASK=""
ONLY_TASK=""
SKIP_BACKUP=0
SKIP_VERIFY=0
ALLOW_DIRTY=0

usage() {
  cat <<'USAGE'
Usage: scripts/overnight-runner.sh [options]

Options:
  --from N           Resume at task number N.
  --only N           Run exactly task number N.
  --tasks-dir DIR    Use an alternate numbered task directory.
  --run-id ID        Use a specific run id.
  --skip-backup      Test-only: do not run staging backup.
  --skip-verify      Test-only: do not run verification.
  --allow-dirty      Test-only: allow a dirty starting tree.

Safe defaults: staging only, no deployment, no publishing, no production DB,
no destructive reset, no imported-content deletion, no invented factual data.
USAGE
}

while [ "$#" -gt 0 ]; do
  case "$1" in
    --from)
      FROM_TASK="$2"
      shift 2
      ;;
    --only)
      ONLY_TASK="$2"
      shift 2
      ;;
    --tasks-dir)
      TASK_DIR="$2"
      shift 2
      ;;
    --run-id)
      RUN_ID="$2"
      RUN_DIR="$LOG_ROOT/$RUN_ID"
      shift 2
      ;;
    --skip-backup)
      SKIP_BACKUP=1
      shift
      ;;
    --skip-verify)
      SKIP_VERIFY=1
      shift
      ;;
    --allow-dirty)
      ALLOW_DIRTY=1
      shift
      ;;
    -h|--help)
      usage
      exit 0
      ;;
    *)
      echo "Unknown option: $1" >&2
      usage >&2
      exit 2
      ;;
  esac
done

refuse_unsafe_environment() {
  if [ "${OVERNIGHT_ENV:-staging}" = "production" ]; then
    echo "Refusing to run: OVERNIGHT_ENV=production is forbidden." >&2
    exit 1
  fi
  if [ "${ALLOW_PRODUCTION_DB:-0}" = "1" ]; then
    echo "Refusing to run: production database access is forbidden." >&2
    exit 1
  fi
  if [ "${ALLOW_DEPLOY:-0}" = "1" ]; then
    echo "Refusing to run: deployment is forbidden." >&2
    exit 1
  fi
  if [ "${ALLOW_PUBLISH:-0}" = "1" ]; then
    echo "Refusing to run: publishing is forbidden." >&2
    exit 1
  fi
  if [ -n "${STAGING_URL:-}" ] && printf '%s' "$STAGING_URL" | grep -Eiq '(^https?://)?(www\.)?southforsyth\.org(/|$)'; then
    echo "Refusing to run: STAGING_URL points at production." >&2
    exit 1
  fi
  case "$LOG_ROOT" in
    "$ROOT_DIR"/*)
      echo "Refusing to run: OVERNIGHT_LOG_ROOT must be outside the Git repository." >&2
      exit 1
      ;;
  esac
  case "${OVERNIGHT_BACKUP_ROOT:-/private/tmp/southforsyth-overnight/backups}" in
    "$ROOT_DIR"/*)
      echo "Refusing to run: OVERNIGHT_BACKUP_ROOT must be outside the Git repository." >&2
      exit 1
      ;;
  esac
}

task_number() {
  basename "$1" | sed -E 's/^([0-9]+).*/\1/'
}

task_title() {
  local file="$1"
  local title
  title="$(grep -m1 '^# ' "$file" | sed 's/^# //')"
  if [ -z "$title" ]; then
    title="$(basename "$file" .md)"
  fi
  printf '%s' "$title"
}

task_script_for() {
  local file="$1"
  local base="${file%.md}"
  if [ -x "$base.sh" ]; then
    printf '%s' "$base.sh"
    return
  fi
  if [ -x "$base" ]; then
    printf '%s' "$base"
  fi
}

record_failure() {
  local num="$1"
  local title="$2"
  local reason="$3"
  printf '%s\t%s\t%s\n' "$num" "$title" "$reason" >> "$RUN_DIR/failed.tsv"
}

finish_report() {
  "$ROOT_DIR/scripts/overnight-report.sh" --run-dir "$RUN_DIR" --output "$RUN_DIR/overnight-report.md" >/dev/null || true
}

cleanup_lock() {
  rm -rf "$LOCK_PATH"
}

refuse_unsafe_environment
mkdir -p "$LOG_ROOT" "$(dirname "$LOCK_PATH")"

if ! mkdir "$LOCK_PATH" 2>/dev/null; then
  echo "Another overnight run appears active: $LOCK_PATH" >&2
  exit 1
fi
trap cleanup_lock EXIT

mkdir -p "$RUN_DIR"
{
  echo "run_id=$RUN_ID"
  echo "started_at=$(date -u '+%Y-%m-%d %H:%M:%S UTC')"
  echo "tasks_dir=$TASK_DIR"
  echo "environment=${OVERNIGHT_ENV:-staging}"
} > "$RUN_DIR/run.env"

cd "$ROOT_DIR"

if [ "$ALLOW_DIRTY" != "1" ] && [ -n "$(git status --porcelain)" ]; then
  record_failure "preflight" "Clean working tree" "working tree is dirty; refusing to create checkpoint commits"
  finish_report
  echo "Working tree is dirty; inspect git status before starting an overnight run." >&2
  exit 1
fi

if [ "$SKIP_BACKUP" != "1" ]; then
  OVERNIGHT_RUN_ID="$RUN_ID" "$ROOT_DIR/scripts/backup-staging.sh" > "$RUN_DIR/backup.log" 2>&1
fi

tasks=()
while IFS= read -r task_file; do
  [ -n "$task_file" ] && tasks+=("$task_file")
done < <(find "$TASK_DIR" -maxdepth 1 -type f -name '[0-9]*.md' | sort)
if [ "${#tasks[@]}" -eq 0 ]; then
  record_failure "preflight" "Task discovery" "no numbered Markdown tasks found"
  finish_report
  exit 1
fi

for task in "${tasks[@]}"; do
  num="$(task_number "$task")"
  title="$(task_title "$task")"
  num_value=$((10#$num))

  if [ -n "$ONLY_TASK" ] && [ "$num_value" -ne "$((10#$ONLY_TASK))" ]; then
    continue
  fi
  if [ -n "$FROM_TASK" ] && [ "$num_value" -lt "$((10#$FROM_TASK))" ]; then
    continue
  fi

  script="$(task_script_for "$task" || true)"
  if [ -z "$script" ]; then
    record_failure "$num" "$title" "blocked: no executable task script found for $task"
    finish_report
    exit 1
  fi

  start_time="$(date -u '+%Y-%m-%d %H:%M:%S UTC')"
  task_log="$RUN_DIR/task-$num.log"
  echo "Starting task $num: $title" | tee "$task_log"
  echo "start_time=$start_time" >> "$task_log"

  if ! OVERNIGHT_TASK_FILE="$task" OVERNIGHT_TASK_NUMBER="$num" OVERNIGHT_RUN_DIR="$RUN_DIR" "$script" >> "$task_log" 2>&1; then
    record_failure "$num" "$title" "task command failed; working tree preserved"
    finish_report
    exit 1
  fi

  if [ "$SKIP_VERIFY" != "1" ]; then
    verify_log="$RUN_DIR/verify-$num.log"
    if ! "$ROOT_DIR/scripts/verify-theme.sh" "$verify_log"; then
      record_failure "$num" "$title" "verification failed; no checkpoint commit created"
      finish_report
      exit 1
    fi
  fi

  git add -A
  commit_message="Overnight task $num: $title"
  if git diff --cached --quiet; then
    git commit --allow-empty -m "$commit_message" >/dev/null
  else
    git commit -m "$commit_message" >/dev/null
  fi
  commit_hash="$(git rev-parse --short HEAD)"
  printf '%s\t%s\n' "$commit_hash" "$commit_message" >> "$RUN_DIR/commits.tsv"

  finish_time="$(date -u '+%Y-%m-%d %H:%M:%S UTC')"
  printf '%s\t%s\t%s\t%s\n' "$num" "$title" "$start_time" "$finish_time" >> "$RUN_DIR/completed.tsv"
done

echo "finished_at=$(date -u '+%Y-%m-%d %H:%M:%S UTC')" >> "$RUN_DIR/run.env"
finish_report
echo "Overnight run complete. Report: $RUN_DIR/overnight-report.md"
