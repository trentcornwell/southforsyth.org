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

## Overnight development workflow
Long-running AI coding sessions should use the staging-only overnight workflow documented in [docs/overnight-workflow.md](docs/overnight-workflow.md). The workflow uses numbered task specs in `tasks/overnight/`, external logs/backups under `/private/tmp/southforsyth-overnight/` by default, a lock to prevent concurrent runs, verification after each task, and checkpoint commits after successful verified tasks. It never deploys to production, publishes content, resets WordPress, deletes imported content, or invents factual data.

## Theme structure
- inc/setup.php — core theme supports and setup
- inc/enqueue.php — CSS and JS asset loading
- inc/menus.php — menu registration
- inc/widgets.php — widget area registration
- inc/post-types.php — 12 custom post types: the original 9 (events, restaurants, parks, neighborhoods, schools, churches, businesses, guides, articles) plus trails, topics, and community resources, added for the data-platform work — see docs/platform-architecture.md
- inc/taxonomies.php — taxonomies attached to those post types, including the cross-cutting `sf_area` taxonomy and the newer geographic hierarchy (`sf_region` > `sf_city` > `sf_area` > `sf_community`) plus `sf_audience`/`sf_interest`/`sf_school_district`/`sf_topic`
- inc/meta.php — post meta fields (directory info incl. `sf_lat`/`sf_lng`, event date/time/venue, article source-attribution fields, the `sf_featured` flag)
- inc/queries.php — query helpers that fetch live content per post type with realistic placeholder fallback
- inc/schema.php — SEO and schema helpers
- inc/helpers.php — reusable rendering helpers, including the card-section renderer used by the homepage
- inc/providers/ — external data provider classes (Google Places, OpenStreetMap, Forsyth County, Weather, Traffic, RSS, ICS Events) behind a common interface — see docs/platform-architecture.md
- inc/cache/ — `Southforsyth_Cache_Manager`, a transient-based read-through cache layer every provider uses
- inc/import/ — the import pipeline (validate, dedupe, slug, image download, queue, log) that turns a normalized provider record into a draft/pending post; the queue and log are two small custom DB tables, not post rows
- inc/search/ — `Southforsyth_Search_Service`, normalized cross-post-type search results for future programmatic use (search.php itself is unchanged)
- inc/admin/ — the "Community Platform" wp-admin menu (Providers, Imports, Queues, Logs, Content Status, Statistics, Settings), loaded only when `is_admin()`
- inc/automation.php — wireable refresh/expiry hooks (weather, traffic, events, stale-content expiry) — no cron job is scheduled anywhere; see docs/platform-architecture.md
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
- template-parts/components/trail-card.php — trail card
- template-parts/components/topic-card.php — topic (pillar page) card
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
- assets/icons/logo-mark.svg — bundled circular badge fallback logo, used when no custom logo is uploaded in wp-admin
- assets/images/logo/ — drop location for the final logo image file (none added yet); see its README.md and "Brand identity" above

See [wordpress/wp-content/themes/southforsyth/docs/content-platform-architecture.md](wordpress/wp-content/themes/southforsyth/docs/content-platform-architecture.md) for the full rationale behind the post types, taxonomies, meta fields, and the "one archive.php, not eighteen" template approach.

## Design system overview
The theme now includes a custom design system built without Bootstrap, Tailwind, or any other CSS framework. It is designed to feel like a premium regional publication with strong hierarchy, generous spacing, polished cards, accessible forms, and mobile-first responsive behavior.

## Brand identity
The visual identity is built around the South Forsyth badge/logo direction: a circular community badge with a navy border, a warm cream fill, and forest-green accents, natural/local imagery cues, and the tagline **Discover • Connect • Volunteer**. The goal is a clean regional-guide identity — professional, not cartoonish, and not "AI-generated stock art."

### Color palette
Defined as CSS custom properties in `wordpress/wp-content/themes/southforsyth/assets/css/main.css`. The brand tokens (`--color-navy`, `--color-forest`, etc.) are the source of truth; the older semantic names (`--color-primary`, `--color-secondary`, `--color-accent`, `--color-bg`, ...) are aliases pointing at them, so the whole site re-themes from one place without a risky rename across every rule that already references those names.

| Token | Hex | Used for |
|---|---|---|
| `--color-navy` (`--color-primary`) | `#16263d` | Primary text-on-light, headers, primary buttons, borders, nav links |
| `--color-navy-dark` (`--color-primary-dark`) | `#0c1622` | Hover/dark state for navy elements, footer background |
| `--color-forest` (`--color-secondary`) | `#2f5233` | Secondary accents, hero/feature-banner gradient, badge cycling |
| `--color-forest-dark` (`--color-secondary-dark`) | `#203c24` | Hover/dark state for forest-green elements |
| `--color-cream` (`--color-bg`) | `#faf6ec` | Page background |
| `--color-cream-soft` (`--color-bg-soft`) | `#f2ead9` | Soft-section backgrounds, placeholder cards |
| `--color-sky` | `#cfe3ee` | Soft sky-blue highlight accents |
| `--color-sky-soft` | `#e7f1f6` | Accent-section background gradient (with cream) |
| `--color-gold` (`--color-accent`) | `#c9973e` | Callouts, eyebrows, "Coming Soon" badges, focus outlines |
| `--color-gold-soft` (`--color-accent-soft`) | `#f3e3c2` | Soft gold backgrounds (badges, feature cards) |
| `--color-surface` | `#ffffff` | Clean white cards |
| `--color-text` | `#17232d` | Body text |
| `--color-muted` | `#595e54` | Secondary/muted text (warm neutral gray) |
| `--color-border` | `#e4dcc8` | Card and input borders (warm, not cool blue-gray) |

`--color-success`/`--color-warning`/`--color-error` stay separate, distinct semantic colors (status messaging, not decorative brand accents) and were left largely as-is.

### Logo
No final logo image file exists in the repo yet — nothing assumes a specific filename. `wordpress/wp-content/themes/southforsyth/assets/images/logo/README.md` documents exactly where to place one and how naming/format should work; the real, production way to set it is via **Appearance → Customize → Site Identity** in wp-admin (WordPress's native custom-logo support, already enabled in `inc/setup.php`) — `site-header.php` picks it up automatically, no template change required. Until then, the header falls back to `assets/icons/logo-mark.svg`, a small circular badge (navy ring, cream fill, forest-green mark, gold accent) already matching this palette, plus the site title text — the header never depends on an image successfully loading.

### Tagline
**Discover • Connect • Volunteer** appears next to the site name in the header (hidden on narrow mobile widths to keep the header compact) and in the footer brand block.

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
Planning for how South Forsyth.org pulls in outside data — official government/school sources (including the Chamber of Commerce events calendar), calendar/ICS feeds, GIS/open data, local news RSS, and community submissions — without compromising accuracy or attribution. The Forsyth County Schools importer now exists as the first real source-to-draft vertical; most other sources remain planned. See [wordpress/wp-content/themes/southforsyth/docs/data-integration-roadmap.md](wordpress/wp-content/themes/southforsyth/docs/data-integration-roadmap.md) plus [docs/data-import-system.md](docs/data-import-system.md).

## Editorial roadmap
The first 25 pages to publish, in priority order, plus the highest-SEO-value pages, directory/event/newsletter sequencing, and how this roadmap relates to the evergreen guide list. See [wordpress/wp-content/themes/southforsyth/docs/editorial-roadmap.md](wordpress/wp-content/themes/southforsyth/docs/editorial-roadmap.md).

## Platform architecture
The provider system, import pipeline, cache layer, admin tooling, search architecture, and automation-ready hooks make it possible to pull content in from outside sources at scale instead of authoring everything by hand. Schools are the first completed ingestion vertical through review-ready drafts; scheduled automation is still off. Includes the theme-vs-plugin tradeoff and the concrete trigger for when this should move into its own plugin. See [wordpress/wp-content/themes/southforsyth/docs/platform-architecture.md](wordpress/wp-content/themes/southforsyth/docs/platform-architecture.md).

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

## Homepage status: preview plus live sections
The homepage (`front-page.php`) is still partly a polished preview, but it now calls live query helpers for featured places, recent events, and recent guides. If no matching posts exist, those live sections simply render empty. The guide/category cards remain preview-style cards that link to real hub pages.

Every section — Things To Do, Events, Restaurants & Coffee, Parks & Trails, Schools, Churches, Neighborhoods, Business Directory, New Resident Guide, and Weekend Guide — has a real URL today. The seven with a custom post type (Events, Restaurants, Parks, Schools, Churches, Neighborhoods, Business Directory) use the enhanced `archive.php`, which shows live posts the moment any exist and falls back to intro copy, a clearly-labeled "Coming soon" notice, and sample category cards when a post type has zero posts. The three without a post type of their own (Things To Do, New Resident Guide, Weekend Guide) are real WordPress Pages, auto-created by `inc/page-provisioning.php` on `page-templates/hub.php`. All ten pull their copy from `inc/hub-content.php` — see that file and `docs/content-platform-architecture.md`'s "Hub pages" section for the full explanation.

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
- The homepage is a preview plus live-query front door (see above): guide cards link to real hub pages, while featured places/events/guides pull from published content when it exists.
- Twelve custom post types and their taxonomies are registered and REST-enabled (see the architecture doc linked above): the original nine, plus Trails (split out of Parks), Topics (pillar/cluster pages), and Community Resources, added alongside the data-platform work. Eight of them (Events, Restaurants, Parks, Trails, Schools, Churches, Neighborhoods, Business Directory) already have a real, polished hub page live at their archive URL via the enhanced `archive.php` — intro copy, FAQ, and a newsletter CTA today; live post grids the moment content is published.
- Things To Do, New Resident Guide, and Weekend Guide are real WordPress Pages (auto-created by `inc/page-provisioning.php`) on the reusable `page-templates/hub.php` template, giving all ten IA sections a working URL.
- A full external-data platform is now in place: providers (`inc/providers/`), an import pipeline with a queue and log (`inc/import/`), a cache layer (`inc/cache/`), normalized cross-post-type search (`inc/search/`), a "Community Platform" wp-admin menu (`inc/admin/`), and wireable-but-unscheduled automation hooks (`inc/automation.php`). Forsyth County Schools is the first real importer; other verticals remain planned. See [docs/platform-architecture.md](wordpress/wp-content/themes/southforsyth/docs/platform-architecture.md).
- `archive.php`, `search.php`, and `single.php` are post-type aware, rendering the right card component and meta fields for whatever type is being displayed, and work today for any post type that gets published.
- Navigation now has a real fallback menu (`southforsyth_primary_nav_fallback()` in `inc/menus.php`) pointing at all ten sections, so the header works before an admin ever builds a menu in Appearance > Menus — building one there still takes over automatically.
- Widgets, footer (now with a fallback quick-links list and copyright bar), and reusable components are wired up.
- SEO-ready metadata and schema output are included.

## Next steps
See "Future roadmap" in [docs/content-platform-architecture.md](wordpress/wp-content/themes/southforsyth/docs/content-platform-architecture.md) for the content-model list, [docs/editorial-roadmap.md](wordpress/wp-content/themes/southforsyth/docs/editorial-roadmap.md) for the specific first 25 pages to publish, and [docs/platform-architecture.md](wordpress/wp-content/themes/southforsyth/docs/platform-architecture.md) for the data-platform next steps (configuring a provider's credentials on the new Settings admin page, manually testing an import via the Imports admin page, and — the concrete trigger called out there — extracting the platform into its own plugin the first time a provider actually imports real content on a schedule).
