# Content platform architecture

South Forsyth.org is a community content platform, not a business or brochure
site. The theme now models its content as real WordPress custom post types
and taxonomies instead of static placeholder markup, so the homepage and
archive/single templates automatically start showing live content the moment
it is published — no template changes required later.

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
  and cross-linked from a Neighborhood profile page — e.g. "restaurants in
  Vickery" or "events near River Club." This is what makes "nearby
  suggestions" and neighborhood-based browsing possible later without a
  redesign.
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
