# Platform architecture

**Status: infrastructure plus the first real ingestion vertical.** Every
class described below is real code, and Forsyth County Schools now has a
source-to-review-ready-draft workflow. No cron job runs anything
automatically. No API keys are configured. This document describes a
scalable *foundation* for pulling in outside data (the explicit goal of
this work), with schools as the model vertical. See
[data-integration-roadmap.md](data-integration-roadmap.md) for the
source-by-source ingestion plan this infrastructure implements, and
[content-platform-architecture.md](content-platform-architecture.md) for
the content model (post types, taxonomies, meta) this all writes into.

## Why this still lives in the theme, and when that should change

This is a **theme**, not a plugin, and everything below — post types,
providers, the import engine, the admin menu — is theme code. That already
matched the pre-existing convention (the original nine post types were
theme-based too), so extending it kept the codebase consistent rather than
splitting content logic across a theme and a new plugin mid-project.

The tradeoff: if this theme is ever replaced or majorly redesigned, all of
this — the data platform, not just the visual design — disappears with it.
That's fine for now, at "infrastructure with no data in it yet" scale. It
stops being fine the moment real imported content exists in the database
and depends on this code to keep functioning. **The concrete trigger to
extract this into a `southforsyth-platform` plugin:** the first time a real
provider is fully wired up and importing real content on a schedule. Do it
before that point, not after — post types, taxonomies, and post meta
survive a theme switch far more easily than a half-migrated one.

## Folder structure

```
inc/
  providers/     Phase 1 — external data source integrations
  cache/         Phase 5 — the transient-based cache layer
  import/        Phase 4 — the import pipeline + its two custom DB tables
  search/        Phase 8 — normalized cross-post-type search
  admin/         Phase 6 — the "Community Platform" wp-admin menu
  automation.php Phase 9 — wireable (unscheduled) refresh/expiry hooks
  post-types.php Phase 2 — extended with 3 new post types (see below)
  taxonomies.php Phase 3 — extended with 7 new taxonomies (see below)
  meta.php       extended with sf_lat/sf_lng and article source-attribution fields
page-templates/
  hub.php        pre-existing hub page template (unchanged by this work)
template-parts/components/
  trail-card.php, topic-card.php   new card components for the 2 new content-bearing post types
docs/
  platform-architecture.md   this file
```

## Content types

**Nine of the twelve post types this work was scoped against already
existed** (event, restaurant, park, neighborhood, school, church, business,
guide, article — see `inc/post-types.php` and
`content-platform-architecture.md`). Re-registering them would either
silently no-op (same slug, same args) or, worse, drift out of sync with the
existing definitions if edited in two places. Only the three genuinely new
ones were added:

| Post type | Archive URL | Why it's separate from an existing type |
|---|---|---|
| `trail` | `/trails/` | Previously content within the `park` hub ("Parks & Trails"). Split out so distance/surface/difficulty can be first-class fields and its own hub page, rather than a park sub-category. The `park` hub was renamed back to "Parks" and now links to Trails instead of describing it. |
| `topic` | `/topics/` | Pillar "topic cluster" pages (e.g. "Moving", "Family Activities") that pull related Guides and Articles together via the new `sf_topic` taxonomy — a common SEO pattern for a site planning to scale into thousands of Guide/Article pages. |
| `community_resource` | `/community-resources/` | General civic/community resources not covered by a more specific type — libraries, senior resources, government services, public safety. Reuses `directory-card.php`, matching how `business` already does. |

`trail` and `community_resource` were added to the shared "directory" meta
field group (`sf_address`/`sf_phone`/`sf_website`/`sf_hours`, plus the two
new geo fields below) and to `sf_area`, so they behave exactly like the
other location-bound types.

**Two new post meta fields**, shared across every directory-style post
type: `sf_lat` / `sf_lng`. `data-integration-roadmap.md` already flagged
these as "a prerequisite for any GIS ingestion" before this work started —
they're consumed today by `Southforsyth_Openstreetmap_Provider` and
`Southforsyth_Google_Places_Provider`. **Two more** (`sf_source_url`,
`sf_source_published`) were added to `article` for RSS-imported content's
source attribution.

## Taxonomies

Same dedup logic as post types: **Category, Tags, Business Type, Cuisine,
Church Denomination, and Park Type were not re-created** — they already
exist as WordPress core `category`/`post_tag` or the existing
`sf_business_category`/`sf_cuisine`/`sf_denomination`/`sf_park_amenity`.
Registering a second, parallel "cuisine-like" taxonomy would make content
editors choose between two fields with the same meaning on every
restaurant — a maintainability regression, not the "reusable taxonomies"
the brief asked for. Reusing what already exists **is** the reusable
outcome.

Seven taxonomies were genuinely new, four of them forming a deliberate
geographic hierarchy:

```
sf_region  (broadest — e.g. a regional festival with relevance beyond South Forsyth)
  > sf_city    (Cumming, Alpharetta, unincorporated Forsyth County)
    > sf_area    (Halcyon, Vickery, Windermere — pre-existing, unchanged meaning)
      > sf_community  (a specific subdivision/HOA within one area)
```

Plus three non-geographic additions: `sf_audience` (who content is for —
Families, Newcomers, Seniors), `sf_interest` (topical interest — Outdoors,
Dining, Faith, distinct from post-type-specific taxonomies like
`sf_cuisine`), and `sf_topic` (ties Guides and Articles into the `topic`
pillar pages above).

None of the four geographic tiers are required on every post — they exist
so content that genuinely doesn't fit the `sf_area` granularity (a
region-wide festival, a "near Cumming" listing, a specific HOA) has
somewhere to go, not so every post must now be tagged four times over.

## Provider system

Every provider (`inc/providers/`) implements `Southforsyth_Data_Provider`:
`search()`, `fetch()`, `normalize()`, `cache()`. `Southforsyth_Abstract_Provider`
supplies `cache()` (via the cache layer below) and a shared `http_get()`
helper (consistent timeout, User-Agent, JSON decoding) so concrete
providers only implement what's genuinely different about their source.

| Provider | Status | Notes |
|---|---|---|
| `Southforsyth_Weather_Provider` | **Working** | api.weather.gov — free, keyless. Directly fulfills the TODO already in `weather-placeholder.php`. |
| `Southforsyth_Events_Provider` | **Working** | Dependency-free ICS/iCalendar parser, exactly as scoped in `data-integration-roadmap.md` ("not a Composer package"). Recurrence (RRULE) is flagged, not expanded — that's still future work per that document. |
| `Southforsyth_Openstreetmap_Provider` | **Working** | Nominatim geocoding. Keyless but rate-limited (~1 req/sec) and requires a real User-Agent — both already handled. |
| `Southforsyth_Rss_Provider` | **Working** | WordPress core's own `fetch_feed()` (SimplePie) — no external library. Returns excerpt + link only, never full content, per the legal rule in `data-integration-roadmap.md`. |
| `Southforsyth_Google_Places_Provider` | **Ready, not configured** | Requires a billing-enabled API key (Settings admin page). Returns empty results, never fabricated data, until one exists. |
| `Southforsyth_Forsyth_County_Provider` | **Working** | Real scraper against `www.forsyth.k12.ga.us`, built from live research, not a guessed endpoint — see "Forsyth County Schools importer" below and `data-integration-roadmap.md`. No API exists; this parses server-rendered HTML with PHP's built-in `DOMDocument`/`DOMXPath`, respecting the site's declared `Crawl-delay: 5` and never requesting its robots.txt-disallowed staff directory. |
| `Southforsyth_Nces_Provider` | **Ready, not configured** | Same configurable-endpoint pattern; NCES publishes its Common Core of Data as downloadable files, not a simple keyless API. Targets `school`, for enrichment/verification fields, not narrative content. |
| `Southforsyth_Census_Provider` | **Ready, not configured** | Requires a free Census API key (Settings admin page). Targets `neighborhood`; normalizes only numbers (population, income, age) via `sf_census_*` meta — never descriptive prose. |
| `Southforsyth_Traffic_Provider` | **Ready, not configured** | Same configurable-endpoint pattern, for GDOT/511 Georgia. |

### How to add a provider

1. Create `inc/providers/class-my-provider.php`, extending
   `Southforsyth_Abstract_Provider`.
2. Implement `search()`, `fetch()`, `normalize()` — `normalize()` must
   return `Southforsyth_Normalizer::shape([...])` (see `inc/import/class-normalizer.php`)
   so the importer can consume it regardless of source.
3. `require_once` it from `inc/providers/providers.php`.
4. Add one line to `Southforsyth_Provider_Registry::definitions()`.
5. If it needs credentials, add an option field to
   `Southforsyth_Admin_Menu::render_settings()` — never hardcode a key or
   put it in `.env` (that file is deployment credentials only).

### Worked example: the Forsyth County Schools importer

`Southforsyth_Forsyth_County_Provider` is the concrete example of everything
above, built from live research rather than a guessed integration:

- **robots.txt respected, not assumed.** The district's site declares
  `Crawl-delay: 5` for every user agent — `fetch()` sleeps 5 seconds before
  each real request. The staff directory
  (`/schools/directions-contact-information/staff-directory-for-schools`) is
  explicitly disallowed, so principal names are never scraped from it —
  `sf_principal_name` stays empty from this provider on purpose, not by
  oversight.
- **Only fields with a clean, structured source get scraped.** The district
  overview page (`/schools/about`) is real server-rendered HTML with four
  `<section class="fsPanel">` blocks (Elementary/Middle/High/Academies),
  parsed via `DOMXPath` — no external library. Each school's own page
  carries a consistent `fsLocationSingleItem` component with dedicated
  street/city/state/zip/phone sub-fields, also parsed via `DOMXPath`. Mascot,
  colors, mission, feeder pattern, and attendance-boundary URL have no
  equivalent structured field on that page — rather than text-mine for them
  (which risks silently wrong data across 40+ schools with unknown
  structural variance), this provider leaves them empty. That's a scope
  decision, not a bug — see `data-integration-roadmap.md` for what a
  deliberate follow-up pass would need.
- **South Forsyth classification stays conservative and explicit.** Coverage
  uses exactly `Confirmed South Forsyth`, `Needs Review`, and `Outside
  Coverage`. Automatic confirmation is limited to the central allowlist:
  South Forsyth High School, Denmark High School, and Lambert High School.
  Broader address, ZIP, corridor, and feeder context belongs in manual review
  evidence; the classifier never confirms solely by city name, corridor
  keyword, ZIP, or fabricated feeder pattern.
- **`wp southforsyth import-schools`** (`inc/import/class-forsyth-county-import-command.php`,
  this theme's first WP-CLI command, registered only when `WP_CLI` is
  defined) drives it: `--dry-run`, `--school=<name>`, `--update-only`,
  `--limit=<n>`, `--south-forsyth-only`, `--verbose`. Always imports as `draft` — that's
  `Southforsyth_Importer::import()`'s own default, not a flag this command
  can override.

## Import system

`Southforsyth_Importer::import($record, $args)` is the one place a
normalized record becomes a WordPress post: **validate → dedupe → slug →
insert/update → tag source meta → (optionally) download image → log.**
Nothing here ever writes directly to `publish` — every import lands as
`draft` (or `pending` if `$args['status']` says so), per
`data-integration-roadmap.md`'s "Import queue strategy" rule 1.

- **`Southforsyth_Data_Validator`** enforces the roadmap's per-type minimum
  fields (an Event needs a date + venue; a Restaurant/Business/Church/School
  needs an address; an Article needs a real excerpt, not one sentence).
- **`Southforsyth_Duplicate_Detector`** dedupes on `(source, source_id)`
  first, falling back to a content hash — the exact two-tier strategy the
  roadmap doc specifies.
- **`Southforsyth_Slug_Generator`** wraps `wp_unique_post_slug()` — no
  reason to reinvent WordPress's own collision handling.
- **`Southforsyth_Image_Downloader`** will not download or attach an image
  unless the caller explicitly passes `rights_confirmed => true` — there is
  no code path that defaults this to true, per the roadmap's "Images
  require rights confirmation" rule.
- **`Southforsyth_Import_Queue`** and **`Southforsyth_Import_Logger`** are
  the two consumers of the custom tables below.

### Why custom tables, not custom post types, for the queue and log

Every other new "data model" in this project (Trail, Topic, Community
Resource) is a WordPress post, and that's the right call for *content*
that gets a URL, a title, and an editor. A queue job or a log line is
neither — it's operational bookkeeping, and this project is explicitly
scoped to scale to **hundreds of thousands of content pages**. Logging
every import attempt as a `wp_posts` row would make that table the
bottleneck for every future post-listing query on the site, for data
nobody browses as content. Two small custom tables
(`{prefix}sf_import_queue`, `{prefix}sf_import_log`), created via
`dbDelta()` — the same WordPress-native schema-migration mechanism core
and most serious plugins use — keep operational data out of the content
tables entirely. `Southforsyth_Import_Install::maybe_install()` runs on
`after_switch_theme` and self-heals on `admin_init`, gated by a version
option so it doesn't re-run `dbDelta()` on every admin request.

## Cache layer

`Southforsyth_Cache_Manager` (`inc/cache/`) wraps WordPress transients:
`get()`/`set()`/`delete()` for direct use, `remember($key, $ttl, $resolver)`
for read-through caching (what every provider's `cache()` calls), and
`refresh()` to force a re-fetch regardless of what's cached (what "manual
refresh" and the Phase 9 automation hooks both use).

**Redis compatibility today, not later:** WordPress transients
automatically use the site's persistent object cache when one is
configured (e.g. a Redis object-cache drop-in) instead of the options
table. Nothing in this class needs to change to move onto Redis — only a
persistent object cache needs to be installed at the hosting level.

## Admin area

One top-level "Community Platform" menu (`inc/admin/class-admin-menu.php`),
capability-gated to `manage_options`, with seven pages: **Providers**
(status per provider), **Imports** (a manual "process next queued job"
button, for testing a provider end-to-end before any automation exists),
**Queues** (job counts + recent jobs), **Logs** (recent log entries +
clear), **Content Status** (published/draft/pending counts per post type),
**Statistics** (total content, total imported-via-pipeline, queue
summary), and **Settings** (provider API keys/feed URLs via the WordPress
Settings API — into `wp_options`, never `.env`). Deliberately unstyled
beyond core admin CSS classes (`.wrap`, `.widefat`) — these are
operational tools, not public-facing UI.

An eighth page, **Suggestions**, is attached the same way
(`add_submenu_page()` targeting `Southforsyth_Admin_Menu::SLUG`) but lives
in `inc/community/class-suggestion-moderation.php`, not
`class-admin-menu.php` itself — see "Community suggestion moderation"
below for why it's kept separate.

## School Editorial Review

The Schools list table (`Posts → Schools`) is extended, not replaced —
`inc/admin/class-school-list-columns.php` adds review columns (level,
South Forsyth status, principal, source, last verified, completeness, and
a "Details" column flagging missing fields / no coordinates / a possible
duplicate title) plus filter dropdowns (`restrict_manage_posts`/
`pre_get_posts`) and bulk actions
(`bulk_actions-edit-school`/`handle_bulk_actions-edit-school`) for the four
South Forsyth statuses plus publish/unpublish. Draft/Published filtering
itself needs no new code — WordPress's native status links already do
that for every post type. Every bulk handler calls
`current_user_can('edit_post', $post_id)` (or `publish_post` for the
publish action) per post before acting, on top of the list table's own
access gate — a bulk action must never be a way to bypass normal
capabilities.

Completeness is precomputed into `sf_completeness_pct` on `save_post_school`
(rather than computed on the fly per list-table row) specifically so the
Completeness filter can use a normal, indexable `meta_query` instead of a
PHP-side filter that would silently break the list table's pagination.

## Community suggestion moderation

`inc/community/community.php` registers `sf_suggestion` (see
`content-platform-architecture.md`'s "Community suggestions") and its
custom statuses, then loads two collaborating classes:

- **`Southforsyth_Suggestion_Handler`** — the public-facing
  `admin_post`/`admin_post_nopriv` submission endpoint. Runs cheapest and
  most bot-revealing checks first: nonce → honeypot (silent fake success,
  no signal to the bot) → per-IP-hash rate limit → validation/sanitization
  → `wp_insert_post()` as `pending`. Never touches the target post.
- **`Southforsyth_Suggestion_Moderation`** — a meta box on `sf_suggestion`'s
  native edit screen (target page, current value, an *editable* proposed
  value, explanation, source URL, submitter identity) plus the
  `admin_post_southforsyth_moderate_suggestion` handler. Every path re-checks
  `current_user_can('edit_others_posts')` and the moderation nonce
  regardless of what the UI shows. Only **Approve** on a *structured*
  suggestion (one whose `sf_requested_field` matches a real meta key, from
  `southforsyth_get_suggestible_fields()`) writes to the target post —
  exactly that one field, the moderator's final edited text, never a
  different field than the one shown. A freeform ("other") approval records
  the decision and still counts as a community update (see the trust
  signals in `content-platform-architecture.md`) but writes nothing
  structured — the moderator applies freeform feedback by hand in the
  normal editor.

Kept in `inc/community/`, not folded into `inc/admin/class-admin-menu.php`,
because it's genuinely a different concern (a public write-path plus its
moderation, not an operational read-only dashboard) with its own file the
way `inc/import/` and `inc/providers/` are already separated by concern
rather than everything living in one admin file.

## Search architecture

`Southforsyth_Search_Service::search($term, $args)` (`inc/search/`) queries
across every registered platform post type (all twelve) plus core
posts/pages, returning results in the same normalized card shape
(`eyebrow`/`title`/`description`/`link`) every card component already uses.

This is **additive, not a replacement** for `search.php`, which keeps
working exactly as it does today — WordPress's native search already
covers every public post type automatically. This service exists for
anything that needs *programmatic*, normalized results instead of a
`WP_Query` loop: a future AJAX live-search box, a `/wp-json/southforsyth/v1/search`
REST endpoint, or a unified search across the eventual hundreds of
thousands of pages without every consumer re-implementing normalization.

## Automation readiness (no cron jobs yet)

`inc/automation.php` registers real callback functions
(`southforsyth_refresh_weather`, `southforsyth_refresh_traffic`,
`southforsyth_refresh_events`, `southforsyth_expire_stale_content`) against
plain WordPress action hooks — **`wp_schedule_event()` is never called
anywhere in this codebase.** Nothing runs on a schedule today. To actually
automate one:

```php
wp_schedule_event(time(), 'hourly', 'southforsyth_refresh_weather');
```

This maps directly onto the phased rollout already planned in
`data-integration-roadmap.md` ("Future automation phases") — prove a
source reliable via the Imports admin page's manual trigger first, *then*
schedule it, matching that document's Phase 1 → Phase 2 progression.

## How a new content type plugs into the ingestion framework

The ingestion framework — providers, `Southforsyth_Importer`, the shared
directory meta group, FAQs, related/nearby — was built generic from the
start rather than per-post-type, so "adding a content type" and "adding a
data source" are both short, mechanical checklists, not new systems. This
section is the reference for both; Schools is the worked example throughout
the codebase for every step below.

**Prefer reuse over a new post type.** Before adding a new CPT, check
whether an existing one already fits — `community_resource` is deliberately
reused for sports organizations, government facilities, libraries, senior
resources, and public safety (distinguished by the `sf_resource_type`
taxonomy, seeded in `inc/resource-provisioning.php`) rather than splitting
each into its own type, the same dedup principle already applied to
`business`/`trail`/`topic`. Only add a new post type when the content is
genuinely structurally different, the way `trail` needed distance/surface/
difficulty fields `park` didn't have.

**Adding a genuinely new content type:**

1. Add an entry to `southforsyth_get_post_type_definitions()` in
   `inc/post-types.php` (title, slug, supports, card_template).
2. Create its card component in `template-parts/components/`, matching an
   existing one's structure (see `trail-card.php`/`topic-card.php` for the
   two newest examples) — reuse an existing card (like `directory-card.php`)
   if the new type is genuinely directory-shaped.
3. If it's directory-shaped (has an address, phone, hours, etc.), add it to
   `southforsyth_get_directory_meta_post_types()` in `inc/meta.php` instead
   of duplicating the field list — this single line is what grants the new
   type `sf_address`/`sf_phone`/`sf_website`/`sf_hours`/`sf_lat`/`sf_lng`/
   `sf_source_url`/`sf_last_verified`/`sf_faqs` *and* related/nearby-places
   support (`southforsyth_get_related_entities()`/`get_nearby_places()` in
   `inc/queries.php` already query across every post type in that list) —
   nothing else to wire up. Add any genuinely type-specific fields (the way
   `school` has `sf_grades_served`/`sf_principal_name`/etc.) as their own
   small group, the same pattern used there.
4. Attach it to existing taxonomies where the meaning already fits (see
   `inc/taxonomies.php`'s dedup notes) before creating a new one. If it
   needs its own classification facet the way `sf_school_type` serves
   Schools, add one hierarchical taxonomy and seed its terms with a small
   provisioning file matching `inc/school-provisioning.php`/
   `inc/resource-provisioning.php`'s idempotent pattern.
5. `archive.php`, `search.php`, and `single.php` pick it up automatically —
   no template changes needed. `single.php` already renders FAQs and
   related/nearby for any post type in `southforsyth_get_directory_meta_post_types()`.
6. If it should have its own hub-page-style intro/FAQ/samples, add an entry
   to `southforsyth_get_hub_definitions()` in `inc/hub-content.php`.

**Adding a new data source (provider):**

1. Create `inc/providers/class-my-provider.php`, extending
   `Southforsyth_Abstract_Provider`. Follow whichever existing provider
   matches the source's real access method — a configurable, inert-until-set
   feed URL (`class-forsyth-county-provider.php`) if no simple public API is
   confirmed, or an API-key-gated pattern (`class-google-places-provider.php`,
   `class-census-provider.php`) if one exists but requires credentials. Never
   guess at or hardcode an endpoint for a source with no identified target —
   document it in `data-integration-roadmap.md` as a future candidate
   instead (see "public GIS"/"government datasets" there).
2. Implement `search()`, `fetch()`, `normalize()` — `normalize()` must
   return `Southforsyth_Normalizer::shape([...])` (see
   `inc/import/class-normalizer.php`), including `'raw' => $raw` so the
   original payload survives as `_sf_import_raw` postmeta for attribution
   and re-processing. Map only structured fields into `meta` — no source
   should ever synthesize prose (summary, history, FAQ text) into a normalized
   record; those stay human-authored, per `data-integration-roadmap.md`'s
   review rules.
3. `require_once` it from `inc/providers/providers.php`.
4. Add one line to `Southforsyth_Provider_Registry::definitions()`.
5. If it needs credentials, add an option field to
   `Southforsyth_Admin_Menu::render_settings()` (and a row to the
   description table in `render_providers()`) — never hardcode a key or put
   it in `.env` (that file is deployment credentials only).
6. `Southforsyth_Importer::import()`, `Southforsyth_Data_Validator`, and
   `Southforsyth_Duplicate_Detector` need no changes — they're already
   provider-agnostic. Only add a `Data_Validator` case if the new post type
   has its own minimum-field rule (the way `school` requires `sf_website` in
   addition to the shared `sf_address` rule).

## Future APIs

Nothing here exposes a public API today. The two concrete, already-
scaffolded next steps:

- **`/wp-json/southforsyth/v1/search`** — a thin REST controller wrapping
  `Southforsyth_Search_Service::search()`, for a future live-search UI.
- **Per-post-type REST endpoints** (`/wp-json/southforsyth/v1/restaurants`,
  etc.) — already sketched per-type in `inc/community-platform.php`'s data
  model definitions; every post type here is already `show_in_rest => true`,
  so WordPress's own `/wp/v2/{post_type}` REST routes work today without
  any custom controller — a custom `southforsyth/v1` namespace would only
  be worth building once a consumer needs a shape the default REST
  response doesn't already provide.
