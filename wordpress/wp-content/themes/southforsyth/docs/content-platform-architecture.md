# Content platform architecture

South Forsyth.org is a community content platform, not a business or brochure
site. The theme models its content as real WordPress custom post types and
taxonomies instead of static placeholder markup, so the archive/single
templates automatically show live content the moment it is published — no
template changes required later. The homepage now mixes live sections with
preview navigation while the content inventory grows; see below.

## Homepage: preview vs. live

The homepage (`front-page.php`) is currently a **preview plus live-query
front door**. It still uses preview-style guide cards, but it now queries
published featured places, events, and guides through `inc/queries.php`:

- It calls `southforsyth_get_featured_places()` and
  `southforsyth_get_latest_items()` for live sections.
- The reason: with zero published posts in most of the nine post types, a
  live-query homepage would either show nothing or show the same generic
  fallback placeholders everywhere — which reads as broken or unfinished
  rather than as a real site. A static, polished "here's what's coming" page
  is more honest and more impressive for a pre-launch site than a live page
  with empty sections.
- Nothing about the content model changed to support this. `archive.php`,
  `search.php`, and `single.php` still work today against any post type the
  moment it has published content; the homepage simply chooses which sections
  are live and which remain preview/navigation.
- The remaining preview cards are intentionally navigational: they point to
  real hub/archive URLs while the content inventory grows.
- Unlike before, the "What We're Building" cards (and the primary nav, and
  the homepage's "Jump to a section" row) are no longer dead-end links —
  each `coming-soon-card` now points at a real hub page (see "Hub pages"
  below) via `southforsyth_get_hub_url()`. The "Preview Content" guide
  cards deliberately still have no link, since no Guide post or hub page
  exists behind an invented guide title like "Best Playgrounds in South
  Forsyth" yet — see `coming-soon-card.php`'s doc comment for the rule
  (only pass a `link` when a real page exists behind the card).

## Custom post types

Registered in `inc/post-types.php`. Every type supports title, editor,
excerpt, featured image, and WordPress's native Custom Fields metabox
(`custom-fields`), and is REST-exposed (`show_in_rest`) for the block editor
and any future headless use.

| Post type       | Archive URL             | Purpose |
|-----------------|--------------------------|---------|
| `event`         | `/events/`               | Community events, markets, recurring programming |
| `restaurant`    | `/restaurants/`          | Restaurants, coffee shops, dining |
| `park`          | `/parks/`                | Parks and playgrounds |
| `trail`         | `/trails/`               | Walking/biking trails and greenways — split out of `park` (see platform-architecture.md) |
| `neighborhood`  | `/neighborhoods/`        | Neighborhood profiles (lifestyle, schools, amenities) |
| `school`        | `/schools/`              | Local schools and education resources |
| `church`        | `/churches/`             | Faith communities and volunteer programs |
| `business`      | `/business-directory/`   | Local businesses and service providers |
| `guide`         | `/guides/`               | Evergreen guides (best parks, moving guide, seasonal roundups) |
| `article`       | `/articles/`             | Editorial stories and local news (uses core `category`/`post_tag`) |
| `topic`         | `/topics/`               | Pillar "topic cluster" pages tying Guides/Articles together via `sf_topic` |
| `community_resource` | `/community-resources/` | General civic/community resources (libraries, senior resources, public safety) |

`business` is one content type among nine — it is deliberately not
over-weighted relative to the others, since this is a community platform
first, not a business directory.

**`sf_suggestion` is deliberately not in this table.** It's a real custom
post type (`inc/community/community.php`), but `public => false`, no
archive, no rewrite — moderation-queue data, not content, the same
distinction `platform-architecture.md` already draws for the import
queue/log ("Why custom tables, not custom post types") applied the other
direction: a suggestion *is* post/postmeta shaped (it benefits from the
native edit screen and revision-free simple storage a real DB table
wouldn't give it for free), it just isn't public. See "Community
suggestions" below.

## Taxonomies

Registered in `inc/taxonomies.php`, prefixed `sf_` to avoid colliding with
plugin or core taxonomies later.

- **`sf_area`** is the one cross-cutting taxonomy. It's attached to every
  location-bound post type (`event`, `restaurant`, `park`, `school`,
  `church`, `business`) so any of them can be tagged with a neighborhood/area
  and cross-linked from a Neighborhood profile page — e.g. "restaurants
  tagged with neighborhood X" or "events near neighborhood Y." This is what
  makes "nearby suggestions" and neighborhood-based browsing possible later
  without a redesign.
- Every other taxonomy is scoped to a single post type: `sf_event_category`,
  `sf_cuisine`, `sf_business_category`, `sf_denomination`, `sf_school_type`,
  `sf_park_amenity`, `sf_lifestyle_tag` (neighborhoods), `sf_guide_topic`,
  `sf_resource_type` (`community_resource`). `sf_resource_type` is what makes
  reusing `community_resource` for sports organizations, government
  facilities, libraries, senior resources, and public safety actually
  distinguishable — the same role `sf_school_type` plays for schools —
  rather than adding a new post type per category; terms are seeded by
  `inc/resource-provisioning.php`.
- `article` uses WordPress's built-in `category` and `post_tag` instead of a
  custom taxonomy, since editorial/news content maps directly onto the
  standard WordPress blogging model — no reason to reinvent it.

## Post meta

Registered in `inc/meta.php`, kept deliberately small:

- **Directory fields** (`sf_address`, `sf_phone`, `sf_website`, `sf_hours`,
  `sf_lat`, `sf_lng`, `sf_source_url`, `sf_last_verified`, `sf_faqs`,
  `sf_city`, `sf_state`, `sf_zip`, `sf_district`, `sf_south_forsyth_status`) —
  shared across `restaurant`, `park`, `school`, `church`, `business`,
  `trail`, and `community_resource` rather than duplicating near-identical
  fields per post type. `sf_source_url`/`sf_last_verified` were added for
  the Schools data-model work as a trust signal ("where did this come
  from," "how current is this") that applies identically to every
  directory-style listing, not just schools — the same reasoning
  `sf_lat`/`sf_lng` were added to the whole group rather than one post
  type. `sf_source_url` is the same key `article` already used for
  RSS-imported source attribution (below); this just extends its
  registration to the directory group too. `sf_faqs` (added for the
  ingestion-framework work) is a JSON-encoded array of `{question, answer}`
  pairs, empty by default — rendered through the existing
  `template-parts/components/faq-block.php` (see "Hub pages" below) via
  `southforsyth_get_post_faqs()` in `inc/queries.php`, so per-entity FAQs
  reuse the same component the hub pages already use rather than a second
  FAQ system. `sf_city`/`sf_state`/`sf_zip`/`sf_district` (added for the
  Forsyth County Schools import) are structured geo alongside the existing
  single-string `sf_address`. `sf_south_forsyth_status` is a 3-value
  editorial workflow field (Confirmed South Forsyth / Needs Review /
  Outside Coverage) — meta rather than a
  taxonomy since it's a single mutually-exclusive status, not a browsable
  multi-tag facet, the same reasoning `sf_featured` is meta, not a
  taxonomy. Public school queries show only Confirmed South Forsyth schools;
  countywide records can remain in drafts for dedupe/reference without being
  prepared for publication (see `docs/data-integration-roadmap.md`).
- **Census fields** (`sf_census_population`, `sf_census_median_income`,
  `sf_census_median_age`, `sf_census_source_year`) — scoped to
  `neighborhood` only, fed by `Southforsyth_Census_Provider`. Numbers only,
  never descriptive prose — see `docs/data-integration-roadmap.md`'s GIS/
  open-data section.
- **School fields** (`sf_grades_served`, `sf_principal_name`,
  `sf_boundary_url`, `sf_feeder_pattern`, `sf_notable_programs`,
  `sf_mascot`, `sf_school_colors`, `sf_mission`) — specific to `school`.
  `sf_grades_served` is a precise range (e.g. "PK-5"); the more categorical
  level/sector facets (Elementary/Middle/High,
  Public/Private/Charter/Homeschool Resource) live in the `sf_school_type`
  taxonomy instead of meta, since a hierarchical taxonomy already supports
  tagging a post with more than one term (see "Taxonomies" above and
  `inc/school-provisioning.php`) — no second taxonomy needed to separate the
  two facets. `sf_boundary_url` links out to the district's own official
  attendance-zone page rather than republishing boundary data on this site.
  `sf_mascot`/`sf_school_colors`/`sf_mission` were added for the Forsyth
  County Schools import; none of the three are populated by that provider
  today (no clean structured source was found for them on the pages it
  reads — see `docs/data-integration-roadmap.md`) but the fields exist for
  manual entry or a future, more targeted pass. `sf_mission`, when
  populated, is an official statement carried with `sf_source_url`
  attribution — a sourced fact, not generated prose.
- **Event fields** (`sf_event_date`, `sf_event_time`, `sf_event_venue`) —
  specific to `event`.
- **Article source-attribution fields** (`sf_source_url`,
  `sf_source_published`) — specific to `article`, for RSS-imported content
  (see `Southforsyth_Rss_Provider`).
- **`sf_featured`** — a single boolean reused across `event`, `restaurant`,
  `park`, `neighborhood`, `business`. Checking it is what makes a post
  eligible for the homepage's "Popular Places" section, which deliberately
  mixes post types instead of requiring a dedicated CPT or taxonomy just for
  "things that are popular."
- **Geocoding provenance** (`sf_geocode_provider`, `sf_geocode_place_id`,
  `sf_geocode_date`, `sf_geocode_confidence`) — shared directory group.
  Kept separate from `sf_lat`/`sf_lng` themselves so "where the coordinates
  came from" survives independently — see `Southforsyth_Geocode_Command`
  (`inc/import/class-geocode-command.php`), a deliberately separate pass
  from the Forsyth County scraper, using the existing
  `Southforsyth_Openstreetmap_Provider` unchanged.
- **Community trust signals** (`sf_community_updated`, `sf_contributor_credit`)
  — shared directory group, written only by
  `Southforsyth_Suggestion_Moderation::apply_approval()` when a community
  suggestion is approved. See "Community suggestions" below.
- **`sf_suggestion` fields** — its own group (`inc/meta.php`), not part of
  the shared directory group, `show_in_rest => false`: `sf_target_post_id`,
  `sf_target_post_type`, `sf_requested_field`, `sf_current_value_snapshot`,
  `sf_suggested_value`, `sf_explanation`, `sf_source_url` (reused key),
  `sf_submitter_name`, `sf_submitter_email`, `sf_ip_hash`,
  `sf_moderator_notes`, `sf_approving_moderator`, `sf_resolution_date`,
  `sf_credit_consent`.

All fields are edited through WordPress's native Custom Fields metabox — no
plugin (e.g. ACF) required, matching the "no plugins" constraint.

## Community suggestions

This project's first public write-path: an "Improve this page" form
(`template-parts/components/suggestion-form.php`) on every directory-type
single page, submitting to `Southforsyth_Suggestion_Handler`
(`inc/community/class-suggestion-handler.php`) via `admin_post`/
`admin_post_nopriv` — never a REST endpoint, no JS framework, matching the
theme's existing "no plugins/frameworks" pattern and the FAQ block's own
no-JS `<details>` disclosure.

**Nothing a visitor submits changes anything directly.** Every submission
becomes a `sf_suggestion` post at `pending` status — pure moderation-queue
data (see "Custom post types" above for why this is a real post type but
not a public one). A moderator reviews it on that post's own native edit
screen (a custom meta box, not a new page — see
`Southforsyth_Suggestion_Moderation`), attached under the existing
"Community Platform" admin menu. Only **Approve** ever writes to the
target post, and only the exact field the moderator was shown (or, for a
freeform "other" suggestion, nothing structured at all — the moderator
applies freeform feedback manually in the normal editor). The moderator's
possibly-edited final text is what gets applied, not necessarily the
original submission.

**Abuse prevention**: a nonce, a visually-hidden (not `type="hidden"`)
honeypot field, and a 60-second per-IP-hash rate limit (the IP itself is
never stored — only a salted hash). No file uploads in this first version.
`sf_submitter_email` is registered but never read by any public-facing
template — the privacy boundary is structural, not a convention someone
could forget.

See `docs/data-integration-roadmap.md`'s "South Forsyth classification
policy" for why a classification change and a factual suggestion are
treated as different kinds of review, and `docs/platform-architecture.md`
for the moderation screen's exact hooks.

## Query helpers and the homepage

`inc/queries.php` provides:

- `southforsyth_get_latest_items($post_type, $count, $fallback, $eyebrow)` —
  fetches the latest published posts of a type, normalized into the card
  shape (`eyebrow`, `title`, `description`, `link`) used by every card
  component. Returns `$fallback` when the post type has no published content
  yet.
- `southforsyth_get_featured_places($count, $fallback)` — same idea, but
  queries across every `sf_featured`-eligible post type at once.
- `southforsyth_get_related_entities($post, $count)` /
  `southforsyth_get_nearby_places($post, $radius_miles, $count)` — added for
  the ingestion-framework work. Related = other directory-type posts (any
  type, not just the same one) sharing an `sf_area` term; nearby = other
  directory-type posts within a radius via `sf_lat`/`sf_lng` (plain-PHP
  haversine, no spatial SQL or external geo library). Both return an empty
  array — never a guess — when the post has no `sf_area`/lat-lng set.
  Rendered on `single.php` via `template-parts/components/related-entities.php`
  and `southforsyth_render_mixed_card_grid()` (`inc/helpers.php`), which
  routes each result through its own post type's card component.

`front-page.php` calls these for every section (events, guides, popular
places, restaurants, parks, neighborhoods, schools, churches, business
directory, articles). Each section shows realistic placeholder content today
and will switch to live content automatically as soon as posts are
published — nothing about the template needs to change when that happens.

`inc/helpers.php` adds `southforsyth_render_card_section()`, which wraps a
list of cards in a consistent section (`section-header` + `card-grid` +
optional CTA link) while rendering each card through its own component
(`event-card`, `restaurant-card`, etc.) so every content type keeps its own
visual treatment instead of collapsing to one generic card style.

## Archive and single templates

Rather than one `archive-{post_type}.php` and `single-{post_type}.php` per
post type (18 near-identical files), `archive.php` and `search.php` look up
the right card component for whatever post type is being displayed via
`southforsyth_get_card_template_for_post_type()`, and `single.php` renders a
type-specific meta list (`template-parts/components/post-meta.php`) — event
date/time/venue, or directory address/phone/hours/website — above the normal
content. This keeps the template layer lightweight and DRY as more post
types are added.

## Hub pages

Every top-level IA section — the seven post type archives above, plus
Things To Do, New Resident Guide, and Weekend Guide, which have no post
type of their own — now works as a real "hub page" with intro copy, an
honest empty-state notice, sample category cards, related-section links, an
FAQ, and a newsletter CTA, instead of either a bare card grid or a 404.

**Why one more layer instead of ten near-duplicate templates:** the same
reasoning as "one `archive.php`, not eighteen" above. `inc/hub-content.php`
holds the copy for all ten sections as data (`southforsyth_get_hub_content($key)`,
keyed by post type slug for the seven CPTs or by page slug for the three
static pages), and two templates render it:

- `archive.php` — unchanged in its live-post behavior (the card grid still
  takes over automatically the instant a post type has published content).
  What's new: it now renders hub copy around that grid — an intro above it,
  and, only when the post type has zero published posts, a `card-placeholder`
  "Coming soon" notice plus sample `coming-soon-card` category cards below
  it (see the placeholder content policy below — samples describe
  categories, never real businesses/events). Related-section links, an FAQ,
  and the newsletter block render regardless of post count.
- `page-templates/hub.php` — a `Template Name: South Forsyth Hub Page` page
  template for the three sections with no CPT. Same rendering pattern minus
  the live-post grid, plus a normal `the_content()` loop so an editor can
  add real body copy in wp-admin without touching the template.

**Why the three extra pages are auto-created, not manually built:**
`inc/page-provisioning.php` runs on `after_switch_theme` and `admin_init`
and creates each of the three pages (checking by slug via
`get_page_by_path()`) only if it doesn't already exist, assigning it the
hub template. Without this, the template would exist but
`/things-to-do/`, `/new-resident-guide/`, and `/weekend-guide/` would 404
until someone manually created each page in wp-admin — a rough edge for a
site meant to demo as a working foundation immediately. It's intentionally
idempotent (never edits or recreates a page that already exists, so
hand-edited content is never touched) and short-circuits via a
`southforsyth_hub_pages_provisioned` option once all three exist, so it
doesn't run its lookup queries on every future admin request forever.

**`southforsyth_get_hub_url($key)`** (also in `inc/hub-content.php`) is the
one place a hub key resolves to a URL — `get_post_type_archive_link()` for
a CPT key, `get_permalink()` via `get_page_by_path()` for a static page key.
The primary nav fallback (`inc/menus.php`), the footer's fallback quick
links, the homepage's "What We're Building" cards and "Jump to a section"
row, and every hub page's related-links row all call this instead of
hardcoding `/events/`-style URLs in more than one place.

## Cleanup notes

A few things were fixed or removed while wiring this up:

- `template-parts/content-card.php` called `southforsyth_get_excerpt()`,
  which lived in `inc/template-functions.php` — a file `functions.php` never
  required. Any archive or search page would have fatally errored. Fixed by
  requiring the file.
- Several templates used CSS classes that were never defined
  (`btn--primary`, `grid--2/3`, `card--feature`, `card--editorial`,
  `card--post`, `grid--sidebar`) — the real classes are `btn-primary`,
  `grid-2/3`, `card-feature`, `card-post`, etc. (single dash, BEM-ish but not
  full BEM). Fixed across the theme and added `.card-editorial`,
  `.layout-content`, and `.sidebar` to `assets/css/main.css` for the styles
  that were genuinely missing rather than just misnamed.
- `inc/seo.php` duplicated `inc/schema.php` (same meta tags, same
  `wp_head` hooks) and was never required — removed to avoid two competing,
  never-synchronized implementations.
- `templates/*.php` (Home Page, Guide Page, Directory Listing Page, Topic
  Landing Page, Evergreen Guide Page) rendered raw internal planning arrays
  (e.g. "SEO strategy: …") as if they were real page content. They were
  planning-visualization artifacts, not production templates, and have been
  superseded by the real archive/single templates above — removed.
- `inc/architecture.php` and `inc/evergreen-content.php` (the information
  architecture and evergreen content plan) are kept as-is: they're editorial
  strategy documents encoded as PHP data, useful reference for ChatGPT/Claude
  when writing new Guide/Article content, not something that needs to be
  "built."

## Second-pass cleanup

A follow-up architecture pass fixed several things the first pass introduced
or missed:

- **Two competing breadcrumb implementations existed** —
  `southforsyth_the_breadcrumbs()` in `inc/template-functions.php` (dead,
  never called) and a second function defined inline inside
  `template-parts/components/breadcrumbs.php` (the one actually used, but
  narrower — no post type archive/taxonomy handling, and an unescaped
  `get_the_title()` call). Consolidated into one canonical
  `southforsyth_the_breadcrumbs()`, extended to add the post type's archive
  as a middle crumb on every CPT single (e.g. Home → Restaurants → *title*),
  and to cover post type archives, taxonomy archives, and 404s. The
  component file now only supplies the wrapping `<div class="container">`
  and calls the canonical function — no breadcrumb logic lives in a
  template part.
- **`southforsyth_render_card_grid()` (`inc/helpers.php`) and
  `template-parts/components/card-grid.php` were dead code** — fully
  superseded by `southforsyth_render_card_section()`, which does the same
  job plus per-type card components and an optional CTA link. Removed both.
- **`assets/css/editor.css` was orphaned** — it defined its own small,
  drifted set of design tokens (different color/radius/shadow values than
  `main.css`) but was never enqueued anywhere; `inc/setup.php` calls
  `add_editor_style('assets/css/main.css')`, not `editor.css`. Removed
  rather than leaving a second, silently-diverging token set in the repo.
- **`functions.php` eagerly required `inc/architecture.php`,
  `inc/evergreen-content.php`, and `inc/community-platform.php`** on every
  single request, even though nothing calls the functions they define
  (confirmed via a full-repo grep). That's roughly 1,500 lines of static
  array data parsed per page load for zero runtime benefit. `functions.php`
  no longer requires them eagerly — see the comment there for how to load
  one on demand.
- **CSS had two fully dead rule blocks** (`.feature-strip`, `.section-intro`
  and its children) left over from the pre-CPT homepage markup they used to
  style, plus one no-op rule (`.card-post { padding: 0; }`, which overrides
  nothing since `.card` never sets padding). Removed all three. Added the
  two rules that actually were missing: `.widget--footer` (the class was
  already used in `inc/widgets.php`'s footer sidebars but had no styling)
  and `.card-spotlight .eyebrow { margin: 0; }` (needed once that eyebrow
  became a real `<h2>`, below).
- **`main.css` was one undifferentiated list of rules** — reorganized into
  labeled sections (tokens, base/reset, layout utilities, buttons, cards,
  sections & headings, editorial components, forms, breadcrumbs, header/nav,
  hero, footer, responsive) with a comment on each of the three places where
  source order is load-bearing (modifier classes that share specificity with
  their base class, and the mobile-first media query stack). No rule's
  selector or declarations changed — this was a reorganization, not a
  redesign.
- **`template-parts/components/community-spotlight.php` had no real
  heading** — its "Community spotlight" label was a `<p>` used only as an
  `aria-labelledby` target, the one homepage section without a heading in
  the outline. Changed to an `<h2 class="eyebrow">` (same visual style, real
  heading) and added the CSS rule above to keep the spacing identical.
- **Component doc comments were stale.** Several card components
  (`event-card`, `restaurant-card`, `school-card`, `church-card`,
  `directory-card`, `guide-card`, `article-card`) still said "TODO: replace
  with dynamic data when available" even though `inc/queries.php` and
  `front-page.php` already feed them real `WP_Query` results. Rewritten to
  accurately describe them as data-agnostic display components, with all
  nine card components now using one consistent doc-comment format.

## Placeholder content policy

Fallback arrays (in `front-page.php` and each card component's own
defaults) describe content *categories* — "a restaurant card looks like
this" — not specific named businesses, churches, or events. The only
proper nouns used anywhere are unambiguous public geography (Lake Lanier,
Big Creek Greenway) and the area's public high school. Every other
placeholder uses generic wording ("A local coffee shop", "A neighborhood
church") and, where relevant, tells the admin what to do to replace it
("Publish a Restaurant to feature it here"). Do not reintroduce invented
specific business names, church names, or event listings with fabricated
schedules — if a section needs a richer-looking placeholder, make the
wording more generic, not more specific.

## Future roadmap

Roughly in priority order:

1. **Author real content.** The entire homepage and archive/search/single
   layer is built and waiting; the highest-leverage next step is publishing
   real Events, Restaurants, Parks, Neighborhoods, Schools, Churches,
   Businesses, Guides, and Articles in wp-admin.
2. **Tag content with `sf_area`** as it's published, so neighborhood pages
   can cross-link nearby restaurants, parks, and events without any new
   code.
3. **Wire up the remaining systems documented in
   `inc/community-platform.php`** as real content volume justifies them:
   filtering, interactive maps, event/business submission forms, member
   accounts and favorites, featured/sponsored listings. Normalized
   cross-post-type search itself is no longer just planned —
   `Southforsyth_Search_Service` (`inc/search/`) exists and works today;
   see platform-architecture.md.
4. **Consider single-{post_type}.php templates** only if a specific post
   type's detail page needs meaningfully different structure than the
   shared `single.php` + `post-meta.php` combination already provides —
   don't split them preemptively.
5. **Revisit `inc/architecture.php` / `inc/evergreen-content.php`** once
   real Guide/Article content starts getting published against their plan;
   at that point it may be worth trimming entries that have shipped rather
   than leaving the full original plan in place indefinitely.
6. **Object-cache-aware transients** for the query helpers in
   `inc/queries.php` once traffic/content volume make repeated `WP_Query`
   calls on the homepage worth caching (noted as a TODO in
   `inc/performance.php` already).
7. **Bring in outside data** (official sources, calendars, GIS/open data,
   local news, community submissions) once manual content authoring alone
   can't keep up — see
   [data-integration-roadmap.md](data-integration-roadmap.md) for the full
   plan. The infrastructure for this now exists (providers, an import
   pipeline, a queue/log, an admin area to run and monitor it — see
   [platform-architecture.md](platform-architecture.md)); no provider is
   configured with real credentials and no import has actually run yet.
