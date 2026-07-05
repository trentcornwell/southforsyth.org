# South Forsyth.org

SouthForsyth.org is a long-term community content platform for South Forsyth, Georgia — not a business website. The goal is to become the definitive local resource for residents, visitors, and families, covering events, restaurants, parks, neighborhoods, schools, churches, the business directory, evergreen guides, and local news as real, queryable WordPress content.

## Purpose
- Provide a polished, mobile-first hub for local community information
- Support future growth into news, events, guides, directories, and business listings
- Prioritize performance, accessibility, SEO, and AI-friendly structure

## Local development
1. Start a local WordPress environment with the project root mounted as the web root.
2. Place this repository in the WordPress themes directory at: wordpress/wp-content/themes/southforsyth
3. Activate the South Forsyth theme from the WordPress admin.
4. Create menus and widgets in Appearance > Menus and Appearance > Widgets to populate the header, footer, and sidebars.

## Theme structure
- inc/setup.php — core theme supports and setup
- inc/enqueue.php — CSS and JS asset loading
- inc/menus.php — menu registration
- inc/widgets.php — widget area registration
- inc/post-types.php — the 9 custom post types (events, restaurants, parks, neighborhoods, schools, churches, businesses, guides, articles)
- inc/taxonomies.php — taxonomies attached to those post types, including the cross-cutting `sf_area` taxonomy
- inc/meta.php — post meta fields (directory info, event date/time/venue, the `sf_featured` flag)
- inc/queries.php — query helpers that fetch live content per post type with realistic placeholder fallback
- inc/schema.php — SEO and schema helpers
- inc/helpers.php — reusable rendering helpers, including the card-section renderer used by the homepage
- inc/hub-content.php — hub page content (intro copy, FAQ, sample cards, related links) shared by every post type archive and the three standalone hub pages, keyed by `southforsyth_get_hub_content()`; also provides `southforsyth_get_hub_url()`, the single place every nav/link/card resolves a section's URL from
- inc/page-provisioning.php — auto-creates the Things To Do, New Resident Guide, and Weekend Guide pages (assigned to page-templates/hub.php) if they don't already exist, so those URLs work without manual wp-admin setup
- inc/performance.php — lean asset delivery (lazy-loaded images, no emoji script, JPEG quality)
- inc/template-functions.php — small presentation helpers (SVG icons, excerpts)
- inc/architecture.php / inc/evergreen-content.php / inc/community-platform.php — editorial strategy and content-planning data. Not required by `functions.php` (nothing at runtime reads them) — require the specific file directly if you need to read it programmatically; see docs below
- page-templates/hub.php — reusable "Template Name: South Forsyth Hub Page" template for the three hub sections with no custom post type of their own (Things To Do, New Resident Guide, Weekend Guide)
- template-parts/header/site-header.php — header partial; primary nav falls back to `southforsyth_primary_nav_fallback()` (inc/menus.php) until an admin builds a real menu in Appearance > Menus
- template-parts/footer/site-footer.php — footer partial with a fallback quick-links list and copyright bar when no footer widgets are active
- template-parts/components/hero.php — homepage hero
- template-parts/components/search.php — search form component
- template-parts/components/cta.php — call-to-action component
- template-parts/components/newsletter.php — newsletter signup block
- template-parts/components/section-header.php — reusable section intro
- template-parts/components/breadcrumbs.php — breadcrumbs for interior pages
- template-parts/components/post-meta.php — type-specific meta list (event date/venue, directory address/phone/hours) on single templates
- template-parts/components/directory-card.php — directory listing card (businesses)
- template-parts/components/restaurant-card.php — restaurant listing card
- template-parts/components/church-card.php — church/community card
- template-parts/components/school-card.php — school card
- template-parts/components/park-card.php — park card
- template-parts/components/neighborhood-card.php — neighborhood card
- template-parts/components/event-card.php — event card
- template-parts/components/article-card.php — article/story card
- template-parts/components/guide-card.php — guide card
- template-parts/components/weather-placeholder.php / traffic-placeholder.php — local-conditions placeholders
- template-parts/components/community-spotlight.php — resident/organization spotlight
- template-parts/components/coming-soon-card.php — icon + title + description + "Coming Soon" badge, with an optional link to a real hub page; used by the homepage's "What We're Building" and "Preview Content" sections and by every hub page's empty-state sample cards
- template-parts/components/sidebar-callout.php — editorial sidebar component
- template-parts/components/feature-banner.php — feature banner component
- template-parts/components/quote-block.php — pull quote or testimonial block
- template-parts/components/statistics.php — stats/metrics section
- template-parts/components/local-definition-block.php — "what is South Forsyth" explainer: the area grid (Halcyon, Big Creek, Denmark, Vickery, Windermere, Polo Fields, McFarland/Union Hill/Shiloh, etc.) plus the "not an official city" note, used on the homepage
- template-parts/components/faq-block.php — accessible `<details>`/`<summary>` FAQ list, used by every hub page (archive.php and page-templates/hub.php) via `southforsyth_render_hub_faq()`
- assets/css/main.css — design-system stylesheet
- assets/js/main.js — small interactive enhancements

See [wordpress/wp-content/themes/southforsyth/docs/content-platform-architecture.md](wordpress/wp-content/themes/southforsyth/docs/content-platform-architecture.md) for the full rationale behind the post types, taxonomies, meta fields, and the "one archive.php, not eighteen" template approach.

## Design system overview
The theme now includes a custom design system built without Bootstrap, Tailwind, or any other CSS framework. It is designed to feel like a premium regional publication with strong hierarchy, generous spacing, polished cards, accessible forms, and mobile-first responsive behavior.

## Information architecture
The theme now includes a planning layer for a large-scale local publishing site. The information architecture is documented in [wordpress/wp-content/themes/southforsyth/docs/information-architecture.md](wordpress/wp-content/themes/southforsyth/docs/information-architecture.md) and backed by reusable architecture helpers in [wordpress/wp-content/themes/southforsyth/inc/architecture.php](wordpress/wp-content/themes/southforsyth/inc/architecture.php).

### Planned section coverage
- Home
- Things To Do
- Events
- Weekend Guide
- Restaurants
- Coffee Shops
- Parks
- Trails
- Playgrounds
- Schools
- Churches
- Neighborhoods
- New Resident Guide
- Business Directory
- Healthcare
- Senior Resources
- Family Activities
- Youth Sports
- Shopping
- History
- Volunteer
- Community Organizations
- Public Safety
- Government
- Local News
- Weather
- Traffic
- Seasonal Guides
- Holiday Guides

The structure is centered on topical authority, internal linking, and long-term scalability for thousands of pages.

## Evergreen content strategy
The theme now includes a long-term evergreen content strategy aimed at high-intent local searches. The planning document is available at [wordpress/wp-content/themes/southforsyth/docs/evergreen-content-strategy.md](wordpress/wp-content/themes/southforsyth/docs/evergreen-content-strategy.md), and the content planning helper lives in [wordpress/wp-content/themes/southforsyth/inc/evergreen-content.php](wordpress/wp-content/themes/southforsyth/inc/evergreen-content.php).

## Data integration roadmap
Planning for how South Forsyth.org will eventually pull in outside data — official government/school sources (including the Chamber of Commerce events calendar), calendar/ICS feeds, GIS/open data, local news RSS, and community submissions — without compromising accuracy or attribution. Documentation only today; no importers exist yet. See [wordpress/wp-content/themes/southforsyth/docs/data-integration-roadmap.md](wordpress/wp-content/themes/southforsyth/docs/data-integration-roadmap.md).

## Editorial roadmap
The first 25 pages to publish, in priority order, plus the highest-SEO-value pages, directory/event/newsletter sequencing, and how this roadmap relates to the evergreen guide list. See [wordpress/wp-content/themes/southforsyth/docs/editorial-roadmap.md](wordpress/wp-content/themes/southforsyth/docs/editorial-roadmap.md).

### Priority guides
1. Best Parks
2. Every Playground
3. Walking Trails
4. Restaurants
5. Coffee Shops
6. Breakfast
7. Pizza
8. BBQ
9. Family Activities
10. Rainy Day Activities
11. Date Night
12. Summer Camps
13. Christmas Events
14. Pumpkin Patches
15. Farmers Markets
16. Fourth of July
17. Halloween
18. Christmas Lights
19. Neighborhood Guides
20. Church Guide
21. Moving Guide
22. School Guide
23. Business Guide
24. Volunteer Guide

### Design tokens
The stylesheet defines tokens for:
- primary and secondary brand colors
- accent, success, warning, and error colors
- neutral grays
- typography scale, line heights, and letter spacing
- spacing scale using 4, 8, 12, 16, 24, 32, 48, 64, and 96
- container widths
- buttons, forms, cards, radius, shadows, and transitions

### Utility classes
Reusable utilities include:
- .container and .container-wide
- .grid, .grid-2, .grid-3, .grid-4
- .flex, .stack, .cluster, .center, .flow
- .card, .card-feature, .card-directory, .card-event
- .btn, .btn-primary, .btn-secondary, .btn-outline
- .badge, .tag
- .section, .section-title, .section-subtitle
- .visually-hidden

### Responsive system
Breakpoints are defined for:
- 480px
- 768px
- 1024px
- 1280px
- 1440px

### Accessibility
The theme includes:
- a skip navigation link
- visible focus states
- keyboard-friendly mobile navigation
- ARIA labels and accessible form controls
- a clear heading and content hierarchy

## Component usage notes
Each component is built as a reusable template partial and should be used as a foundation for future WordPress-driven content.

- Hero: use for landing pages and top-of-page storytelling
- Search: use in hero areas or directory pages
- CTA: use for announcements, campaigns, or newsletter signups
- Newsletter signup: use as a lightweight conversion block
- Section header: use for consistent section intros
- Breadcrumbs: use on interior pages
- Directory cards: use for businesses, directories, and services
- Restaurant cards: use for dining content
- Church cards: use for community and church directories
- School cards: use for education-focused content
- Park cards: use for parks, trails, and outdoor recreation
- Neighborhood cards: use for neighborhood profiles
- Event cards: use for upcoming events and programming
- Article cards: use for editorial pieces and stories
- Guide cards: use for how-to and local explainer content
- Post meta: use on single templates to show event date/venue or directory address/phone/hours
- Coming soon card: use for feature/category previews with no published content behind them yet — no link, so it never dead-ends on an empty archive
- Weather / traffic placeholders: use in a "local conditions" section until a live data feed is connected
- Community spotlight: use to highlight a resident, volunteer, or organization
- Sidebar callout: use for related stories or quick links
- Feature banner: use for premium editorial highlights
- Quote block: use for testimonials or editorial pull quotes
- Statistics section: use for metrics, community milestones, and numbers
- Local definition block: use to explain what "South Forsyth" means (community identity, not a city) and list its constituent areas
- FAQ block: use for a hub page's frequently-asked-questions section

## Homepage status: preview, now with real hub pages behind it
The homepage (`front-page.php`) is still intentionally a **static preview** rather than a live, post-type-driven portal — it doesn't query any custom post type directly, and every "Coming Soon" section is still static copy, on purpose, so the site stays honest about how much content actually exists today. What changed: every one of those "Coming Soon" cards, and the primary navigation, now link to a real, working hub page instead of nowhere.

Every section — Things To Do, Events, Restaurants & Coffee, Parks & Trails, Schools, Churches, Neighborhoods, Business Directory, New Resident Guide, and Weekend Guide — has a real URL today. The seven with a custom post type (Events, Restaurants, Parks, Schools, Churches, Neighborhoods, Business Directory) use the enhanced `archive.php`, which shows live posts the moment any exist and falls back to intro copy, a clearly-labeled "Coming soon" notice, and sample category cards when a post type has zero posts. The three without a post type of their own (Things To Do, New Resident Guide, Weekend Guide) are real WordPress Pages, auto-created by `inc/page-provisioning.php` on `page-templates/hub.php`. All ten pull their copy from `inc/hub-content.php` — see that file and `docs/content-platform-architecture.md`'s "Hub pages" section for the full explanation.

Each place in `front-page.php` where a live query should eventually replace a static array still has a `TODO` comment pointing at the exact `inc/queries.php` function to use — see "Homepage: preview vs. live" in the architecture doc for the full explanation and how to switch it over once real content exists.

Placeholder wording throughout (hero, "What We're Building," "Preview Content," and every hub page's sample cards) is deliberately generic where it describes future content, and factual where it describes the area itself — see "Placeholder content policy" in the architecture doc before adding more.

## DreamHost deployment workflow

### 1. Configure your environment
Copy the example environment file and fill in your DreamHost details:

```bash
cp deploy.example.env .env
```

Then edit .env and set:
- DREAMHOST_USER
- DREAMHOST_SERVER
- DREAMHOST_REMOTE_PATH
- LOCAL_THEME_PATH

The file .env is ignored by Git so credentials remain local.

### 2. Pull the live theme
Use this when you want to sync the currently live DreamHost theme down to your local workspace:

```bash
./pull-live.sh
```

### 3. Deploy the local theme
After editing locally and committing to GitHub, deploy the theme to DreamHost with:

```bash
./deploy.sh
```

The deployment script uses rsync over SSH and excludes common local-only files such as .DS_Store, node_modules, logs, cache files, and environment files.

### 4. Connect VS Code to DreamHost
Recommended workflow:
- Use VS Code with the project folder open.
- Keep local development in this repository.
- Use GitHub as the source of truth.
- Use SFTP/SSH remote access or a DreamHost-compatible remote connection if you want to browse the server directly from VS Code.
- Keep the deployment process scripted so it remains repeatable and consistent.

### 5. Recommended development loop
```bash
git status
./pull-live.sh
# edit locally
git add .
git commit
# push to GitHub
git push
./deploy.sh
```

This keeps local development first, uses GitHub as the source of truth, and only deploys after a commit is ready.

## Deployment notes
- Keep the theme lightweight and plugin-free where possible.
- Use caching, image optimization, and a CDN when moving to production.
- Review SEO metadata and schema output regularly as content expands.

## Current status
- The homepage is a static "preview / launching soon" page (see above) — an honest, polished front door while real content is authored, now linking every section through to a real hub page instead of nowhere.
- Nine custom post types and their taxonomies are registered and REST-enabled (see the architecture doc linked above). Seven of them (Events, Restaurants, Parks, Schools, Churches, Neighborhoods, Business Directory) already have a real, polished hub page live at their archive URL via the enhanced `archive.php` — intro copy, FAQ, and a newsletter CTA today; live post grids the moment content is published.
- Things To Do, New Resident Guide, and Weekend Guide are real WordPress Pages (auto-created by `inc/page-provisioning.php`) on the reusable `page-templates/hub.php` template, giving all ten IA sections a working URL.
- `archive.php`, `search.php`, and `single.php` are post-type aware, rendering the right card component and meta fields for whatever type is being displayed, and work today for any post type that gets published.
- Navigation now has a real fallback menu (`southforsyth_primary_nav_fallback()` in `inc/menus.php`) pointing at all nine sections, so the header works before an admin ever builds a menu in Appearance > Menus — building one there still takes over automatically.
- Widgets, footer (now with a fallback quick-links list and copyright bar), and reusable components are wired up.
- SEO-ready metadata and schema output are included.

## Next steps
See "Future roadmap" in [docs/content-platform-architecture.md](wordpress/wp-content/themes/southforsyth/docs/content-platform-architecture.md) for the full, prioritized list, and [docs/editorial-roadmap.md](wordpress/wp-content/themes/southforsyth/docs/editorial-roadmap.md) for the specific first 25 pages to publish. In short: author real content first (the archive/single templates and every hub page are ready and waiting), tag it with `sf_area` as it's published, then build out search/filtering, maps, and submission workflows as content volume justifies them.
