# Content platform architecture

South Forsyth.org is a community content platform, not a business or brochure
site. The theme models its content as real WordPress custom post types and
taxonomies instead of static placeholder markup, so the archive/single
templates automatically show live content the moment it is published — no
template changes required later. The homepage is a deliberate exception right
now; see below.

## Homepage: preview vs. live

The homepage (`front-page.php`) is currently a **static "preview / launching
soon" page**, not a live query against the content model described in this
document. That's an intentional, temporary state, not an architecture gap:

- It doesn't call `southforsyth_get_latest_items()` or
  `southforsyth_render_card_section()` with real post-type data anywhere.
  Every section is a plain PHP array of "Coming Soon" copy.
- The reason: with zero published posts in most of the nine post types, a
  live-query homepage would either show nothing or show the same generic
  fallback placeholders everywhere — which reads as broken or unfinished
  rather than as a real site. A static, polished "here's what's coming" page
  is more honest and more impressive for a pre-launch site than a live page
  with empty sections.
- Nothing about the content model changed to support this — `inc/post-types.php`,
  `inc/taxonomies.php`, `inc/meta.php`, and `inc/queries.php` are untouched.
  `archive.php`, `search.php`, and `single.php` still work today against any
  post type the moment it has published content; only the homepage is
  deliberately static.
- Every static section in `front-page.php` that should eventually become a
  live query has a `TODO` comment immediately above it naming the exact
  `inc/queries.php` function and post type to swap in — e.g. the "What We're
  Building" section's comment says to replace it with
  `southforsyth_get_latest_items('event', 3, $fallback)` once Event posts
  exist. Convert sections one at a time as each post type gets real content;
  there's no need to flip the whole homepage over at once.

## Custom post types

Registered in `inc/post-types.php`. Every type supports title, editor,
excerpt, featured image, and WordPress's native Custom Fields metabox
(`custom-fields`), and is REST-exposed (`show_in_rest`) for the block editor
and any future headless use.

| Post type       | Archive URL             | Purpose |
|-----------------|--------------------------|---------|
| `event`         | `/events/`               | Community events, markets, recurring programming |
| `restaurant`    | `/restaurants/`          | Restaurants, coffee shops, dining |
| `park`          | `/parks/`                | Parks, trails, playgrounds |
| `neighborhood`  | `/neighborhoods/`        | Neighborhood profiles (lifestyle, schools, amenities) |
| `school`        | `/schools/`              | Local schools and education resources |
| `church`        | `/churches/`             | Faith communities and volunteer programs |
| `business`      | `/business-directory/`   | Local businesses and service providers |
| `guide`         | `/guides/`               | Evergreen guides (best parks, moving guide, seasonal roundups) |
| `article`       | `/articles/`             | Editorial stories and local news (uses core `category`/`post_tag`) |

`business` is one content type among nine — it is deliberately not
over-weighted relative to the others, since this is a community platform
first, not a business directory.

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
  `sf_park_amenity`, `sf_lifestyle_tag` (neighborhoods), `sf_guide_topic`.
- `article` uses WordPress's built-in `category` and `post_tag` instead of a
  custom taxonomy, since editorial/news content maps directly onto the
  standard WordPress blogging model — no reason to reinvent it.

## Post meta

Registered in `inc/meta.php`, kept deliberately small:

- **Directory fields** (`sf_address`, `sf_phone`, `sf_website`, `sf_hours`) —
  shared across `restaurant`, `park`, `school`, `church`, `business` rather
  than duplicating near-identical fields per post type.
- **Event fields** (`sf_event_date`, `sf_event_time`, `sf_event_venue`) —
  specific to `event`.
- **`sf_featured`** — a single boolean reused across `event`, `restaurant`,
  `park`, `neighborhood`, `business`. Checking it is what makes a post
  eligible for the homepage's "Popular Places" section, which deliberately
  mixes post types instead of requiring a dedicated CPT or taxonomy just for
  "things that are popular."

All fields are edited through WordPress's native Custom Fields metabox — no
plugin (e.g. ACF) required, matching the "no plugins" constraint.

## Query helpers and the homepage

`inc/queries.php` provides:

- `southforsyth_get_latest_items($post_type, $count, $fallback, $eyebrow)` —
  fetches the latest published posts of a type, normalized into the card
  shape (`eyebrow`, `title`, `description`, `link`) used by every card
  component. Returns `$fallback` when the post type has no published content
  yet.
- `southforsyth_get_featured_places($count, $fallback)` — same idea, but
  queries across every `sf_featured`-eligible post type at once.

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
   search/filtering, interactive maps, event/business submission forms,
   member accounts and favorites, featured/sponsored listings.
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
   plan. Planning only today; no importers exist yet.
