# Data integration roadmap

**Status: planning only.** Nothing described in this document is built yet —
there are no importers, cron jobs, feed parsers, or submission forms in the
theme today. This is the design for how South Forsyth.org will eventually
pull in outside data (official sources, calendars, open data, news, and
community submissions) without compromising accuracy, attribution, or the
"real content, not filler" standard set in
[content-platform-architecture.md](content-platform-architecture.md).

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
- **Forsyth County Schools (district)** — school info, calendars, news.
  Maps to the `school` post type and to `event` for district calendar
  items.
- **Forsyth County Parks & Recreation** — park facilities, programs,
  reservations info. Maps to `park` and `event`.
- **Forsyth County Public Library** — branch info and programming. Likely
  feeds `event` (library programs) and, later, a "Community Organizations"
  extension of `church`/civic content once that data model exists (see
  `inc/community-platform.php`).
- **City of Cumming / neighboring municipalities** — South Forsyth borders
  Cumming; relevant civic content there (events, notices) may be worth
  including with clear attribution that it's outside South Forsyth proper.
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
  library event calendar.
- **Taxonomy mapping:** `sf_event_category` assigned based on source (e.g.
  a library-sourced event defaults to a "Community" category), refined by a
  human reviewer.

## GIS/open data sources

Structured, often geographic, open data — high trust, but typically a
one-time or infrequent pull rather than a live feed, and prone to going
stale silently (no "last updated" signal from most county GIS portals).

- **Forsyth County GIS** (parcels, parks boundaries, zoning) — candidate
  source for `park` post details and for `neighborhood` boundary
  descriptions. Likely delivered as shapefiles or a GeoJSON export, not a
  live API.
- **Georgia DOT open data** — traffic and road condition data for the
  "Traffic" homepage placeholder. Would need a genuinely live feed (not a
  one-time pull) to be worth showing; until then, that section stays a
  static placeholder as documented in `docs/content-platform-architecture.md`.
- **U.S. Census / American Community Survey (ACS)** — demographic context
  for `neighborhood` profiles (school-age population, commute times,
  housing age). Useful as descriptive background text, not as the primary
  content of a neighborhood page.
- **Geo meta fields don't exist yet.** `inc/meta.php` has no latitude/
  longitude fields today. Adding `sf_lat`/`sf_lng` (shared across `park`,
  `restaurant`, `business`, `church`, `school`, matching the existing
  `sf_address`/`sf_phone`/`sf_hours` pattern) is a prerequisite for any GIS
  ingestion and for the "Interactive Maps" system already sketched in
  `inc/community-platform.php`.

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

## Custom post type mapping

How each source category maps onto the nine post types and their
taxonomies (all defined in `inc/post-types.php` / `inc/taxonomies.php`
today):

| Source category | Post type(s) | Taxonomies | Meta fields |
|---|---|---|---|
| Official — schools | `school` | `sf_school_type`, `sf_area` | `sf_address`, `sf_phone`, `sf_website`, `sf_hours` |
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

1. **Phase 0 (current state):** manual entry only, via wp-admin. No
   importers exist. This document is the plan, not a description of
   anything running today.
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
