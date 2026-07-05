# Editorial roadmap

**Status: planning only.** No content described below has been published
yet — this document is the priority order for authoring real content once
someone sits down in wp-admin, not a description of anything live today.
It complements, rather than replaces, the existing planning docs:

- [information-architecture.md](information-architecture.md) — the full
  site map and per-section SEO/AI strategy.
- [evergreen-content-strategy.md](evergreen-content-strategy.md) — the
  24-guide evergreen publishing list referenced below.
- [content-platform-architecture.md](content-platform-architecture.md) —
  the post types, taxonomies, and placeholder content policy every page
  below must follow (no fabricated business names, events, or schedules).

## First 25 pages to publish

In order. Each hub page (archive.php or page-templates/hub.php) already
exists and works today with zero posts behind it — publishing content means
adding real posts/pages in wp-admin, not building new templates.

1. Home (already live as the preview homepage)
2. Neighborhoods archive — at least one real `neighborhood` post (Halcyon)
3. Neighborhoods archive — Vickery
4. Neighborhoods archive — Windermere
5. New Resident Guide (the hub page itself, with real body copy)
6. Schools archive — the area's public high school
7. Schools archive — one feeder middle school
8. Schools archive — one feeder elementary school
9. Parks & Trails archive — Big Creek Greenway
10. Parks & Trails archive — one neighborhood park
11. Restaurants & Coffee archive — one coffee shop
12. Restaurants & Coffee archive — one family-dining restaurant
13. Churches archive — three to five congregations across different
    denominations (breadth over depth for the first pass)
14. Things To Do (hub page body copy, once #9–12 exist to link to)
15. Guide: Best Parks (see evergreen list, #1)
16. Guide: Every Playground (#2)
17. Guide: Walking Trails (#3)
18. Guide: Moving Guide (#21) — ties directly into New Resident Guide
19. Guide: School Guide (#22) — ties directly into Schools
20. Guide: Church Guide (#20) — ties directly into Churches
21. Weekend Guide (hub page body copy, once Events has a few real entries)
22. Events archive — first 3–5 real, dated events (markets, school/library
    calendar items — see data-integration-roadmap.md for sourcing)
23. Business Directory archive — five to ten businesses across categories
    (not weighted toward any one category — see the "business is one
    content type among nine" note in content-platform-architecture.md)
24. Guide: Restaurants (#4) — a roundup once #11–12 and a few more
    restaurants exist
25. Guide: Family Activities (#9)

### Why this order

- **Neighborhoods first.** Almost every other section (Schools, New
  Resident Guide, Restaurants) links back to a neighborhood, so a handful
  of real neighborhood profiles make every other page's internal linking
  meaningful instead of pointing at an empty archive.
- **New Resident Guide early, not last.** It's the highest-intent page for
  someone actively deciding whether to move here — worth having real
  content behind it well before Events or the Business Directory.
- **Breadth before depth on Churches and Business Directory.** A handful of
  entries across categories/denominations reads as "a real directory that's
  still growing"; ten entries in one category reads as incomplete.
- **Evergreen guides slot in once their subject has content.** A "Best
  Parks" guide is more credible (and easier to write honestly) once a few
  real Park posts exist to link to.

## Highest SEO value pages

Ranked by search-intent strength, per the existing evergreen strategy doc:

1. **New Resident Guide** — high commercial/relocation intent, evergreen,
   low competition for "moving to South Forsyth" style queries.
2. **Neighborhoods archive + individual profiles** — "what's it like to
   live in Halcyon/Vickery/Windermere" has real, repeat search volume and
   almost no local competition.
3. **Best Parks / Every Playground / Walking Trails** — the top three
   evergreen guides per evergreen-content-strategy.md; consistently
   searched, easy to make genuinely useful without original photography.
4. **Schools archive** — high trust-and-intent value for families
   researching a move, feeds directly into New Resident Guide.
5. **Restaurants & Coffee archive + cuisine-based guides** (Breakfast,
   Pizza, BBQ) — frequent, repeat local searches; each cuisine guide is a
   small, fast evergreen win once enough restaurants exist.
6. **Weekend Guide** — lower search volume than the above but strong for
   repeat visits and newsletter conversion once it's a real, refreshed
   itinerary.

## Local guide priorities

Follow the 24-guide list already defined in
[evergreen-content-strategy.md](evergreen-content-strategy.md) in the order
given there — this document doesn't duplicate that list, only calls out
which guides are prerequisites for #14–25 above (Best Parks, Every
Playground, Walking Trails, Moving Guide, School Guide, Church Guide,
Restaurants, Family Activities).

## Directory priorities

For both Business Directory and Churches, breadth-before-depth applies:

1. Publish a small, category-diverse first batch (see #13 and #23 above)
   rather than exhaustively filling one category.
2. Tag every entry with `sf_area` as it's published (per the "Future
   roadmap" in content-platform-architecture.md) so neighborhood profiles
   can immediately cross-link nearby businesses and churches without any
   new code.
3. Don't backfill missing fields (phone, hours, address) with guessed
   values — leave them blank per the placeholder content policy; an
   incomplete-but-honest listing is fine, a fabricated one is not.
4. Featured/sponsored placement (`sf_featured` and the "featured-listings"/
   "sponsored-listings" systems in `inc/community-platform.php`) stays off
   until there's enough directory volume that "featured" means something —
   don't feature the first three listings just because they're the only
   three.

## Event calendar strategy

1. **Phase 0 (now): manual entry only**, matching the current state
   described in data-integration-roadmap.md. The first 3–5 events (#22
   above) are added directly in wp-admin, sourced from public calendars
   (school district, county parks, library) with the organizer credited.
2. **Publish recurring events as individual occurrences**, not one post
   with a recurrence rule — matches how the `event` post type is already
   structured (see data-integration-roadmap.md's "Recurring events" note)
   and keeps each event's date/time/venue fields accurate.
3. **Seasonal clustering.** Once there are enough events to make it
   meaningful, group upcoming seasonal events (Fourth of July, Halloween,
   Christmas events — see the evergreen guide list) into their own guide
   pages that link back to the individual Event posts, rather than trying
   to make the Events archive itself seasonal-aware.
4. **Don't wait for automation to start publishing.** The ICS/calendar
   import pipeline in data-integration-roadmap.md is Phase 1–2 work with no
   timeline dependency on this roadmap — manual entry should start now,
   independent of when (or whether) automated ingestion is built.

## Newsletter strategy

The newsletter signup (`template-parts/components/newsletter.php`) is
currently visual-only — no provider is connected and the form fields are
disabled by design (see the TODO comment in that file). Sequencing:

1. **Don't connect a provider before there's a reason to send an issue.**
   Turning on signups before the first 10–15 pages above exist means
   collecting emails with nothing to send them yet.
2. **First real issue ships alongside items #14 and #22** (Things To Do
   hub content and the first real Events) — a newsletter's first issue
   should point at genuinely new content, not "we launched, more soon."
3. **Segment lightly, not heavily, at first.** A single weekly digest
   (events + new guides + new restaurant/business listings) is enough
   until subscriber volume or explicit reader feedback justifies splitting
   into segments — matches the "don't build for hypothetical future
   requirements" principle in CLAUDE.md.
4. **Newsletter CTA placement stays as-is**: on the homepage, and now on
   every hub page (see archive.php and page-templates/hub.php) — that
   placement was added as part of this roadmap's rollout and shouldn't be
   duplicated further (e.g. inside every single Guide/Article body) without
   a specific reason.
