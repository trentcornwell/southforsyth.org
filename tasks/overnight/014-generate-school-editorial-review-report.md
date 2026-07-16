# Generate a school editorial review report

## Objective
Generate a staging-only editorial review report that helps a human decide which school drafts are ready for publication later.

## Files likely involved
- `wordpress/wp-content/themes/southforsyth/inc/import/class-schools-pilot-command.php`
- `wordpress/wp-content/themes/southforsyth/docs/`
- Staging report output outside the theme directory

## Permitted changes
- Improve report formatting.
- Add non-mutating report fields.
- Write generated reports to the overnight run directory.

## Forbidden changes
- Do not publish schools.
- Do not change post statuses.
- Do not modify production.
- Do not invent editorial facts.

## Required verification
- `scripts/verify-theme.sh`
- Run report command on staging.
- Confirm report is read-only.

## Success criteria
- Report includes completeness, source URL, classification, missing fields, duplicate status, and preview links.
- No database content changes occur.

## Stop conditions
- Report command mutates content unexpectedly.
- Required staging data is absent.
- Publication decisions are implied or automated.

## Required final report
- Attach or reference the generated report path and summarize readiness counts.
