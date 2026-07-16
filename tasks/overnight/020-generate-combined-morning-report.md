# Generate a combined morning report

## Objective
Generate the final morning report for the overnight run, summarizing completed tasks, failures, commits, changed files, post counts, import results, verification results, and unresolved decisions.

## Files likely involved
- `scripts/overnight-report.sh`
- Overnight run directory outside the theme
- Project documentation if the report format needs clarification

## Permitted changes
- Improve report generation.
- Write report artifacts outside the WordPress theme directory.
- Add documentation updates for report interpretation.

## Forbidden changes
- Do not deploy.
- Do not publish content.
- Do not modify production.
- Do not create backups inside the Git repository.

## Required verification
- Run `scripts/overnight-report.sh` against the active run directory.
- Confirm the report includes all required sections.
- Run `scripts/verify-theme.sh` if code changed.

## Success criteria
- A single Markdown report exists for the run.
- It includes completed/failed tasks, commits, changed files, post counts, import results, verification results, and unresolved decisions.

## Stop conditions
- Run directory is missing.
- Report omits failed tasks or unresolved decisions.
- Report generation would require production access.

## Required final report
- Provide the report path and a concise summary of overnight outcomes.
