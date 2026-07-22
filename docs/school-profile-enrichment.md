# School profile enrichment

School enrichment is an editorial metadata update for the existing published,
Confirmed South Forsyth profiles. It is not a school importer and cannot create,
publish, rename, delete, or reclassify a school.

## Sources and field model

Only HTTPS pages on `forsyth.k12.ga.us` or an official school subdomain are
accepted. Use district, school, attendance-boundary, enrollment, transportation,
and parent-resource pages. Do not copy narrative descriptions. Write
`sf_editorial_summary` in original, neutral language and cite the official facts
used to prepare it.

Existing metadata is reused for grades, principal, boundaries, feeder patterns,
programs, mascot, colors, mission, coordinates, hours, website, source URL, and
verification date. New metadata covers activities, athletics, enrollment/parent/
transportation links, editorial summary, enrichment status, field-level source
notes, and the last enrichment check date.

`sf_enrichment_source_notes` is a JSON map keyed by metadata field. Each entry
contains `source_url`, `source_note`, and `checked_at`, so facts do not depend on
one page-level citation.

## Read-only gap report

```bash
wp southforsyth audit-school-profiles
```

This dedicated audit checks the 19 tracked profile fields for published,
Confirmed South Forsyth schools only and prints per-profile completion plus
aggregate missing-field totals. It performs no external requests or writes.

The enrichment command also retains its shorter operational gap report:

```bash
wp southforsyth enrich-schools --verbose
```

## Reviewed manifest

Prepare a JSON file outside the public web root:

```json
{
  "schools": [
    {
      "official_name": "Example Elementary School",
      "source_id": "https://www.forsyth.k12.ga.us/fs/pages/12345",
      "checked_at": "2026-07-21",
      "status": "verified",
      "fields": {
        "sf_grades_served": {
          "value": "K-5",
          "source_url": "https://example.forsyth.k12.ga.us/about",
          "source_note": "Grade range on the official school page."
        },
        "sf_parent_resources_url": {
          "value": "https://example.forsyth.k12.ga.us/parent-resources",
          "source_url": "https://example.forsyth.k12.ga.us/parent-resources",
          "source_note": "Official parent resources page."
        }
      }
    }
  ]
}
```

The stable source ID is preferred; the normalized complete official name is the
fallback. A target must be both published and Confirmed South Forsyth.

Dry-run first:

```bash
wp southforsyth enrich-schools --file=/safe/path/school-enrichment.json --dry-run --verbose
```

Apply reviewed metadata:

```bash
wp southforsyth enrich-schools --file=/safe/path/school-enrichment.json --verbose
```

The command refuses unsupported fields, non-official sources, ambiguous targets,
and attempts to replace a different non-empty value. Conflicts require manual
editorial review. Dry-run performs no writes.
