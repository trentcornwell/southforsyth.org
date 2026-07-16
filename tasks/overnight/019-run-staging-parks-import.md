# Run a staging parks import as drafts

## Objective
Run the parks importer on staging, create/update valid park records as drafts, and verify idempotency.

## Files likely involved
- Staging database only
- `wordpress/wp-content/themes/southforsyth/inc/import/`
- `wordpress/wp-content/themes/southforsyth/inc/providers/`

## Permitted changes
- Staging draft park imports.
- Staging import logs.
- Report artifacts outside the theme directory.

## Forbidden changes
- Do not publish parks.
- Do not modify production.
- Do not delete imported content.
- Do not reset WordPress.
- Do not import records that fail validation.

## Required verification
- Confirm staging backup exists.
- Run parks dry run.
- Run parks live import on staging only.
- Run parks importer a second time to prove idempotency.
- `scripts/verify-theme.sh`

## Success criteria
- Valid parks are draft posts.
- Second run creates no duplicates.
- Invalid records are skipped with reasons.

## Stop conditions
- Any park is published.
- Duplicate source IDs appear.
- Source failures make the import incomplete.
- Unrelated post-type counts change unexpectedly.

## Required final report
- Include found/create/update/skip/source-failure counts, duplicate checks, post counts, and sample records.
