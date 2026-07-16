#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
THEME_DIR="$ROOT_DIR/wordpress/wp-content/themes/southforsyth"
REPORT_FILE="${1:-}"

log() {
  printf '%s\n' "$*"
  if [ -n "$REPORT_FILE" ]; then
    printf '%s\n' "$*" >> "$REPORT_FILE"
  fi
}

fail() {
  log "FAIL: $*"
  exit 1
}

run_step() {
  log ""
  log "## $1"
}

cd "$ROOT_DIR"

if [ -n "$REPORT_FILE" ]; then
  mkdir -p "$(dirname "$REPORT_FILE")"
  : > "$REPORT_FILE"
fi

run_step "Git Whitespace"
git diff --check
log "PASS: git diff --check"

run_step "Changed PHP Lint"
changed_php=()
while IFS= read -r file; do
  [ -n "$file" ] && changed_php+=("$file")
done < <(
  {
    git diff --name-only --diff-filter=ACMRT -- '*.php'
    git ls-files --others --exclude-standard -- '*.php'
  } | sort -u
)

if [ "${#changed_php[@]}" -eq 0 ]; then
  log "SKIP: no changed PHP files"
else
  for file in "${changed_php[@]}"; do
    php -l "$file" >/dev/null
    log "PASS: php -l $file"
  done
fi

run_step "Project Scripts"
if [ -f package.json ] && command -v node >/dev/null 2>&1; then
  real_scripts_output="$(node <<'NODE'
const fs = require('fs');
const pkg = JSON.parse(fs.readFileSync('package.json', 'utf8'));
const scripts = pkg.scripts || {};
const candidates = ['lint', 'build', 'check', 'format:check', 'format-check', 'validate'];
for (const name of candidates) {
  if (scripts[name]) console.log(name);
}
if (scripts.test && scripts.test !== 'echo "Error: no test specified" && exit 1') {
  console.log('test');
}
NODE
)"
  real_scripts=()
  while IFS= read -r script_name; do
    [ -n "$script_name" ] && real_scripts+=("$script_name")
  done <<< "$real_scripts_output"
  if [ "${#real_scripts[@]}" -eq 0 ]; then
    log "SKIP: no real lint/build/check/test scripts"
  else
    for script in "${real_scripts[@]}"; do
      npm run "$script"
      log "PASS: npm run $script"
    done
  fi
else
  log "SKIP: package.json or node unavailable"
fi

run_step "Repository Hygiene"
if [ ! -d "$THEME_DIR" ]; then
  fail "theme directory not found: $THEME_DIR"
fi

bad_files="$(
  find "$ROOT_DIR" \
    \( -path "$ROOT_DIR/.git" -o -path "$ROOT_DIR/node_modules" -o -path "$ROOT_DIR/.overnight" \) -prune -o \
    -type f \( \
      -name '*.sql' -o -name '*.sqlite' -o -name '*.sqlite3' -o -name '*.db' -o \
      -name '*.log' -o -name '*.tmp' -o -name '*.temp' -o -name '*.cache' \
    \) -print
)"
if [ -n "$bad_files" ]; then
  printf '%s\n' "$bad_files"
  fail "database/log/temp/cache files found inside repository"
fi
log "PASS: no database/log/temp/cache files found in repository"

credential_hits="$(
  git ls-files | grep -Ev '^(node_modules/|package-lock\.json$)' | xargs rg -n -i \
    '((api|secret|private|access)[_-]?key|password|passwd|token|DB_PASSWORD)[[:space:]]*[:=][[:space:]]*["'\'']?[A-Za-z0-9_./+=-]{16,}|bearer[[:space:]]+[A-Za-z0-9._-]{16,}|BEGIN (RSA|OPENSSH|PRIVATE) KEY)' \
    2>/dev/null || true
)"
if [ -n "$credential_hits" ]; then
  printf '%s\n' "$credential_hits"
  fail "possible committed credential material found"
fi
log "PASS: no credential-like material found in tracked files"

run_step "PHP Error Logs"
if [ -f "$ROOT_DIR/wordpress/wp-content/debug.log" ]; then
  if grep -Ei 'PHP (Fatal|Parse|Warning)|WordPress database error|There has been a critical error' "$ROOT_DIR/wordpress/wp-content/debug.log"; then
    fail "debug.log contains PHP/database errors"
  fi
  log "PASS: debug.log present without fatal/parse/warning/database errors"
else
  log "PASS: no local wp-content/debug.log"
fi

run_step "WordPress Smoke Checks"
WP_BIN="${WP_BIN:-wp}"
WP_PATH="${WP_PATH:-$ROOT_DIR/wordpress}"
if command -v "$WP_BIN" >/dev/null 2>&1 && [ -f "$WP_PATH/wp-load.php" ]; then
  "$WP_BIN" --path="$WP_PATH" theme list --status=active
  "$WP_BIN" --path="$WP_PATH" post-type get school --field=name >/dev/null
  "$WP_BIN" --path="$WP_PATH" post-type get sf_suggestion --field=name >/dev/null
  log "PASS: required post types are registered"

  if [ "${ALLOW_PUBLISHED_IMPORTED:-0}" != "1" ]; then
    published_imported="$("$WP_BIN" --path="$WP_PATH" eval '
      $ids = get_posts(array(
        "post_type" => array("school", "park", "restaurant", "business", "church", "trail", "community_resource"),
        "post_status" => "publish",
        "posts_per_page" => -1,
        "fields" => "ids",
        "meta_key" => "_sf_import_source",
      ));
      echo count($ids);
    ')"
    if [ "$published_imported" != "0" ]; then
      fail "$published_imported imported directory posts are published; set ALLOW_PUBLISHED_IMPORTED=1 only for an explicit publishing task"
    fi
    log "PASS: imported directory posts are not published"
  else
    log "SKIP: published imported directory posts allowed by ALLOW_PUBLISHED_IMPORTED=1"
  fi
else
  log "SKIP: WP-CLI smoke checks unavailable"
fi

log ""
log "Verification complete."
