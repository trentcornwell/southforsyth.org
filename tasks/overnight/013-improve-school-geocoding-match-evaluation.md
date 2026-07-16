# Improve school geocoding match evaluation

## Objective
Improve staging-only school geocoding confidence and match evaluation so candidate coordinates can be reviewed safely before acceptance.

## Files likely involved
- `wordpress/wp-content/themes/southforsyth/inc/import/class-geocode-command.php`
- `wordpress/wp-content/themes/southforsyth/inc/import/class-geocode-match-evaluator.php`
- `wordpress/wp-content/themes/southforsyth/inc/meta.php`
- `wordpress/wp-content/themes/southforsyth/docs/data-integration-roadmap.md`

## Permitted changes
- Add stricter match scoring and clearer review metadata.
- Add report-only geocoding checks.
- Store candidate coordinates in staging only when explicitly using the geocode command.

## Forbidden changes
- Do not accept coordinates automatically unless the existing confidence policy says to.
- Do not run geocoding against production.
- Do not publish schools.
- Do not overwrite verified coordinates without review.

## Required verification
- `scripts/verify-theme.sh`
- Staging geocode dry/report run.
- Spot-check several schools with known addresses.

## Success criteria
- Candidate match reasons are explainable.
- Low-confidence matches are flagged for review.
- No production database access occurs.

## Stop conditions
- Geocoder returns inconsistent or out-of-county results.
- The task would require production data to validate.
- Coordinates would be accepted without a review trail.

## Required final report
- List scoring rules, sample evaluated schools, accepted/rejected candidate counts, and unresolved geocoding decisions.
