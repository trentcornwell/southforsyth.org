# Platform architecture

**Status: infrastructure, not data.** Every class described below is real
and working code — but nothing has been used to actually import content.
No cron job runs anything automatically. No API keys are configured. This
document describes a scalable *foundation* for pulling in outside data
(the explicit goal of this work), not a site that has done so yet. See
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
| `Southforsyth_Forsyth_County_Provider` | **Ready, not configured** | No confirmed county API exists yet (see roadmap doc); reads a configurable feed URL so it starts working the moment one is confirmed, with zero code changes. |
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

## How to add a new content type

1. Add an entry to `southforsyth_get_post_type_definitions()` in
   `inc/post-types.php` (title, slug, supports, card_template).
2. Create its card component in `template-parts/components/`, matching an
   existing one's structure (see `trail-card.php`/`topic-card.php` for the
   two newest examples) — reuse an existing card (like `directory-card.php`)
   if the new type is genuinely directory-shaped, per the dedup principle
   used for `community_resource` above.
3. If it needs its own meta fields, add them in `inc/meta.php`; if it's
   directory-shaped, just add it to
   `southforsyth_get_directory_meta_post_types()` instead of duplicating
   the field list.
4. Attach it to existing taxonomies where the meaning already fits (see
   `inc/taxonomies.php`'s dedup notes) before creating a new one.
5. `archive.php` and `search.php` pick it up automatically — no template
   changes needed, matching the "one archive.php, not eighteen" principle
   from `content-platform-architecture.md`.
6. If it should have its own hub-page-style intro/FAQ/samples, add an entry
   to `southforsyth_get_hub_definitions()` in `inc/hub-content.php`.

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
