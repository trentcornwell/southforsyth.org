# Overnight Development Workflow

This workflow is for long-running, resumable AI coding sessions on the SouthForsyth.org WordPress project. It is staging-only by default. It must not deploy, publish content, access production databases, reset WordPress, delete imported content, or invent factual data.

## Files
- `tasks/overnight/*.md` contains numbered task specs.
- `scripts/overnight-runner.sh` runs numbered tasks in order.
- `scripts/backup-staging.sh` backs up staging before a run.
- `scripts/verify-theme.sh` runs repository and WordPress validation.
- `scripts/overnight-report.sh` generates the final Markdown report.

Task files are instructions. A task runs only when a matching executable script exists next to it, such as `tasks/overnight/011-fix-school-importer-dry-run.sh`. If no executable exists, the runner stops and reports the task as blocked.

## Safe Defaults
- Staging only.
- No deployment.
- No publishing.
- No production database access.
- No destructive database reset.
- No deletion of imported content.
- No invented factual data.
- Logs and backups are written outside the WordPress theme directory.
- Logs and database backups default to `/private/tmp/southforsyth-overnight/`, not the Git repository.
- A lock directory prevents two overnight runs from operating at once.

## Before Starting
Use a clean working tree. The runner refuses to create checkpoint commits from a dirty tree unless `--allow-dirty` is passed for isolated tests.

Configure staging paths:

```bash
export OVERNIGHT_ENV=staging
export STAGING_WP_PATH=/path/to/staging/wordpress
export STAGING_THEME_PATH=/path/to/staging/wordpress/wp-content/themes/southforsyth
export STAGING_URL=https://staging.example.test
```

Do not point `STAGING_URL` at `https://www.southforsyth.org`.

## Start A Run
```bash
scripts/overnight-runner.sh
```

The runner will:
1. Create an external run directory.
2. Acquire the lock.
3. Run the staging backup.
4. Run tasks in numeric order.
5. Run verification after each successful task.
6. Commit a checkpoint after each successful verified task.
7. Stop at the first task or verification failure.
8. Generate `overnight-report.md`.

## Run One Task
```bash
scripts/overnight-runner.sh --only 011
```

## Resume From A Task
```bash
scripts/overnight-runner.sh --from 014
```

## Stop A Run
Use `Ctrl-C`. The runner removes the lock on normal shell exit. If a machine crash leaves a stale lock, inspect the run directory first, then remove:

```bash
rm -rf /private/tmp/southforsyth-overnight/overnight.lock
```

Do not remove a lock until you are sure no runner is active.

## Inspect A Run
Run directories default to:

```bash
/private/tmp/southforsyth-overnight/logs/<run-id>
```

Useful files:
- `run.env`
- `backup.log`
- `task-<number>.log`
- `verify-<number>.log`
- `completed.tsv`
- `failed.tsv`
- `commits.tsv`
- `overnight-report.md`

Generate or refresh a report:

```bash
scripts/overnight-report.sh --run-dir /private/tmp/southforsyth-overnight/logs/<run-id>
```

## Verification
Run manually any time:

```bash
scripts/verify-theme.sh
```

It checks whitespace, changed PHP syntax, real npm validation scripts, repository hygiene, local PHP error logs, WP-CLI smoke checks when available, and imported directory post status safety.

## Failure Behavior
- If a task fails, the runner stops.
- If verification fails, the runner stops.
- Failed working trees are preserved for inspection.
- No checkpoint commit is created after a failed task or failed verification.
- No production deployment command is ever run by this workflow.

## Checkpoint Commits
After each successful verified task, the runner creates a commit with the task number in the message:

```text
Overnight task 011: Fix school importer dry-run and live-validation parity
```

Use a clean working tree before starting so checkpoint commits contain only the task's changes.
