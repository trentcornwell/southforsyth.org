# Run a staging school import and verify idempotency

## Objective
Run the Forsyth County school importer against staging only, import valid school records as drafts, and prove that a second run updates existing records without creating duplicates or changing statuses.

## Files likely involved
- `wordpress/wp-content/themes/southforsyth/inc/import/class-forsyth-county-import-command.php`
- `wordpress/wp-content/themes/southforsyth/inc/import/class-importer.php`
- `wordpress/wp-content/themes/southforsyth/inc/import/class-duplicate-detector.php`
- Staging database only

## Permitted changes
- Staging database draft imports.
- Staging-only import logs.
- Documentation updates if the observed import counts differ from the task assumptions.

## Forbidden changes
- Do not publish schools.
- Do not run `wp southforsyth schools-pilot --publish`.
- Do not run geocoding.
- Do not delete imported content.
- Do not touch production.

## Required verification
- Confirm staging backup exists before import.
- Run importer once on staging.
- Run importer a second time on staging.
- Confirm imported source IDs are unique.
- Confirm imported school posts are drafts.

## Success criteria
- Valid records import as draft school posts.
- Addressless academy/virtual records remain skipped unless a separate policy has been implemented.
- Second run creates zero duplicate posts.

## Stop conditions
- Any school is published.
- Duplicate source IDs appear.
- Any unrelated post type count changes unexpectedly.
- Staging backup is missing.

## Required final report
- Include first-run counts, second-run counts, skipped records, duplicate checks, and school status counts.
