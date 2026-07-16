# Implement the parks provider and importer

## Objective
Implement a staging-safe parks provider/importer that follows the documented parks ingestion plan and imports valid park records as drafts only.

## Files likely involved
- `wordpress/wp-content/themes/southforsyth/inc/providers/`
- `wordpress/wp-content/themes/southforsyth/inc/import/`
- `wordpress/wp-content/themes/southforsyth/inc/meta.php`
- `wordpress/wp-content/themes/southforsyth/functions.php`
- `wordpress/wp-content/themes/southforsyth/docs/data-integration-roadmap.md`

## Permitted changes
- Add provider class, command class, registration, and validation hooks.
- Add dry-run support with live-validation parity.
- Add documentation for command syntax.

## Forbidden changes
- Do not run production imports.
- Do not publish parks.
- Do not weaken existing school importer behavior.
- Do not invent missing park facts.

## Required verification
- `scripts/verify-theme.sh`
- Staging dry run.
- Validation evaluation for incomplete records.
- Confirm all imports would be drafts.

## Success criteria
- Dry run accurately predicts live import outcomes.
- Invalid records are skipped before writes.
- Command is registered only for WP-CLI.

## Stop conditions
- Source data cannot be parsed reliably.
- Dry run/live parity cannot be proven on staging.
- Any command would publish or delete content.

## Required final report
- Include command syntax, source details, dry-run counts, validation failures, and changed files.
