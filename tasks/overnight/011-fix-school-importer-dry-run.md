# Fix school importer dry-run and live-validation parity

## Objective
Make `wp southforsyth import-schools --dry-run` report the same create/update/skip outcomes that the live importer would produce, including validation failures, without writing posts.

## Files likely involved
- `wordpress/wp-content/themes/southforsyth/inc/import/class-forsyth-county-import-command.php`
- `wordpress/wp-content/themes/southforsyth/inc/import/class-importer.php`
- `wordpress/wp-content/themes/southforsyth/inc/import/class-data-validator.php`
- `wordpress/wp-content/themes/southforsyth/inc/providers/class-forsyth-county-provider.php`
- `wordpress/wp-content/themes/southforsyth/docs/data-integration-roadmap.md`

## Permitted changes
- Refactor dry-run reporting to call the same normalizer, validator, and duplicate detector used by live import.
- Add clearer dry-run summary fields for validation failures and skip reasons.
- Add focused tests or WP-CLI verification notes if a local/staging WordPress environment is available.

## Forbidden changes
- Do not weaken school validation.
- Do not invent addresses or phone numbers.
- Do not import, publish, delete, or reset content.
- Do not deploy to production.

## Required verification
- `scripts/verify-theme.sh`
- Dry-run on staging only.
- Confirm the three addressless academy/virtual records report as skipped before writes.

## Success criteria
- Dry run predicts 42 valid school drafts and 3 validation skips for the current Forsyth County directory.
- Live import behavior and dry-run behavior agree on create/update/skip counts.
- No production access occurs.

## Stop conditions
- Dry run still labels invalid records as `Would create`.
- Any validation rule change would allow addressless school records without a separate content policy.
- Staging WP-CLI is unavailable and behavior cannot be verified safely.

## Required final report
- Summarize changed code paths.
- Include dry-run counts, validation skip names, and verification output.
- State explicitly whether live import parity is proven on staging.
