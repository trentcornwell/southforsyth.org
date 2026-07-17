# Data Import System

SouthForsyth.org now has a real import foundation plus one active vertical:
Forsyth County Schools. The school pipeline is the model for future parks,
churches, restaurants, and events work.

## School Pipeline

Source:
- `Southforsyth_Forsyth_County_Provider`
- Official Forsyth County Schools public pages
- Parsed as server-rendered HTML with `DOMDocument`/`DOMXPath`
- Respects the district crawl delay

Flow:
1. Provider finds school stubs from the district directory.
2. Provider expands directory names into complete official display names.
3. Provider fetches each school page and normalizes official facts.
4. `Southforsyth_School_Import_Safety` validates, normalizes, and checks duplicate/update safety.
5. `Southforsyth_Importer` creates new draft schools or updates confident draft matches.
6. Published schools, non-draft matches, ambiguous duplicates, and invalid records are skipped and reported.
7. Geocoding runs as a separate review step.
8. `wp southforsyth schools-pilot` prints the editorial readiness report.

The importer never auto-publishes schools.

## School Naming and Identity

Forsyth County's directory labels some schools with shortened names. The
provider stores complete display names for regular schools:

- `Elementary Schools` -> `[Name] Elementary School`
- `Middle Schools` -> `[Name] Middle School`
- `High Schools` -> `[Name] High School`

The post title should match the official published school name whenever
possible. The suffix is not duplicated when the source already includes it,
and complete official branding such as `Alliance Academy for Innovation`,
`Forsyth Academy`, or `Forsyth Virtual Academy` is preserved instead of being
forced into a normal elementary/middle/high suffix.

Stable identity comes from the official `/fs/pages/NNNNN` URL, stored both as
`source_id`/`_sf_import_source_id` and `sf_source_url`. Shortened matching
names are not enough to merge records. If two official source URLs represent
different schools, they must remain separate WordPress posts even when their
short names collide.

## Safety Rules

- New official school records create `draft` posts.
- Confident duplicate matches update existing drafts only.
- Published schools are never overwritten automatically.
- Duplicate source IDs or source URLs are skipped for human review.
- Ambiguous shortened names are reported by audit, but are not merged on name alone.
- South Forsyth coverage uses exactly three statuses: `Confirmed South Forsyth`, `Needs Review`, and `Outside Coverage`.
- Automatic imports preserve existing Confirmed/Outside editorial classifications.
- Public school queries and homepage school cards show only `Confirmed South Forsyth` schools.
- Unknown source values stay empty.
- Raw source payload is stored as `_sf_import_raw`.
- Source attribution is stored through `_sf_import_source`, `_sf_import_source_id`, `_sf_import_hash`, `_sf_import_fetched_at`, and `sf_source_url`.
- Geocoding preserves existing coordinates and stores replacement candidates for review.

## South Forsyth Coverage

The county directory remains the source of truth for identity and dedupe, but
SouthForsyth.org prepares and publishes only schools that genuinely serve the
South Forsyth coverage area. The automatic classifier is intentionally
conservative: it confirms only schools on the central allowlist or schools
with an explicit manual decision. It does not classify solely by city name,
does not confirm from one corridor keyword, and does not fabricate feeder
patterns.

Coverage decision provenance is stored with each school:

- `sf_south_forsyth_status`
- `sf_coverage_decision_source`
- `sf_coverage_decision_note`
- `sf_coverage_decision_date`
- `sf_coverage_decision_type` (`manual` or `automatic`)

Initial conservative confirmed allowlist:

- South Forsyth High School
- Denmark High School
- Lambert High School

Middle and elementary schools remain `Needs Review` until an editor records
official boundary, official attendance-map, official feeder/serving-area,
official address-with-boundary, or manual editorial evidence.

Outside automatic signals:

- Clearly non-South-Forsyth county school/community names: North Forsyth, East Forsyth, West Forsyth, Forsyth Central, Coal Mountain, Chestatee, Cumming, Matt, Sawnee, Kelly Mill, Otwell, Liberty, Little Mill, Lakeside, Silver City, Poole's Mill, Mashburn, Chattahoochee
- Outside corridors/communities: Coal Mountain, Matt Highway, Dahlonega Highway, Spot Road, Tribble Gap, Keith Bridge, Little Mill, Jot Em Down, Gainesville Highway
- Outside supporting ZIPs: 30506, 30534

Everything else remains `Needs Review`.

## Publication Readiness

Required for basic verified publication:

- Complete official school name
- Official source ID or source URL
- Official website
- Address
- City/state/ZIP
- Phone when officially available
- School type
- District
- Last verified date
- No unresolved duplicate conflict

Recommended enrichment fields are warnings, not blockers:

- Grades served
- Principal
- Latitude/longitude
- Boundary link
- Feeder pattern
- Notable programs
- Mission
- Mascot
- Colors

## Current Commands

Recommended order:

```bash
wp southforsyth audit-schools
wp southforsyth school-coverage-report
wp southforsyth classify-schools --dry-run
wp southforsyth classify-schools
wp southforsyth correct-school-titles --dry-run
wp southforsyth correct-school-titles
wp southforsyth import-schools --south-forsyth-only --dry-run --verbose
wp southforsyth import-schools --south-forsyth-only --verbose
wp southforsyth geocode-schools --dry-run --verbose
wp southforsyth geocode-schools --verbose
wp southforsyth publish-confirmed-schools --dry-run --verbose
wp southforsyth schools-pilot
```

Publishing remains staff-controlled. To publish every eligible Confirmed South
Forsyth draft school, dry-run first:

```bash
wp southforsyth publish-confirmed-schools --dry-run --verbose
wp southforsyth publish-confirmed-schools
```

The command only considers `school` posts marked `Confirmed South Forsyth`,
only publishes drafts, skips already published posts, refuses Needs Review and
Outside Coverage posts, and refuses unresolved duplicate conflicts. Its report
also compares the official countywide source directory to existing school posts
and lists source records that do not yet have a WordPress school post; those
records are reported only, never created by the publish command. Roll back a
mistaken school publish with `wp post update <id> --post_status=draft`, then fix
the school status/metadata in wp-admin and rerun the dry-run.

The older guarded ID-list helper still exists for one-off reviewed publishes:
`wp southforsyth schools-pilot --publish=<id,id> --reviewer="Reviewer Name"`.

## Homepage and Coverage Page

The homepage includes a "What Is South Forsyth?" section that defines South
Forsyth as a community identity, not a legal municipality. The reusable full
coverage template is `page-templates/coverage.php`; the project intentionally
does not auto-create the page.

Admin steps:

1. Pages > Add New.
2. Title: `What Is South Forsyth?`.
3. Slug: `what-is-south-forsyth`.
4. Template: `What Is South Forsyth?`.
5. Publish when editorial review is complete.

## Known Data Gaps

The current official source does not provide every editorial field in a
structured way. These remain empty until manually verified or enriched from a
second official source or from the individual official school pages:

- `sf_principal_name`
- `sf_boundary_url`
- `sf_feeder_pattern`
- `sf_notable_programs`
- many regular-school `sf_grades_served` values
- `sf_mascot`
- `sf_school_colors`
- `sf_mission`

No code should infer these values from school type alone.
