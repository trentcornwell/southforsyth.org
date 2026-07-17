# WP-CLI Import Plan

This project uses WP-CLI for staff-run imports before any scheduled
automation is enabled. Commands are source-specific; there is no parallel
generic import command.

## Schools

Audit source coverage and title drift, no writes:

```bash
wp southforsyth audit-schools
```

Report existing posts by South Forsyth coverage status, no writes:

```bash
wp southforsyth school-coverage-report
```

Classify existing school drafts/posts, dry run first:

```bash
wp southforsyth classify-schools --dry-run
```

Safe live classification update:

```bash
wp southforsyth classify-schools
```

Correct existing draft titles/slugs to full official names, dry run first:

```bash
wp southforsyth correct-school-titles --dry-run
```

Safe live correction for drafts only:

```bash
wp southforsyth correct-school-titles
```

Dry run, no writes:

```bash
wp southforsyth import-schools --south-forsyth-only --dry-run --verbose
```

Live draft import for Confirmed South Forsyth records only:

```bash
wp southforsyth import-schools --south-forsyth-only --verbose
```

Limit or target while testing:

```bash
wp southforsyth import-schools --dry-run --limit=3 --verbose
wp southforsyth import-schools --school="South Forsyth" --verbose
wp southforsyth import-schools --update-only --verbose
```

School names follow the Forsyth County Schools directory section:
`Elementary Schools` becomes `[Name] Elementary School`, `Middle Schools`
becomes `[Name] Middle School`, and `High Schools` becomes `[Name] High
School`. The suffix is not added twice, and complete official branding such
as `Alliance Academy for Innovation`, `Forsyth Academy`, or `Forsyth Virtual
Academy` is preserved exactly instead of being forced into a normal level
suffix.

School identity is the official source ID/source URL. A shared shortened name
does not merge records; for example, South Forsyth Middle School and South
Forsyth High School remain separate when their `/fs/pages/NNNNN` URLs differ.

Basic publication readiness requires a complete official name, source ID or
source URL, official website, address, city/state/ZIP, phone when officially
available, school type, district, last verified date, and no unresolved
duplicate conflict. Grades served, principal, latitude/longitude, boundary
link, feeder pattern, notable programs, mission, mascot, and colors are
enrichment warnings rather than publication blockers.

Coverage status uses exactly three values: `Confirmed South Forsyth`, `Needs
Review`, and `Outside Coverage`. Automatic confirmation is limited to the
central allowlist: South Forsyth High School, Denmark High School, and Lambert
High School. Middle and elementary schools stay Needs Review unless an editor
records official boundary, attendance-map, feeder/serving-area, address-with-
boundary, or manual editorial evidence. The classifier must not confirm solely
by city name, corridor keyword, ZIP, or fabricated feeder pattern. Public
school queries and homepage school cards show only Confirmed South Forsyth
schools.

Geocoding dry run:

```bash
wp southforsyth geocode-schools --dry-run --verbose
```

Geocoding write pass:

```bash
wp southforsyth geocode-schools --verbose
```

Editorial review report:

```bash
wp southforsyth schools-pilot
```

Confirmed South Forsyth only:

```bash
wp southforsyth schools-pilot --confirmed-only
```

Dry-run publishing every eligible Confirmed South Forsyth draft school:

```bash
wp southforsyth publish-confirmed-schools --dry-run --verbose
```

Live publishing every eligible Confirmed South Forsyth draft school:

```bash
wp southforsyth publish-confirmed-schools
```

Guarded publish helper, after manual review:

```bash
wp southforsyth schools-pilot --publish=<id,id> --reviewer="Reviewer Name"
```

Rollback a mistaken publish:

```bash
wp post update <id> --post_status=draft
```

`publish-confirmed-schools` is idempotent. It never creates posts from source
records, never changes already published schools, and never publishes schools
with `Needs Review`, `Outside Coverage`, missing required metadata, invalid
website/source fields, or unresolved duplicate conflicts. Dry-run output is
grouped into:

- schools that would publish
- schools blocked/protected from publishing, with exact blocker reasons
- official source records without an existing school post
- totals for existing posts, publishable posts, blocked/protected posts, and missing source records

## Exit Behavior

- Source-level failures such as an unreachable directory trigger `WP_CLI::error`.
- Per-record failures do not stop the full import.
- Invalid, ambiguous, protected, or incomplete records are skipped and counted.
- Successful dry runs and imports return success after printing summaries.

## Future Sources

Future parks, churches, restaurants, and event importers should follow the
school pattern:

1. Confirm the source and access policy.
2. Build one provider for that source.
3. Normalize into `Southforsyth_Normalizer::shape()`.
4. Use the shared importer.
5. Keep source-specific safety rules in a small helper, not in a second import system.
6. Draft first, review second, publish manually.
