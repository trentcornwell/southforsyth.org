# Data integration roadmap

**Status: mostly planning, with real ingestion infrastructure built and one
source (Forsyth County Schools) actually working.** There are still no cron
jobs or submission forms in the theme today, and most sources below remain
unbuilt — but the import pipeline, several feed parsers (ICS, RSS), and now
a real Forsyth County Schools scraper (`Southforsyth_Forsyth_County_Provider`,
driven by `wp southforsyth import-schools`) all exist and run. This document
is still the design for how South Forsyth.org pulls in outside data (official
sources, calendars, open data, news, and community submissions) without
compromising accuracy, attribution, or the "real content, not filler"
standard set in
[content-platform-architecture.md](content-platform-architecture.md) — treat
each source below as built only where explicitly marked so.

It builds directly on the not-yet-built systems already sketched in
`inc/community-platform.php` (`event-submission`, `business-submission`,
`church-submission`, `search`, `filtering`, etc.) — this document is the
operational plan for how those get fed real data, and how outside data
earns its way onto the site at all.

## Source categories

Every external source falls into one of five categories. Each has a
different trust level, a different ingestion mechanism, and a different
review burden — the strategy below treats them differently on purpose
rather than funneling everything through one generic "importer."

| Category | Trust level | Typical cadence | Goes in as |
|---|---|---|---|
| Official sources | High | Weekly/monthly pull | Draft, light review |
| Calendar/ICS sources | Medium-high | Daily/weekly pull | Draft, standard review |
| GIS/open data sources | High (but stale-prone) | One-time or quarterly | Draft, standard review |
| RSS/news sources | Medium | Daily pull | Draft, standard review, link + excerpt only |
| Manual submissions | Variable | Real-time | Pending review, full review |

## Official sources

Government and institutional sources for Forsyth County and its schools.
These are the highest-trust sources but rarely have a clean API — most will
require a periodic manual or semi-manual pull rather than a live feed at
first.

- **Forsyth County Government** (departments, meeting notices, public
  safety announcements) — likely page-scrape or manual entry until/unless
  an open data or RSS endpoint is confirmed.
- **Forsyth County Schools (district) — Working, school info only.**
  `Southforsyth_Forsyth_County_Provider` (`inc/providers/`) is a real
  scraper against `www.forsyth.k12.ga.us`, run via
  `wp southforsyth import-schools`. Confirmed live (not assumed) before
  building it: the district's `robots.txt` declares `Crawl-delay: 5` for
  every user agent (the provider sleeps 5s before each request) and
  explicitly disallows the staff directory
  (`/schools/directions-contact-information/staff-directory-for-schools`),
  which is why principal names are never captured by this source. The
  district overview page and each school's own page are real,
  server-rendered HTML — no JS execution needed — parsed with PHP's
  built-in `DOMDocument`/`DOMXPath`. Captured per school: complete official
  display name (directory name plus Elementary/Middle/High suffix when the
  directory section provides that level), stable `/fs/pages/NNNNN` source ID,
  level,
  sector (`Public`), address/city/state/zip, phone, website, and (for the
  three "Academies of Creative Education" programs only) grades served,
  directly from the overview page's own stated range. **Not captured, by
  design, not oversight:** principal (source is disallowed), mission
  (lives on a separate per-school subpage — a deliberate follow-up pass,
  not skipped forever), mascot/colors/feeder pattern/attendance-boundary
  URL (no structured source found on the pages this provider reads — see
  "Content quality rules" below on why guessing at unstructured text isn't
  an acceptable substitute). District calendar items (→ `event`) are not
  yet built — this provider only handles `school`.
- **NCES (National Center for Education Statistics)** and **Georgia DOE /
  GOSA** — federal and state public school data (grade span, address,
  lat/lng, public/private status). Use for *enrichment and verification* of
  a school profile's factual fields, not for writing its description — NCES
  data is administrative, not narrative. `Southforsyth_Nces_Provider`
  follows the same configurable-endpoint, inert-until-configured pattern as
  the county provider above (no hardcoded or guessed API — NCES publishes
  its Common Core of Data as downloadable files, not a simple keyless REST
  API, and this project doesn't wire up to unvetted third-party wrappers).
  Georgia DOE/GOSA doesn't get its own provider class for the same reason
  the Chamber of Commerce's member list doesn't (see below): treated as a
  secondary manual cross-check source until it's actually needed, not an
  automated feed.
- **Forsyth County Parks & Recreation** — park facilities, programs,
  reservations info. Maps to `park` and `event`.
- **Forsyth County Public Library** — branch info and programming. Likely
  feeds `event` (library programs) and, later, a "Community Organizations"
  extension of `church`/civic content once that data model exists (see
  `inc/community-platform.php`).
- **City of Cumming / neighboring municipalities** — South Forsyth borders
  Cumming; relevant civic content there (events, notices) may be worth
  including with clear attribution that it's outside South Forsyth proper.
- **Greater Forsyth County Chamber of Commerce** — business networking
  events, ribbon cuttings, and community programming. Maps to `event`, and
  its member business list is a candidate (not a source of truth) for
  cross-checking new `business` submissions — never imported wholesale as
  business listings, since Chamber membership isn't the same thing as a
  verified, complete directory profile.
- **Georgia DOT (GDOT)** — road closures, construction, traffic incidents
  relevant to the "Traffic" homepage placeholder. Likely a GIS/open-data
  feed rather than a page scrape — see GIS section below.

**Caution:** confirm terms of use before pulling from any .gov site.
Government content is usually public domain in the U.S., but scraping
policies, rate limits, and robots.txt still apply and should be respected.

## Calendar/ICS sources

Many of the official sources above (schools, library, county parks,
churches, civic organizations) publish calendars as `.ics` feeds or
embeddable Google Calendars rather than as structured APIs. This is
realistically the primary feed for the `event` post type.

- **Ingestion approach:** a lightweight ICS parser (no such library exists
  in the theme yet — this would be a small, dependency-free parser, not a
  Composer package, to keep the "no plugins" / lightweight constraint) that
  reads `VEVENT` blocks and maps `SUMMARY` → post title, `DESCRIPTION` →
  post content/excerpt, `DTSTART`/`DTEND` → `sf_event_date`/`sf_event_time`,
  `LOCATION` → `sf_event_venue`.
- **Recurring events:** ICS `RRULE` recurrence is common (weekly farmers
  market, monthly meetup) and is the hardest part of this to get right.
  Initial approach: expand recurring rules into individual `event` posts
  for a bounded window (e.g. next 90 days) rather than trying to model
  recurrence natively in WordPress — simpler, and matches how the `event`
  CPT is already structured (one post per occurrence).
- **Sources to target first:** church service/event calendars (with
  permission), school district calendar, county parks program calendar,
  library event calendar, and the Chamber of Commerce events calendar.
- **Taxonomy mapping:** `sf_event_category` assigned based on source (e.g.
  a library-sourced event defaults to a "Community" category), refined by a
  human reviewer.

## GIS/open data sources

Structured, often geographic, open data — high trust, but typically a
one-time or infrequent pull rather than a live feed, and prone to going
stale silently (no "last updated" signal from most county GIS portals).

- **U.S. Census / American Community Survey (ACS)** — demographic context
  for `neighborhood` profiles (population, median income, median age).
  `Southforsyth_Census_Provider` (`inc/providers/`) is built and ready —
  it requires a free Census API key (Settings admin page) and is inert
  until one is configured. Normalizes into `sf_census_population`/
  `sf_census_median_income`/`sf_census_median_age`/`sf_census_source_year`
  (`inc/meta.php`) — numbers only, never prose; the surrounding descriptive
  text on a neighborhood page stays human-written.
- **Forsyth County GIS** (parcels, parks boundaries, zoning) — candidate
  source for `park` post details and for `neighborhood` boundary
  descriptions. Likely delivered as shapefiles or a GeoJSON export, not a
  live API. **Named but not built**: no confirmed public endpoint exists
  yet, so this stays documentation only — no inert provider class has been
  scaffolded for it, matching the same caution already applied to Georgia
  DOE/GOSA below.
- **Georgia DOT open data** — traffic and road condition data for the
  "Traffic" homepage placeholder. Would need a genuinely live feed (not a
  one-time pull) to be worth showing; until then, that section stays a
  static placeholder as documented in `docs/content-platform-architecture.md`.
  **Named but not built**, same reasoning as Forsyth County GIS above.
- **Other government datasets** (state/county open-data portals not listed
  above) — a real category this project expects to draw from eventually,
  but with no specific source identified yet. Treat any addition here the
  same way: only scaffold a provider class once a concrete, confirmed
  endpoint exists — see "How to add a new data source" in
  `docs/platform-architecture.md`.
- **Geo meta fields** (`sf_lat`/`sf_lng`) exist today, shared across every
  directory-type post type (`inc/meta.php`) — the prerequisite this section
  used to flag as missing is done, and they're already consumed by
  `Southforsyth_Openstreetmap_Provider`, `Southforsyth_Google_Places_Provider`,
  and `southforsyth_get_nearby_places()` (`inc/queries.php`).

**Caution:** open data licenses vary by source (some require attribution,
some are public domain, some are share-alike). Record the source and its
license terms alongside the data itself — see Legal/attribution below.

## RSS/news sources

Local news coverage relevant to South Forsyth, feeding the `article` post
type (which already uses core `category`/`post_tag` rather than a custom
taxonomy, matching standard WordPress blogging conventions).

- Candidate sources: local news outlets covering Forsyth County, county
  government press releases (if RSS is available), school district news
  feed.
- **Never republish full articles.** RSS/news content comes in as a short
  excerpt plus a clearly-labeled link back to the original source — never
  as the full post body. This is a legal requirement (see below), not just
  a style preference.
- **Editorializing is required, not optional.** A raw RSS excerpt dropped
  in as an `article` post reads as content-farming, not as "a real local
  guide." Every imported news item needs a human-written framing sentence
  or two before publishing — this is exactly why these land as drafts (see
  Human review workflow), not why they get skipped entirely.

## Manual submission sources

Community-submitted content: event organizers, business owners, churches,
schools, and residents submitting content directly. This is the source
category explicitly designed for in `inc/community-platform.php`'s
`event-submission`, `business-submission`, and `church-submission` entries
— this document assumes those forms exist and describes what happens to
their output.

- Every submission is tied to a submitter (name/email/organization at
  minimum) for accountability and follow-up — never anonymous. Submissions
  are not auto-published under any circumstances, regardless of submitter
  type — the content quality rules and human review workflow below apply
  identically to a resident's playground tip and a business owner's
  directory listing.
- **Spam/abuse risk is real** for this category in a way it isn't for the
  others. Rate-limiting per submitter, a honeypot field, and basic content
  filtering (no links to unrelated domains, no profanity) are minimum
  requirements before any public submission form goes live — not a later
  nice-to-have.

## Import queue strategy

A single, consistent staging pattern for every source category above,
regardless of how the data arrives:

1. **Nothing writes directly to `publish` status.** Every import — official
   source, calendar, GIS, RSS, or manual submission — creates a post in
   `draft` (system/scheduled sources) or WordPress's native `pending`
   status (manual submissions, since that status exists specifically for
   "needs editorial sign-off before going live").
2. **Every imported post carries source metadata**, stored as post meta
   (not in `inc/meta.php`'s content-facing fields — these are internal,
   underscore-prefixed so they don't show in the Custom Fields UI):
   - `_sf_import_source` — which source fed this (e.g. `forsyth-county-parks-ics`).
   - `_sf_import_source_id` — the source's own identifier for this item, if it has one (an ICS `UID`, an RSS `guid`, a GIS feature ID).
   - `_sf_import_hash` — a hash of the normalized content used for duplicate detection (see below).
   - `_sf_import_fetched_at` — timestamp of the import run.
3. **Imports run on a schedule per source**, using WP-Cron (already
   available, no new infrastructure), not a single "import everything"
   job — an ICS calendar might run daily, a GIS pull quarterly, an RSS feed
   hourly. Each source's cadence lives with its own import routine once
   built.
4. **Imports are idempotent.** Re-running a source's import should update
   an existing draft (if the source item changed) rather than creating a
   duplicate — this is what `_sf_import_source` + `_sf_import_source_id`
   is for.
5. **Failures are logged, not silent.** A source that stops responding or
   starts returning malformed data should be visible to whoever maintains
   the site, not fail quietly for months (see Future automation phases for
   when this gets a real admin-facing view).

## Human review workflow

No source category skips human review before anything appears live on the
site — this is the single non-negotiable rule underlying every section
above.

- **Reviewer checklist**, applied to every queued item regardless of
  source: accurate and current information, appropriate tone for the site
  (matches the "Coding/editorial philosophy" in `CLAUDE.md`), correctly
  categorized (right post type, right taxonomy terms, `sf_area` set if
  location-relevant), no duplicate of existing content, no obvious spam/
  abuse (manual submissions), image rights confirmed if a photo is
  attached.
- **Reviewers use WordPress's native editorial statuses** — `draft` →
  `pending` → `publish` — rather than a custom review system. This keeps
  the review workflow inside the standard wp-admin "Posts" screen anyone
  familiar with WordPress already knows.
- **A queued-imports view is a Phase 3 nice-to-have, not a blocker.**
  Reviewers can work directly from the Posts list (filtered by status and
  post type) using only what WordPress already provides until there's
  enough import volume to justify a dedicated dashboard.

## South Forsyth classification policy

`sf_south_forsyth_status` (`inc/meta.php`, shared directory group — see
"Post meta" in `content-platform-architecture.md`) exists because "South
Forsyth" is not an incorporated place with a fixed legal boundary — the
homepage's own `local-definition-block.php` says as much already
("There is no city hall, mayor, or municipal boundary for South Forsyth").
This document does not claim there is a single permanent, official
definition, and neither should any future code or copy that touches this
field. What counts as "in South Forsyth" is a judgment call, informed by
whatever mix of the following is actually available and relevant for a
given entity — no single one of these is authoritative on its own, and
none is required:

- School attendance zones (where applicable)
- Physical location and address
- Feeder-pattern or service-area relationships to other confirmed entities
- Commonly recognized local community names (Halcyon, Big Creek, Denmark,
  Vickery, Windermere, Polo Fields, the McFarland/Union Hill/Shiloh
  corridors — the same list `local-definition-block.php` already uses)
- Authoritative county or school-district sources, when they say something
  directly relevant
- Editorial judgment, applied openly rather than hidden behind a
  false-precision label

**The three statuses stay exactly as defined for the Schools import**
(`Southforsyth_Forsyth_County_Provider`) and apply the same way to any
future directory-type content that needs this same judgment call:

| Status | Meaning |
|---|---|
| `Confirmed South Forsyth` | A human or strong automatic signal has affirmatively placed this entity in the South Forsyth coverage area. |
| `Needs Review` | Default for anything not yet classified or not confidently classifiable. |
| `Outside Coverage` | Affirmatively placed outside the South Forsyth editorial coverage area. |

Automatic confirmation is limited to the central allowlist in
`Southforsyth_School_Import_Safety::coverage_allowlist()`: South Forsyth High
School, Denmark High School, and Lambert High School. Middle and elementary
schools stay `Needs Review` unless an editor records official boundary,
attendance-map, feeder/serving-area, address-with-boundary, or manual
editorial evidence. The classifier must not confirm solely by city name,
corridor keyword, ZIP, or fabricated feeder pattern. Clearly outside county
signals include North Forsyth, East Forsyth, West Forsyth, Forsyth Central,
Coal Mountain, Chestatee, Cumming, Matt, Sawnee, Kelly Mill, Otwell, Liberty,
Little Mill, Lakeside, Silver City, Poole's Mill, Mashburn, and Chattahoochee.

Every coverage decision should carry provenance in `sf_coverage_decision_*`
meta: decision source, note, date, and `manual`/`automatic` type.

**Classification is stored separately from factual metadata on purpose.**
`sf_south_forsyth_status` is never bundled into `sf_address`,
`sf_district`, or any other factual field, specifically so that changing a
school's classification is never mistaken for — or accidentally applied
as — a correction to a verified fact, and so a status can be revisited
later without touching the record's sourced data at all. This is also why
the community-suggestion system (see below) treats a classification change
and a factual correction as different kinds of suggestions with different
review weight, not the same "edit a field" action.

## Custom post type mapping

How each source category maps onto the nine post types and their
taxonomies (all defined in `inc/post-types.php` / `inc/taxonomies.php`
today):

| Source category | Post type(s) | Taxonomies | Meta fields |
|---|---|---|---|
| Official — schools (FCS, NCES, GA DOE) | `school` | `sf_school_type` (level + sector), `sf_school_district`, `sf_area` | `sf_address`, `sf_phone`, `sf_website`, `sf_hours`, `sf_grades_served`, `sf_principal_name`, `sf_boundary_url`, `sf_feeder_pattern`, `sf_notable_programs`, `sf_source_url`, `sf_last_verified` |
| Official — parks & rec | `park`, `event` | `sf_park_amenity`, `sf_event_category`, `sf_area` | `sf_address`, `sf_hours`; `sf_event_date/time/venue` |
| Official — library/civic | `event` | `sf_event_category`, `sf_area` | `sf_event_date/time/venue` |
| Calendar/ICS | `event` | `sf_event_category`, `sf_area` | `sf_event_date`, `sf_event_time`, `sf_event_venue` |
| GIS/open data | `park`, `neighborhood`, `school` | `sf_park_amenity`, `sf_lifestyle_tag`, `sf_area` | `sf_address`; future `sf_lat`/`sf_lng` |
| RSS/news | `article` | core `category`, `post_tag` | — |
| Manual — event submission | `event` | `sf_event_category`, `sf_area` | `sf_event_date/time/venue` |
| Manual — business submission | `business` | `sf_business_category`, `sf_area` | `sf_address`, `sf_phone`, `sf_website`, `sf_hours`, `sf_featured` |
| Manual — church submission | `church` | `sf_denomination`, `sf_area` | `sf_address`, `sf_phone`, `sf_website`, `sf_hours` |
| Manual — restaurant tip | `restaurant` | `sf_cuisine`, `sf_area` | `sf_address`, `sf_phone`, `sf_website`, `sf_hours`, `sf_featured` |

Nothing here requires new post types or taxonomies — the existing content
model (documented in `content-platform-architecture.md`) was deliberately
built broad enough to absorb all of this.

## Duplicate prevention

Duplicate content is the most likely failure mode once more than one
source can produce the same real-world thing (e.g. a farmers market that's
in both the county parks ICS feed and a manually-submitted event).

- **Automated sources (official, ICS, GIS, RSS):** dedupe primarily on
  `_sf_import_source` + `_sf_import_source_id`, since these sources
  provide stable external IDs. `_sf_import_hash` (a hash of normalized
  title + date + venue, or title + link for news) is the fallback for
  sources without a reliable ID, and also catches the case where the same
  event appears in two different feeds.
- **Manual submissions:** no external ID to key off, so dedupe is
  fuzzier and human-assisted: a pre-submission check ("does this business
  already exist?" via a simple search-as-you-type against existing
  `business` posts by name) reduces duplicate submissions at the source,
  and the reviewer checklist above catches what slips through.
- **Cross-source duplicates** (the farmers-market-in-two-feeds case) are
  a review-time judgment call, not something to fully automate — merge
  by keeping the more complete/higher-trust source's version and adding
  the other source's ID to `_sf_import_source_id` as a secondary reference
  rather than publishing both.

## Content quality rules

Minimum bar for anything to move from `draft`/`pending` to `publish`,
regardless of source:

- **Per-type minimum fields** before publishing: `event` needs a date and
  a venue at minimum; `business`/`restaurant`/`church`/`school` need an
  address; `article` needs more than a one-sentence excerpt (see RSS
  section — a bare excerpt is not publishable as-is).
- **No fabricated specifics.** This extends the placeholder-content policy
  already established for the homepage in `content-platform-architecture.md`
  to imported content: never fill a missing field with an invented value
  (a guessed phone number, a made-up "hours") to make a listing look more
  complete than the source actually provided. Leave it blank and let the
  template's existing empty-field handling deal with it.
- **Tone matches the site**, per `CLAUDE.md`'s existing philosophy —
  warm, community-minded, not government-form dry and not marketing-copy
  breathless. This is why RSS excerpts get a human framing sentence and
  why GIS/census data becomes descriptive prose, not a raw data dump.
- **Images require rights confirmation.** No image gets attached to an
  imported post unless its license/rights are known (public domain
  government photo, submitter-owned photo with permission granted, or a
  licensed stock source) — never a scraped image of unknown provenance.

## Legal/attribution cautions

- **Government/official data** is usually public domain in the U.S., but
  "usually" isn't "always" — confirm the specific county/state/federal
  source's terms before treating it as free to reuse, and attribute the
  source with a link regardless of license, as a matter of trust and
  transparency with readers.
- **GIS/open data licenses vary.** Some Forsyth County and Georgia state
  datasets carry attribution requirements or share-alike terms; record the
  license alongside `_sf_import_source` so it isn't lost when the data is
  transformed into a post.
- **News/RSS content is copyrighted.** Excerpt-and-link only, never full
  reproduction — this isn't just an editorial preference, it's the legal
  boundary that keeps this from being a copyright problem. Confirm each
  source's own republishing policy before importing at all; some outlets
  explicitly prohibit even excerpting via automated feeds.
- **Calendar/ICS content belongs to the event organizer**, not the
  calendar platform hosting it. Attribute the organizer, not just "County
  Parks Calendar," where the ICS data makes that distinction available.
- **Don't scrape sites whose terms of service prohibit it**, regardless of
  whether the data is otherwise public. Robots.txt and ToS review happens
  before a source is added, not after.
- **Never import personal data about private individuals** (names, phone
  numbers, addresses of non-organizational people) beyond what a public
  organization has already chosen to publish about itself. A church's
  posted service times are fine; a resident's home address scraped from a
  county parcel record for a "neighborhood" post is not.

## Future automation phases

This is a staged rollout, not a single project — each phase should be
justified by actual content volume and reviewer capacity, not built ahead
of need.

1. **Phase 0 (current state):** manual entry plus one staff-run school
   importer. Forsyth County Schools can be imported as review-ready drafts;
   parks, churches, restaurants, and most event sources remain planned.
2. **Phase 1 — semi-automated, staff-run.** A small number of WP-CLI
   commands (one per source, e.g. `wp southforsyth import:ics <url>`) that
   a site maintainer runs manually and reviews the resulting drafts
   immediately after. No cron yet — a human decides when each import runs.
3. **Phase 2 — scheduled imports.** The Phase 1 commands move behind
   WP-Cron on a per-source schedule, landing as drafts/pending exactly as
   described above, reviewed in the normal wp-admin Posts screen on a
   regular cadence rather than triggered manually.
4. **Phase 3 — review tooling.** Once import volume makes the plain Posts
   list unwieldy, add a dedicated admin screen listing queued imports by
   source, with the duplicate/quality checks above surfaced as reviewer
   hints rather than manual lookups, plus basic import-failure
   notifications (email/admin-notice) so a broken source doesn't fail
   silently.
5. **Phase 4 — public submission forms.** The `event-submission`/
   `business-submission`/`church-submission` systems from
   `inc/community-platform.php` go live, feeding the same `pending`-status
   queue and review workflow as every automated source, with spam/rate
   protections in place before launch (see Manual submission sources
   above).

Each phase depends on the content model and review discipline established
in Phase 0 continuing to hold — automation is meant to reduce reviewer
workload, not to bypass review.
